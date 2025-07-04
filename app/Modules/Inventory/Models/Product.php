<?php

namespace App\Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\Translatable\HasTranslations;

class Product extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, HasTranslations;

    protected $fillable = [
        'name',
        'description',
        'sku',
        'barcode',
        'category_id',
        'brand_id',
        'unit_id',
        'type',
        'status',
        'cost_price',
        'selling_price',
        'min_stock_level',
        'max_stock_level',
        'reorder_level',
        'weight',
        'dimensions',
        'images',
        'is_active',
        'is_trackable',
        'has_expiry',
        'has_batch',
        'tax_rate',
        'meta_data',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'min_stock_level' => 'decimal:2',
        'max_stock_level' => 'decimal:2',
        'reorder_level' => 'decimal:2',
        'weight' => 'decimal:3',
        'tax_rate' => 'decimal:2',
        'dimensions' => 'array',
        'images' => 'array',
        'meta_data' => 'array',
        'is_active' => 'boolean',
        'is_trackable' => 'boolean',
        'has_expiry' => 'boolean',
        'has_batch' => 'boolean',
    ];

    public $translatable = ['name', 'description'];

    const TYPE_SIMPLE = 'simple';
    const TYPE_VARIABLE = 'variable';
    const TYPE_SERVICE = 'service';

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_DISCONTINUED = 'discontinued';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name', 'sku', 'barcode', 'category_id', 'brand_id', 
                'cost_price', 'selling_price', 'status', 'is_active'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relationships
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function saleItems()
    {
        return $this->hasMany(\App\Modules\Sales\Models\SaleItem::class);
    }

    public function purchaseOrderItems()
    {
        return $this->hasMany(\App\Modules\Purchase\Models\PurchaseOrderItem::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByBrand($query, $brandId)
    {
        return $query->where('brand_id', $brandId);
    }

    public function scopeLowStock($query)
    {
        return $query->whereHas('stocks', function ($q) {
            $q->whereRaw('quantity <= reorder_level');
        });
    }

    public function scopeOutOfStock($query)
    {
        return $query->whereHas('stocks', function ($q) {
            $q->where('quantity', '<=', 0);
        });
    }

    // Accessors & Mutators
    public function getProfitMarginAttribute(): float
    {
        if ($this->cost_price <= 0) {
            return 0;
        }

        return (($this->selling_price - $this->cost_price) / $this->cost_price) * 100;
    }

    public function getProfitAmountAttribute(): float
    {
        return $this->selling_price - $this->cost_price;
    }

    public function getMainImageAttribute(): ?string
    {
        $images = $this->images ?? [];
        return !empty($images) ? asset('storage/' . $images[0]) : null;
    }

    public function getTotalStockAttribute(): float
    {
        return $this->stocks()->sum('quantity');
    }

    public function getAvailableStockAttribute(): float
    {
        return $this->stocks()->sum('available_quantity');
    }

    // Methods
    public function isLowStock(): bool
    {
        return $this->total_stock <= $this->reorder_level;
    }

    public function isOutOfStock(): bool
    {
        return $this->available_stock <= 0;
    }

    public function isOverStock(): bool
    {
        return $this->total_stock > $this->max_stock_level;
    }

    public function getStockStatus(): string
    {
        if ($this->isOutOfStock()) {
            return 'out_of_stock';
        } elseif ($this->isLowStock()) {
            return 'low_stock';
        } elseif ($this->isOverStock()) {
            return 'over_stock';
        }

        return 'in_stock';
    }

    public function getStockInWarehouse(int $warehouseId): float
    {
        return $this->stocks()
            ->where('warehouse_id', $warehouseId)
            ->sum('quantity');
    }

    public function hasStock(float $quantity = 1, ?int $warehouseId = null): bool
    {
        $query = $this->stocks();
        
        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }
        
        return $query->sum('available_quantity') >= $quantity;
    }

    public function generateSku(): string
    {
        $prefix = strtoupper(substr($this->category->code ?? 'PRD', 0, 3));
        $number = str_pad($this->id ?? 1, 6, '0', STR_PAD_LEFT);
        
        return $prefix . '-' . $number;
    }

    public function updateStock(int $warehouseId, float $quantity, string $type = 'adjustment', ?string $reference = null): void
    {
        $stock = $this->stocks()->firstOrCreate([
            'warehouse_id' => $warehouseId,
        ], [
            'quantity' => 0,
            'available_quantity' => 0,
            'reserved_quantity' => 0,
        ]);

        $oldQuantity = $stock->quantity;
        
        if ($type === 'in') {
            $stock->quantity += $quantity;
            $stock->available_quantity += $quantity;
        } elseif ($type === 'out') {
            $stock->quantity -= $quantity;
            $stock->available_quantity -= $quantity;
        } else {
            $stock->quantity = $quantity;
            $stock->available_quantity = $quantity;
        }

        $stock->save();

        // Log stock movement
        $this->stockMovements()->create([
            'warehouse_id' => $warehouseId,
            'type' => $type,
            'quantity' => $quantity,
            'old_quantity' => $oldQuantity,
            'new_quantity' => $stock->quantity,
            'reference' => $reference,
            'notes' => "Stock {$type} for product {$this->name}",
        ]);
    }
}
