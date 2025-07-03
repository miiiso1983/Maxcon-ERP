<?php

namespace App\Modules\Customer\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\Translatable\HasTranslations;
use App\Modules\Sales\Models\Sale;

class Customer extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, HasTranslations;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'mobile',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'tax_number',
        'customer_code',
        'customer_type',
        'credit_limit',
        'payment_terms',
        'discount_percentage',
        'is_active',
        'date_of_birth',
        'gender',
        'notes',
        'meta_data',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'is_active' => 'boolean',
        'date_of_birth' => 'date',
        'meta_data' => 'array',
    ];

    public $translatable = ['name', 'address', 'notes'];

    const TYPE_INDIVIDUAL = 'individual';
    const TYPE_BUSINESS = 'business';
    const TYPE_HOSPITAL = 'hospital';
    const TYPE_CLINIC = 'clinic';
    const TYPE_PHARMACY = 'pharmacy';

    const GENDER_MALE = 'male';
    const GENDER_FEMALE = 'female';
    const GENDER_OTHER = 'other';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name', 'email', 'phone', 'customer_type', 
                'credit_limit', 'is_active'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relationships
    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function loyaltyPoints()
    {
        return $this->hasMany(CustomerLoyaltyPoint::class);
    }

    public function visits()
    {
        return $this->hasMany(\App\Modules\MedicalReps\Models\CustomerVisit::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('customer_type', $type);
    }

    public function scopeWithDebt($query)
    {
        return $query->whereHas('sales', function ($q) {
            $q->where('payment_status', '!=', Sale::PAYMENT_STATUS_PAID);
        });
    }

    // Accessors & Mutators
    public function getFullNameAttribute(): string
    {
        return $this->name;
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
        return $this->sales()->sum('total_amount');
    }

    public function getTotalDebtAttribute(): float
    {
        return $this->sales()
            ->where('payment_status', '!=', Sale::PAYMENT_STATUS_PAID)
            ->sum(\DB::raw('total_amount - paid_amount'));
    }

    public function getAvailableCreditAttribute(): float
    {
        return $this->credit_limit - $this->total_debt;
    }

    public function getTotalLoyaltyPointsAttribute(): int
    {
        return $this->loyaltyPoints()->sum('points');
    }

    public function getLastPurchaseDateAttribute()
    {
        return $this->sales()->latest('sale_date')->value('sale_date');
    }

    // Methods
    public function generateCustomerCode(): string
    {
        $prefix = strtoupper(substr($this->customer_type, 0, 3));
        $sequence = str_pad($this->id ?? 1, 6, '0', STR_PAD_LEFT);
        
        return "{$prefix}-{$sequence}";
    }

    public function hasAvailableCredit(float $amount): bool
    {
        return $this->available_credit >= $amount;
    }

    public function addLoyaltyPoints(float $purchaseAmount): void
    {
        // 1 point per 10 currency units spent
        $points = floor($purchaseAmount / 10);
        
        if ($points > 0) {
            $this->loyaltyPoints()->create([
                'points' => $points,
                'transaction_type' => 'earned',
                'reference' => 'Purchase',
                'description' => "Earned from purchase of {$purchaseAmount}",
            ]);
        }
    }

    public function redeemLoyaltyPoints(int $points): bool
    {
        if ($this->total_loyalty_points >= $points) {
            $this->loyaltyPoints()->create([
                'points' => -$points,
                'transaction_type' => 'redeemed',
                'reference' => 'Redemption',
                'description' => "Redeemed {$points} points",
            ]);
            
            return true;
        }
        
        return false;
    }

    public function getCustomerTypeColorAttribute(): string
    {
        return match($this->customer_type) {
            self::TYPE_INDIVIDUAL => 'primary',
            self::TYPE_BUSINESS => 'success',
            self::TYPE_HOSPITAL => 'danger',
            self::TYPE_CLINIC => 'warning',
            self::TYPE_PHARMACY => 'info',
            default => 'secondary',
        };
    }

    public function isOverCreditLimit(): bool
    {
        return $this->total_debt > $this->credit_limit;
    }

    public function getPurchaseFrequency(): string
    {
        $salesCount = $this->sales()->count();
        $daysSinceFirstPurchase = $this->sales()->oldest('sale_date')->value('sale_date')?->diffInDays(now()) ?? 1;
        
        $frequency = $salesCount / max($daysSinceFirstPurchase / 30, 1); // purchases per month
        
        if ($frequency >= 4) {
            return 'very_frequent';
        } elseif ($frequency >= 2) {
            return 'frequent';
        } elseif ($frequency >= 1) {
            return 'regular';
        } else {
            return 'occasional';
        }
    }

    public function getCustomerSegment(): string
    {
        $totalPurchases = $this->total_purchases;
        
        if ($totalPurchases >= 10000) {
            return 'vip';
        } elseif ($totalPurchases >= 5000) {
            return 'premium';
        } elseif ($totalPurchases >= 1000) {
            return 'regular';
        } else {
            return 'new';
        }
    }
}
