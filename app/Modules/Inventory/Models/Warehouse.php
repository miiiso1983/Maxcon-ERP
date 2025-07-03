<?php

namespace App\Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\Translatable\HasTranslations;

class Warehouse extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, HasTranslations;

    protected $fillable = [
        'name',
        'code',
        'description',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'phone',
        'email',
        'manager_id',
        'is_active',
        'is_default',
        'capacity',
        'type',
        'meta_data',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'capacity' => 'decimal:2',
        'meta_data' => 'array',
    ];

    public $translatable = ['name', 'description'];

    const TYPE_MAIN = 'main';
    const TYPE_BRANCH = 'branch';
    const TYPE_VIRTUAL = 'virtual';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'code', 'address', 'manager_id', 'is_active', 'is_default'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relationships
    public function manager()
    {
        return $this->belongsTo(\App\Models\User::class, 'manager_id');
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function transfers()
    {
        return $this->hasMany(StockTransfer::class, 'from_warehouse_id')
            ->orWhere('to_warehouse_id', $this->id);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    // Methods
    public function getTotalProducts(): int
    {
        return $this->stocks()->distinct('product_id')->count();
    }

    public function getTotalStock(): float
    {
        return $this->stocks()->sum('quantity');
    }

    public function getTotalValue(): float
    {
        return $this->stocks()
            ->join('products', 'stocks.product_id', '=', 'products.id')
            ->selectRaw('SUM(stocks.quantity * products.cost_price) as total_value')
            ->value('total_value') ?? 0;
    }

    public function getCapacityUsed(): float
    {
        if (!$this->capacity) {
            return 0;
        }

        return ($this->getTotalStock() / $this->capacity) * 100;
    }

    public function isCapacityExceeded(): bool
    {
        if (!$this->capacity) {
            return false;
        }

        return $this->getTotalStock() > $this->capacity;
    }

    public function getProductStock(int $productId): float
    {
        return $this->stocks()
            ->where('product_id', $productId)
            ->sum('quantity');
    }

    public function hasProduct(int $productId): bool
    {
        return $this->stocks()
            ->where('product_id', $productId)
            ->where('quantity', '>', 0)
            ->exists();
    }

    public function getLowStockProducts(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->stocks()
            ->with('product')
            ->whereHas('product', function ($query) {
                $query->whereRaw('stocks.quantity <= products.reorder_level');
            })
            ->get();
    }

    public function getOutOfStockProducts(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->stocks()
            ->with('product')
            ->where('quantity', '<=', 0)
            ->get();
    }

    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->state,
            $this->postal_code,
            $this->country,
        ]);

        return implode(', ', $parts);
    }
}
