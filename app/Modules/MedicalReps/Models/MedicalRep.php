<?php

namespace App\Modules\MedicalReps\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Modules\HR\Models\Employee;
use App\Modules\Customer\Models\Customer;

class MedicalRep extends Model
{
    use HasFactory, SoftDeletes, HasTranslations, LogsActivity;

    protected $fillable = [
        'employee_id',
        'rep_code',
        'specialization',
        'license_number',
        'license_expiry',
        'territory_id',
        'supervisor_id',
        'commission_rate',
        'base_salary',
        'target_monthly',
        'target_quarterly',
        'target_annual',
        'vehicle_info',
        'phone_allowance',
        'fuel_allowance',
        'medical_allowance',
        'education_level',
        'certifications',
        'languages_spoken',
        'start_date',
        'end_date',
        'status',
        'performance_rating',
        'notes',
        'is_active',
        'meta_data',
    ];

    protected $casts = [
        'license_expiry' => 'date',
        'commission_rate' => 'decimal:4',
        'base_salary' => 'decimal:2',
        'target_monthly' => 'decimal:2',
        'target_quarterly' => 'decimal:2',
        'target_annual' => 'decimal:2',
        'phone_allowance' => 'decimal:2',
        'fuel_allowance' => 'decimal:2',
        'medical_allowance' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'certifications' => 'array',
        'languages_spoken' => 'array',
        'vehicle_info' => 'array',
        'is_active' => 'boolean',
        'meta_data' => 'array',
    ];

    public $translatable = ['specialization', 'notes'];

    // Status Constants
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_TERMINATED = 'terminated';
    const STATUS_ON_LEAVE = 'on_leave';

    // Performance Ratings
    const RATING_EXCELLENT = 'excellent';
    const RATING_GOOD = 'good';
    const RATING_AVERAGE = 'average';
    const RATING_BELOW_AVERAGE = 'below_average';
    const RATING_POOR = 'poor';

    // Relationships
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function territory()
    {
        return $this->belongsTo(Territory::class);
    }

    public function supervisor()
    {
        return $this->belongsTo(MedicalRep::class, 'supervisor_id');
    }

    public function subordinates()
    {
        return $this->hasMany(MedicalRep::class, 'supervisor_id');
    }

    public function visits()
    {
        return $this->hasMany(CustomerVisit::class);
    }

    public function sales()
    {
        return $this->hasMany(\App\Modules\Sales\Models\Sale::class, 'medical_rep_id');
    }

    public function commissions()
    {
        return $this->hasMany(Commission::class);
    }

    public function expenses()
    {
        return $this->hasMany(RepExpense::class);
    }

    public function customers()
    {
        return $this->belongsToMany(Customer::class, 'rep_customers')
                    ->withPivot(['assigned_date', 'priority', 'notes'])
                    ->withTimestamps();
    }

    public function routes()
    {
        return $this->hasMany(RepRoute::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                     ->where('status', self::STATUS_ACTIVE);
    }

    public function scopeByTerritory($query, $territoryId)
    {
        return $query->where('territory_id', $territoryId);
    }

    public function scopeBySupervisor($query, $supervisorId)
    {
        return $query->where('supervisor_id', $supervisorId);
    }

    public function scopeByPerformance($query, $rating)
    {
        return $query->where('performance_rating', $rating);
    }

    // Accessors
    public function getFullNameAttribute(): string
    {
        return $this->employee ? $this->employee->full_name : 'N/A';
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_ACTIVE => 'success',
            self::STATUS_INACTIVE => 'secondary',
            self::STATUS_SUSPENDED => 'warning',
            self::STATUS_TERMINATED => 'danger',
            self::STATUS_ON_LEAVE => 'info',
            default => 'light',
        };
    }

    public function getPerformanceColorAttribute(): string
    {
        return match($this->performance_rating) {
            self::RATING_EXCELLENT => 'success',
            self::RATING_GOOD => 'info',
            self::RATING_AVERAGE => 'warning',
            self::RATING_BELOW_AVERAGE => 'danger',
            self::RATING_POOR => 'dark',
            default => 'light',
        };
    }

