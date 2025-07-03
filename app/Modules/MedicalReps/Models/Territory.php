<?php

namespace App\Modules\MedicalReps\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Territory extends Model
{
    use HasFactory, SoftDeletes, HasTranslations;

    protected $fillable = [
        'name',
        'description',
        'code',
        'region',
        'province',
        'cities',
        'postal_codes',
        'population',
        'market_potential',
        'competition_level',
        'manager_id',
        'coordinates',
        'boundaries',
        'is_active',
        'meta_data',
    ];

    protected $casts = [
        'cities' => 'array',
        'postal_codes' => 'array',
        'population' => 'integer',
        'market_potential' => 'decimal:2',
        'coordinates' => 'array',
        'boundaries' => 'array',
        'is_active' => 'boolean',
        'meta_data' => 'array',
    ];

    public $translatable = ['name', 'description'];

    // Competition Levels
    const COMPETITION_LOW = 'low';
    const COMPETITION_MEDIUM = 'medium';
    const COMPETITION_HIGH = 'high';
    const COMPETITION_VERY_HIGH = 'very_high';

    // Relationships
    public function manager()
    {
        return $this->belongsTo(MedicalRep::class, 'manager_id');
    }

    public function medicalReps()
    {
        return $this->hasMany(MedicalRep::class);
    }

    public function customers()
    {
        return $this->hasMany(\App\Modules\Customer\Models\Customer::class);
    }

    public function visits()
    {
        return $this->hasManyThrough(CustomerVisit::class, MedicalRep::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByRegion($query, $region)
    {
        return $query->where('region', $region);
    }

    public function scopeByProvince($query, $province)
    {
        return $query->where('province', $province);
    }

    // Accessors
    public function getCompetitionColorAttribute(): string
    {
        return match($this->competition_level) {
            self::COMPETITION_LOW => 'success',
            self::COMPETITION_MEDIUM => 'warning',
            self::COMPETITION_HIGH => 'danger',
            self::COMPETITION_VERY_HIGH => 'dark',
            default => 'light',
        };
    }

    public function getTotalCustomersAttribute(): int
    {
        return $this->customers()->count();
    }

    public function getActiveRepsAttribute(): int
    {
        return $this->medicalReps()->active()->count();
    }

    // Methods
    public function getPerformanceMetrics(string $period = 'month'): array
    {
        $startDate = match($period) {
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'quarter' => now()->startOfQuarter(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };

        $reps = $this->medicalReps()->active()->get();
        $totalSales = 0;
        $totalVisits = 0;
        $completedVisits = 0;
        $totalTarget = 0;

        foreach ($reps as $rep) {
            $repSales = $rep->sales()
                ->where('sale_date', '>=', $startDate)
                ->where('status', 'completed')
                ->sum('total_amount');
            
            $repVisits = $rep->visits()
                ->where('visit_date', '>=', $startDate)
                ->get();

            $totalSales += $repSales;
            $totalVisits += $repVisits->count();
            $completedVisits += $repVisits->where('status', CustomerVisit::STATUS_COMPLETED)->count();
            
            $target = match($period) {
                'month' => $rep->target_monthly,
                'quarter' => $rep->target_quarterly,
                'year' => $rep->target_annual,
                default => $rep->target_monthly,
            };
            $totalTarget += $target;
        }

        return [
            'total_sales' => $totalSales,
            'total_visits' => $totalVisits,
            'completed_visits' => $completedVisits,
            'total_target' => $totalTarget,
            'target_achievement' => $totalTarget > 0 ? ($totalSales / $totalTarget) * 100 : 0,
            'visit_completion_rate' => $totalVisits > 0 ? ($completedVisits / $totalVisits) * 100 : 0,
            'active_reps' => $reps->count(),
            'average_sales_per_rep' => $reps->count() > 0 ? $totalSales / $reps->count() : 0,
        ];
    }

    public function getCustomerDistribution(): array
    {
        $customers = $this->customers;
        $totalCustomers = $customers->count();
        
        $activeCustomers = $customers->filter(function ($customer) {
            return $customer->sales()->where('created_at', '>=', now()->subDays(90))->exists();
        })->count();

        $newCustomers = $customers->where('created_at', '>=', now()->subDays(30))->count();

        return [
            'total_customers' => $totalCustomers,
            'active_customers' => $activeCustomers,
            'inactive_customers' => $totalCustomers - $activeCustomers,
            'new_customers' => $newCustomers,
            'activation_rate' => $totalCustomers > 0 ? ($activeCustomers / $totalCustomers) * 100 : 0,
        ];
    }

    public function getCoverageAnalysis(): array
    {
        $totalCustomers = $this->customers()->count();
        $visitedCustomers = $this->customers()
            ->whereHas('visits', function ($query) {
                $query->where('visit_date', '>=', now()->subDays(30))
                      ->where('status', CustomerVisit::STATUS_COMPLETED);
            })
            ->count();

        $unvisitedCustomers = $totalCustomers - $visitedCustomers;
        $coverageRate = $totalCustomers > 0 ? ($visitedCustomers / $totalCustomers) * 100 : 0;

        return [
            'total_customers' => $totalCustomers,
            'visited_customers' => $visitedCustomers,
            'unvisited_customers' => $unvisitedCustomers,
            'coverage_rate' => $coverageRate,
            'market_potential' => $this->market_potential,
            'population' => $this->population,
        ];
    }

    // Static Methods
    public static function getCompetitionLevels(): array
    {
        return [
            self::COMPETITION_LOW => 'Low',
            self::COMPETITION_MEDIUM => 'Medium',
            self::COMPETITION_HIGH => 'High',
            self::COMPETITION_VERY_HIGH => 'Very High',
        ];
    }

    public static function getRegions(): array
    {
        return [
            'baghdad' => 'Baghdad',
            'basra' => 'Basra',
            'erbil' => 'Erbil',
            'sulaymaniyah' => 'Sulaymaniyah',
            'dohuk' => 'Dohuk',
            'mosul' => 'Mosul',
            'kirkuk' => 'Kirkuk',
            'najaf' => 'Najaf',
            'karbala' => 'Karbala',
            'hillah' => 'Hillah',
            'ramadi' => 'Ramadi',
            'tikrit' => 'Tikrit',
            'samarra' => 'Samarra',
            'fallujah' => 'Fallujah',
            'amarah' => 'Amarah',
            'nasiriyah' => 'Nasiriyah',
            'diwaniyah' => 'Diwaniyah',
            'kut' => 'Kut',
        ];
    }
}
