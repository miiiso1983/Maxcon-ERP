<?php

namespace App\Modules\Supplier\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Models\User;

class PurchaseReceipt extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'purchase_order_id',
        'receipt_number',
        'received_date',
        'user_id',
        'warehouse_id',
        'quantity_received',
        'total_amount',
        'notes',
        'supplier_invoice_number',
        'supplier_invoice_date',
    ];

    protected $casts = [
        'received_date' => 'datetime',
        'supplier_invoice_date' => 'date',
        'quantity_received' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'receipt_number', 'purchase_order_id', 'quantity_received',
                'total_amount', 'received_date'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relationships
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
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
        return $this->hasMany(PurchaseReceiptItem::class);
    }

    // Methods
    public function generateReceiptNumber(): string
    {
        $prefix = 'REC';
        $date = now()->format('Ymd');
        $sequence = str_pad($this->id ?? 1, 4, '0', STR_PAD_LEFT);
        
        return "{$prefix}-{$date}-{$sequence}";
    }
}
