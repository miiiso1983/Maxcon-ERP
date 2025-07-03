<?php

namespace App\Modules\Supplier\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Modules\Inventory\Models\Product;

class PurchaseOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'product_name',
        'product_sku',
        'quantity',
        'unit_cost',
        'tax_rate',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'notes',
        'expected_delivery_date',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'expected_delivery_date' => 'date',
    ];

    // Relationships
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function receiptItems()
    {
        return $this->hasMany(PurchaseReceiptItem::class);
    }

    // Accessors
    public function getSubtotalAttribute(): float
    {
        return $this->quantity * $this->unit_cost;
    }

    public function getReceivedQuantityAttribute(): float
    {
        return $this->receiptItems()->sum('quantity_received');
    }

    public function getPendingQuantityAttribute(): float
    {
        return $this->quantity - $this->received_quantity;
    }

    public function getIsFullyReceivedAttribute(): bool
    {
        return $this->received_quantity >= $this->quantity;
    }

    // Methods
    public function calculateTotals(): void
    {
        $subtotal = $this->quantity * $this->unit_cost;
        $this->tax_amount = $subtotal * ($this->tax_rate / 100);
        $this->total_amount = $subtotal + $this->tax_amount - $this->discount_amount;
    }
}
