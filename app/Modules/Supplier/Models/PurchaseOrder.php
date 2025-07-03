<?php

namespace App\Modules\Supplier\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Models\User;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'po_number',
        'supplier_id',
        'user_id',
        'warehouse_id',
        'order_date',
        'expected_delivery_date',
        'delivered_date',
        'status',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'shipping_cost',
        'total_amount',
        'received_amount',
        'notes',
        'reference',
        'currency',
        'exchange_rate',
        'terms_conditions',
        'meta_data',
    ];

    protected $casts = [
        'order_date' => 'datetime',
        'expected_delivery_date' => 'datetime',
        'delivered_date' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'received_amount' => 'decimal:2',
        'exchange_rate' => 'decimal:4',
        'meta_data' => 'array',
    ];

    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_PARTIAL = 'partial';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'po_number', 'supplier_id', 'status', 'total_amount',
                'expected_delivery_date', 'delivered_date'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relationships
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(\App\Modules\Inventory\Models\Warehouse::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function receipts()
    {
        return $this->hasMany(PurchaseReceipt::class);
    }

    // Scopes
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_CONFIRMED]);
    }

    public function scopeOverdue($query)
    {
        return $query->where('expected_delivery_date', '<', now())
            ->whereNotIn('status', [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    // Accessors
    public function getBalanceAmountAttribute(): float
    {
        return $this->total_amount - $this->received_amount;
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->expected_delivery_date 
            && $this->expected_delivery_date->isPast()
            && !in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'secondary',
            self::STATUS_PENDING => 'warning',
            self::STATUS_CONFIRMED => 'info',
            self::STATUS_PARTIAL => 'primary',
            self::STATUS_COMPLETED => 'success',
            self::STATUS_CANCELLED => 'danger',
            default => 'secondary',
        };
    }

    public function getCompletionPercentageAttribute(): float
    {
        if ($this->total_amount <= 0) {
            return 0;
        }

        return ($this->received_amount / $this->total_amount) * 100;
    }

    // Methods
    public function generatePONumber(): string
    {
        $prefix = 'PO';
        $date = now()->format('Ymd');
        $sequence = str_pad($this->id ?? 1, 4, '0', STR_PAD_LEFT);
        
        return "{$prefix}-{$date}-{$sequence}";
    }

    public function calculateTotals(): void
    {
        $this->subtotal = $this->items()->sum(\DB::raw('quantity * unit_cost'));
        $this->tax_amount = $this->items()->sum(\DB::raw('quantity * unit_cost * (tax_rate / 100)'));
        $this->total_amount = $this->subtotal + $this->tax_amount + $this->shipping_cost - $this->discount_amount;
        
        $this->updateStatus();
    }

    public function updateStatus(): void
    {
        $totalReceived = $this->receipts()->sum('total_amount');
        $this->received_amount = $totalReceived;

        if ($totalReceived <= 0) {
            // Keep current status if no receipts
        } elseif ($totalReceived >= $this->total_amount) {
            $this->status = self::STATUS_COMPLETED;
            $this->delivered_date = now();
        } else {
            $this->status = self::STATUS_PARTIAL;
        }
    }

    public function canBeEdited(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_PENDING]);
    }

    public function canBeCancelled(): bool
    {
        return !in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    public function canReceiveItems(): bool
    {
        return in_array($this->status, [self::STATUS_CONFIRMED, self::STATUS_PARTIAL]);
    }

    public function getTotalItemsAttribute(): int
    {
        return $this->items()->sum('quantity');
    }

    public function getReceivedItemsAttribute(): int
    {
        return $this->receipts()->sum('quantity_received');
    }

    public function getPendingItemsAttribute(): int
    {
        return $this->total_items - $this->received_items;
    }

    public function createReceipt(array $items, array $receiptData = []): PurchaseReceipt
    {
        $receipt = $this->receipts()->create(array_merge([
            'receipt_number' => 'REC-' . time(),
            'received_date' => now(),
            'user_id' => auth()->id(),
            'warehouse_id' => $this->warehouse_id,
        ], $receiptData));

        $totalAmount = 0;
        $totalQuantity = 0;

        foreach ($items as $itemData) {
            $poItem = $this->items()->find($itemData['purchase_order_item_id']);
            
            if ($poItem) {
                $receiptItem = $receipt->items()->create([
                    'purchase_order_item_id' => $poItem->id,
                    'product_id' => $poItem->product_id,
                    'quantity_received' => $itemData['quantity_received'],
                    'unit_cost' => $poItem->unit_cost,
                    'batch_number' => $itemData['batch_number'] ?? null,
                    'expiry_date' => $itemData['expiry_date'] ?? null,
                    'notes' => $itemData['notes'] ?? null,
                ]);

                $totalAmount += $receiptItem->quantity_received * $receiptItem->unit_cost;
                $totalQuantity += $receiptItem->quantity_received;

                // Update stock
                if ($poItem->product && $poItem->product->is_trackable) {
                    $poItem->product->updateStock(
                        $this->warehouse_id,
                        $receiptItem->quantity_received,
                        'purchase',
                        "Purchase receipt #{$receipt->receipt_number}",
                        $receiptItem->batch_number,
                        $receiptItem->expiry_date
                    );
                }
            }
        }

        $receipt->update([
            'total_amount' => $totalAmount,
            'quantity_received' => $totalQuantity,
        ]);

        // Update PO status
        $this->updateStatus();
        $this->save();

        return $receipt;
    }
}