    public function getTenureAttribute(): ?int
    {
        return $this->start_date ? $this->start_date->diffInDays(now()) : null;
    }

    // Methods
    public function generateRepCode(): string
    {
        $territory = $this->territory;
        $territoryCode = $territory ? strtoupper(substr($territory->name, 0, 3)) : 'GEN';
        $year = now()->format('y');
        $sequence = str_pad(MedicalRep::count() + 1, 3, '0', STR_PAD_LEFT);
        
        return "REP{$territoryCode}{$year}{$sequence}";
    }

    public function calculateMonthlyCommission(string $month = null): array
    {
        $month = $month ?: now()->format('Y-m');
        
        $sales = $this->sales()
            ->where('sale_date', 'like', $month . '%')
            ->where('status', 'completed')
            ->get();

        $totalSales = $sales->sum('total_amount');
        $commission = $totalSales * $this->commission_rate;
        
        $targetAchievement = $this->target_monthly > 0 ? ($totalSales / $this->target_monthly) * 100 : 0;

        return [
            'month' => $month,
            'total_sales' => $totalSales,
            'commission_rate' => $this->commission_rate,
            'commission_amount' => $commission,
            'target_monthly' => $this->target_monthly,
            'target_achievement' => $targetAchievement,
            'sales_count' => $sales->count(),
        ];
    }

    public function getVisitStats(string $month = null): array
    {
        $month = $month ?: now()->format('Y-m');
        
        $visits = $this->visits()
            ->where('visit_date', 'like', $month . '%')
            ->get();

        $plannedVisits = $visits->where('status', CustomerVisit::STATUS_PLANNED)->count();
        $completedVisits = $visits->where('status', CustomerVisit::STATUS_COMPLETED)->count();
        $cancelledVisits = $visits->where('status', CustomerVisit::STATUS_CANCELLED)->count();
        $missedVisits = $visits->where('status', CustomerVisit::STATUS_MISSED)->count();

        return [
            'total_visits' => $visits->count(),
            'planned_visits' => $plannedVisits,
            'completed_visits' => $completedVisits,
            'cancelled_visits' => $cancelledVisits,
            'missed_visits' => $missedVisits,
            'completion_rate' => $visits->count() > 0 ? ($completedVisits / $visits->count()) * 100 : 0,
            'unique_customers' => $visits->pluck('customer_id')->unique()->count(),
        ];
    }

