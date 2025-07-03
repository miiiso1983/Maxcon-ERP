<?php

namespace App\Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'type',
        'quantity',
        'old_quantity',
        'new_quantity',
        'cost_price',
        'reference_type',
        'reference_id',
        'reference',
        'batch_number',
        'expiry_date',
        'user_id',
        'notes',
        'meta_data',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'old_quantity' => 'decimal:2',
        'new_quantity' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'expiry_date' => 'date',
        'meta_data' => 'array',
    ];

    const TYPE_IN = 'in';
    const TYPE_OUT = 'out';
    const TYPE_ADJUSTMENT = 'adjustment';
    const TYPE_TRANSFER = 'transfer';
    const TYPE_SALE = 'sale';
    const TYPE_PURCHASE = 'purchase';
    const TYPE_RETURN = 'return';
    const TYPE_DAMAGE = 'damage';
    const TYPE_EXPIRED = 'expired';

    // Relationships
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }

    // Scopes
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeByWarehouse($query, int $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    public function scopeIncoming($query)
    {
        return $query->whereIn('type', [self::TYPE_IN, self::TYPE_PURCHASE, self::TYPE_RETURN]);
    }

    public function scopeOutgoing($query)
    {
        return $query->whereIn('type', [self::TYPE_OUT, self::TYPE_SALE, self::TYPE_TRANSFER, self::TYPE_DAMAGE, self::TYPE_EXPIRED]);
    }

    // Accessors
    public function getQuantityChangeAttribute(): float
    {
        return $this->new_quantity - $this->old_quantity;
    }

    public function getValueChangeAttribute(): float
    {
        return $this->quantity_change * $this->cost_price;
    }

    public function getTypeColorAttribute(): string
    {
        return match($this->type) {
            self::TYPE_IN, self::TYPE_PURCHASE, self::TYPE_RETURN => 'success',
            self::TYPE_OUT, self::TYPE_SALE => 'primary',
            self::TYPE_TRANSFER => 'info',
            self::TYPE_DAMAGE, self::TYPE_EXPIRED => 'danger',
            self::TYPE_ADJUSTMENT => 'warning',
            default => 'secondary',
        };
    }

    public function getTypeIconAttribute(): string
    {
        return match($this->type) {
            self::TYPE_IN => 'fas fa-arrow-down',
            self::TYPE_OUT => 'fas fa-arrow-up',
            self::TYPE_PURCHASE => 'fas fa-shopping-cart',
            self::TYPE_SALE => 'fas fa-cash-register',
            self::TYPE_TRANSFER => 'fas fa-exchange-alt',
            self::TYPE_RETURN => 'fas fa-undo',
            self::TYPE_DAMAGE => 'fas fa-exclamation-triangle',
            self::TYPE_EXPIRED => 'fas fa-clock',
            self::TYPE_ADJUSTMENT => 'fas fa-edit',
            default => 'fas fa-box',
        };
    }

    // Methods
    public function isIncoming(): bool
    {
        return in_array($this->type, [self::TYPE_IN, self::TYPE_PURCHASE, self::TYPE_RETURN]);
    }

    public function isOutgoing(): bool
    {
        return in_array($this->type, [self::TYPE_OUT, self::TYPE_SALE, self::TYPE_TRANSFER, self::TYPE_DAMAGE, self::TYPE_EXPIRED]);
    }

    public function getFormattedTypeAttribute(): string
    {
        return match($this->type) {
            self::TYPE_IN => __('Stock In'),
            self::TYPE_OUT => __('Stock Out'),
            self::TYPE_PURCHASE => __('Purchase'),
            self::TYPE_SALE => __('Sale'),
            self::TYPE_TRANSFER => __('Transfer'),
            self::TYPE_RETURN => __('Return'),
            self::TYPE_DAMAGE => __('Damage'),
            self::TYPE_EXPIRED => __('Expired'),
            self::TYPE_ADJUSTMENT => __('Adjustment'),
            default => ucfirst($this->type),
        };
    }
}
