<?php

namespace App\Modules\Sales\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Modules\Customer\Models\Customer;
use App\Models\User;

class Sale extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'invoice_number',
        'customer_id',
        'user_id',
        'medical_rep_id',
        'warehouse_id',
        'sale_date',
        'due_date',
        'status',
        'payment_status',
        'payment_method',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'paid_amount',
        'change_amount',
        'notes',
        'reference',
        'currency',
        'exchange_rate',
        'meta_data',
    ];

    protected $casts = [
        'sale_date' => 'datetime',
        'due_date' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'exchange_rate' => 'decimal:4',
        'meta_data' => 'array',
    ];

    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_SHIPPED = 'shipped';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_RETURNED = 'returned';

    const PAYMENT_STATUS_PENDING = 'pending';
    const PAYMENT_STATUS_PARTIAL = 'partial';
    const PAYMENT_STATUS_PAID = 'paid';
    const PAYMENT_STATUS_OVERDUE = 'overdue';
    const PAYMENT_STATUS_REFUNDED = 'refunded';

    const PAYMENT_METHOD_CASH = 'cash';
    const PAYMENT_METHOD_CARD = 'card';
    const PAYMENT_METHOD_TRANSFER = 'transfer';
    const PAYMENT_METHOD_CREDIT = 'credit';
    const PAYMENT_METHOD_MIXED = 'mixed';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'invoice_number', 'customer_id', 'status', 'payment_status',
                'total_amount', 'paid_amount'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relationships
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function medicalRep()
    {
        return $this->belongsTo(\App\Modules\MedicalReps\Models\MedicalRep::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(\App\Modules\Inventory\Models\Warehouse::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function returns()
    {
        return $this->hasMany(SaleReturn::class);
    }

    // Scopes
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPaymentStatus($query, string $paymentStatus)
    {
        return $query->where('payment_status', $paymentStatus);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('sale_date', today());
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('sale_date', now()->month)
            ->whereYear('sale_date', now()->year);
    }

    public function scopeOverdue($query)
    {
        return $query->where('payment_status', '!=', self::PAYMENT_STATUS_PAID)
            ->where('due_date', '<', now());
    }

    // Accessors & Mutators
    public function getBalanceAmountAttribute(): float
    {
        return $this->total_amount - $this->paid_amount;
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->payment_status !== self::PAYMENT_STATUS_PAID 
            && $this->due_date 
            && $this->due_date->isPast();
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'secondary',
            self::STATUS_PENDING => 'warning',
            self::STATUS_CONFIRMED => 'info',
            self::STATUS_SHIPPED => 'primary',
            self::STATUS_DELIVERED => 'success',
            self::STATUS_CANCELLED => 'danger',
            self::STATUS_RETURNED => 'dark',
            default => 'secondary',
        };
    }

    public function getPaymentStatusColorAttribute(): string
    {
        return match($this->payment_status) {
            self::PAYMENT_STATUS_PENDING => 'warning',
            self::PAYMENT_STATUS_PARTIAL => 'info',
            self::PAYMENT_STATUS_PAID => 'success',
            self::PAYMENT_STATUS_OVERDUE => 'danger',
            self::PAYMENT_STATUS_REFUNDED => 'dark',
            default => 'secondary',
        };
    }

    // Methods
    public function generateInvoiceNumber(): string
    {
        $prefix = 'INV';
        $date = now()->format('Ymd');
        $sequence = str_pad($this->id ?? 1, 4, '0', STR_PAD_LEFT);
        
        return "{$prefix}-{$date}-{$sequence}";
    }

    public function calculateTotals(): void
    {
        $this->subtotal = $this->items()->sum(\DB::raw('quantity * unit_price'));
        $this->tax_amount = $this->items()->sum(\DB::raw('quantity * unit_price * (tax_rate / 100)'));
        $this->total_amount = $this->subtotal + $this->tax_amount - $this->discount_amount;
        
        $this->updatePaymentStatus();
    }

    public function updatePaymentStatus(): void
    {
        if ($this->paid_amount <= 0) {
            $this->payment_status = self::PAYMENT_STATUS_PENDING;
        } elseif ($this->paid_amount >= $this->total_amount) {
            $this->payment_status = self::PAYMENT_STATUS_PAID;
        } else {
            $this->payment_status = self::PAYMENT_STATUS_PARTIAL;
        }

        // Check if overdue
        if ($this->payment_status !== self::PAYMENT_STATUS_PAID 
            && $this->due_date 
            && $this->due_date->isPast()) {
            $this->payment_status = self::PAYMENT_STATUS_OVERDUE;
        }
    }

    public function addPayment(float $amount, string $method, ?string $reference = null): Payment
    {
        $payment = $this->payments()->create([
            'amount' => $amount,
            'payment_method' => $method,
            'payment_date' => now(),
            'reference' => $reference,
            'user_id' => auth()->id() ?? $this->user_id,
        ]);

        $this->paid_amount += $amount;
        $this->updatePaymentStatus();
        $this->save();

        return $payment;
    }

    public function canBeEdited(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_PENDING]);
    }

    public function canBeCancelled(): bool
    {
        return !in_array($this->status, [self::STATUS_DELIVERED, self::STATUS_CANCELLED, self::STATUS_RETURNED]);
    }

    public function getTotalItemsAttribute(): int
    {
        return $this->items()->sum('quantity');
    }

    public function getProfitAttribute(): float
    {
        return $this->items()->sum(\DB::raw('quantity * (unit_price - cost_price)'));
    }
}