    public function getPerformanceMetrics(string $period = 'month'): array
    {
        $startDate = match($period) {
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'quarter' => now()->startOfQuarter(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };

        $sales = $this->sales()
            ->where('sale_date', '>=', $startDate)
            ->where('status', 'completed')
            ->get();

        $visits = $this->visits()
            ->where('visit_date', '>=', $startDate)
            ->get();

        $totalSales = $sales->sum('total_amount');
        $salesCount = $sales->count();
        $visitCount = $visits->count();
        $completedVisits = $visits->where('status', CustomerVisit::STATUS_COMPLETED)->count();

        $target = match($period) {
            'month' => $this->target_monthly,
            'quarter' => $this->target_quarterly,
            'year' => $this->target_annual,
            default => $this->target_monthly,
        };

        return [
            'period' => $period,
            'total_sales' => $totalSales,
            'sales_count' => $salesCount,
            'visit_count' => $visitCount,
            'completed_visits' => $completedVisits,
            'target' => $target,
            'target_achievement' => $target > 0 ? ($totalSales / $target) * 100 : 0,
            'average_sale_value' => $salesCount > 0 ? $totalSales / $salesCount : 0,
            'visit_completion_rate' => $visitCount > 0 ? ($completedVisits / $visitCount) * 100 : 0,
            'sales_per_visit' => $completedVisits > 0 ? $salesCount / $completedVisits : 0,
        ];
    }

    public function getCustomerPortfolio(): array
    {
        $customers = $this->customers;
        $totalCustomers = $customers->count();
        
        $activeCustomers = $customers->filter(function ($customer) {
            return $customer->sales()->where('created_at', '>=', now()->subDays(90))->exists();
        })->count();

        $highPriorityCustomers = $customers->where('pivot.priority', 'high')->count();
        $mediumPriorityCustomers = $customers->where('pivot.priority', 'medium')->count();
        $lowPriorityCustomers = $customers->where('pivot.priority', 'low')->count();

        return [
            'total_customers' => $totalCustomers,
            'active_customers' => $activeCustomers,
            'inactive_customers' => $totalCustomers - $activeCustomers,
            'high_priority' => $highPriorityCustomers,
            'medium_priority' => $mediumPriorityCustomers,
            'low_priority' => $lowPriorityCustomers,
            'activation_rate' => $totalCustomers > 0 ? ($activeCustomers / $totalCustomers) * 100 : 0,
        ];
    }

    public function getTodaySchedule(): array
    {
        $today = now()->format('Y-m-d');
        
        $visits = $this->visits()
            ->where('visit_date', $today)
            ->with('customer')
            ->orderBy('visit_time')
            ->get();

        $routes = $this->routes()
            ->where('route_date', $today)
            ->with('customers')
            ->first();

        return [
            'visits' => $visits,
            'route' => $routes,
            'total_visits' => $visits->count(),
            'completed_visits' => $visits->where('status', CustomerVisit::STATUS_COMPLETED)->count(),
            'pending_visits' => $visits->where('status', CustomerVisit::STATUS_PLANNED)->count(),
        ];
    }

    public function calculateTotalEarnings(string $month = null): array
    {
        $month = $month ?: now()->format('Y-m');
        
        $commission = $this->calculateMonthlyCommission($month);
        $expenses = $this->expenses()
            ->where('expense_date', 'like', $month . '%')
            ->where('status', RepExpense::STATUS_APPROVED)
            ->sum('amount');

        $totalEarnings = $this->base_salary + $commission['commission_amount'] + 
                        $this->phone_allowance + $this->fuel_allowance + $this->medical_allowance;

        return [
            'base_salary' => $this->base_salary,
            'commission' => $commission['commission_amount'],
            'phone_allowance' => $this->phone_allowance,
            'fuel_allowance' => $this->fuel_allowance,
            'medical_allowance' => $this->medical_allowance,
            'approved_expenses' => $expenses,
            'total_earnings' => $totalEarnings,
            'net_earnings' => $totalEarnings + $expenses,
        ];
    }

    public function isLicenseExpiring(int $days = 30): bool
    {
        return $this->license_expiry && 
               $this->license_expiry->diffInDays(now()) <= $days &&
               $this->license_expiry->isFuture();
    }

    public function getUpcomingVisits(int $days = 7): \Illuminate\Database\Eloquent\Collection
    {
        return $this->visits()
            ->where('visit_date', '>=', now())
            ->where('visit_date', '<=', now()->addDays($days))
            ->where('status', CustomerVisit::STATUS_PLANNED)
            ->with('customer')
            ->orderBy('visit_date')
            ->orderBy('visit_time')
            ->get();
    }

    // Activity Log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'territory_id', 'commission_rate', 'performance_rating'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Static Methods
    public static function getStatuses(): array
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
            self::STATUS_SUSPENDED => 'Suspended',
            self::STATUS_TERMINATED => 'Terminated',
            self::STATUS_ON_LEAVE => 'On Leave',
        ];
    }

    public static function getPerformanceRatings(): array
    {
        return [
            self::RATING_EXCELLENT => 'Excellent',
            self::RATING_GOOD => 'Good',
            self::RATING_AVERAGE => 'Average',
            self::RATING_BELOW_AVERAGE => 'Below Average',
            self::RATING_POOR => 'Poor',
        ];
    }

    public static function getTopPerformers(int $limit = 10, string $period = 'month'): \Illuminate\Database\Eloquent\Collection
    {
        $startDate = match($period) {
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'quarter' => now()->startOfQuarter(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };

        return self::active()
            ->with(['employee', 'territory'])
            ->withSum(['sales' => function ($query) use ($startDate) {
                $query->where('sale_date', '>=', $startDate)
                      ->where('status', 'completed');
            }], 'total_amount')
            ->orderByDesc('sales_sum_total_amount')
            ->take($limit)
            ->get();
    }
}
