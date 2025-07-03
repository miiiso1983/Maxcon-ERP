<?php

namespace App\Modules\Supplier\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\Translatable\HasTranslations;

class Supplier extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, HasTranslations;

    protected $fillable = [
        'name',
        'company_name',
        'email',
        'phone',
        'mobile',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'tax_number',
        'supplier_code',
        'supplier_type',
        'payment_terms',
        'credit_limit',
        'discount_percentage',
        'is_active',
        'contact_person',
        'website',
        'bank_details',
        'notes',
        'rating',
        'meta_data',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'is_active' => 'boolean',
        'bank_details' => 'array',
        'meta_data' => 'array',
        'rating' => 'decimal:1',
    ];

    public $translatable = ['name', 'address', 'notes'];

    const TYPE_MANUFACTURER = 'manufacturer';
    const TYPE_DISTRIBUTOR = 'distributor';
    const TYPE_WHOLESALER = 'wholesaler';
    const TYPE_IMPORTER = 'importer';
    const TYPE_LOCAL = 'local';
    const TYPE_INTERNATIONAL = 'international';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name', 'email', 'phone', 'supplier_type', 
                'credit_limit', 'is_active', 'rating'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relationships
    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function products()
    {
        return $this->belongsToMany(\App\Modules\Inventory\Models\Product::class, 'supplier_products')
            ->withPivot(['supplier_sku', 'cost_price', 'lead_time_days', 'minimum_order_quantity'])
            ->withTimestamps();
    }

    public function evaluations()
    {
        return $this->hasMany(SupplierEvaluation::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('supplier_type', $type);
    }

    public function scopeWithOutstandingOrders($query)
    {
        return $query->whereHas('purchaseOrders', function ($q) {
            $q->whereIn('status', ['pending', 'confirmed', 'partial']);
        });
    }

    // Accessors & Mutators
    public function getFullNameAttribute(): string
    {
        return $this->company_name ?: $this->name;
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

    public function getTotalPurchasesAttribute(): float
    {
        return $this->purchaseOrders()->sum('total_amount');
    }

    public function getTotalOutstandingAttribute(): float
    {
        return $this->purchaseOrders()
            ->whereIn('status', ['pending', 'confirmed', 'partial'])
            ->sum(\DB::raw('total_amount - received_amount'));
    }

    public function getAverageLeadTimeAttribute(): int
    {
        return $this->purchaseOrders()
            ->whereNotNull('delivered_date')
            ->avg(\DB::raw('DATEDIFF(delivered_date, order_date)')) ?? 0;
    }

    public function getLastOrderDateAttribute()
    {
        return $this->purchaseOrders()->latest('order_date')->value('order_date');
    }

    public function getSupplierTypeColorAttribute(): string
    {
        return match($this->supplier_type) {
            self::TYPE_MANUFACTURER => 'primary',
            self::TYPE_DISTRIBUTOR => 'success',
            self::TYPE_WHOLESALER => 'info',
            self::TYPE_IMPORTER => 'warning',
            self::TYPE_LOCAL => 'secondary',
            self::TYPE_INTERNATIONAL => 'danger',
            default => 'secondary',
        };
    }

    public function getRatingColorAttribute(): string
    {
        if ($this->rating >= 4.5) return 'success';
        if ($this->rating >= 3.5) return 'info';
        if ($this->rating >= 2.5) return 'warning';
        return 'danger';
    }

    // Methods
    public function generateSupplierCode(): string
    {
        $prefix = strtoupper(substr($this->supplier_type, 0, 3));
        $sequence = str_pad($this->id ?? 1, 6, '0', STR_PAD_LEFT);
        
        return "{$prefix}-{$sequence}";
    }

    public function hasAvailableCredit(float $amount): bool
    {
        return ($this->credit_limit - $this->total_outstanding) >= $amount;
    }

    public function updateRating(): void
    {
        $evaluations = $this->evaluations()->where('is_active', true);
        
        if ($evaluations->count() > 0) {
            $this->rating = $evaluations->avg('overall_rating');
            $this->save();
        }
    }

    public function getPerformanceMetrics(): array
    {
        $orders = $this->purchaseOrders();
        
        return [
            'total_orders' => $orders->count(),
            'completed_orders' => $orders->where('status', 'completed')->count(),
            'on_time_delivery_rate' => $this->calculateOnTimeDeliveryRate(),
            'quality_rating' => $this->evaluations()->avg('quality_rating') ?? 0,
            'service_rating' => $this->evaluations()->avg('service_rating') ?? 0,
            'price_competitiveness' => $this->evaluations()->avg('price_rating') ?? 0,
            'average_lead_time' => $this->average_lead_time,
        ];
    }

    private function calculateOnTimeDeliveryRate(): float
    {
        $completedOrders = $this->purchaseOrders()
            ->where('status', 'completed')
            ->whereNotNull('delivered_date')
            ->whereNotNull('expected_delivery_date');

        $totalCompleted = $completedOrders->count();
        
        if ($totalCompleted === 0) {
            return 0;
        }

        $onTimeDeliveries = $completedOrders
            ->whereRaw('delivered_date <= expected_delivery_date')
            ->count();

        return ($onTimeDeliveries / $totalCompleted) * 100;
    }

    public function getSupplierCategory(): string
    {
        $metrics = $this->getPerformanceMetrics();
        $rating = $this->rating ?? 0;
        $onTimeRate = $metrics['on_time_delivery_rate'];

        if ($rating >= 4.5 && $onTimeRate >= 95) {
            return 'preferred';
        } elseif ($rating >= 4.0 && $onTimeRate >= 85) {
            return 'approved';
        } elseif ($rating >= 3.0 && $onTimeRate >= 70) {
            return 'conditional';
        } else {
            return 'under_review';
        }
    }

    public function canPlaceOrder(float $amount): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->getSupplierCategory() === 'under_review') {
            return false;
        }

        return $this->hasAvailableCredit($amount);
    }
}
