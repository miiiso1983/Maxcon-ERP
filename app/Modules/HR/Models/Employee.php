<?php

namespace App\Modules\HR\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Models\User;

class Employee extends Model
{
    use HasFactory, SoftDeletes, HasTranslations, LogsActivity;

    protected $fillable = [
        'employee_code',
        'user_id',
        'first_name',
        'last_name',
        'arabic_name',
        'kurdish_name',
        'email',
        'phone',
        'national_id',
        'passport_number',
        'date_of_birth',
        'gender',
        'marital_status',
        'nationality',
        'address',
        'emergency_contact_name',
        'emergency_contact_phone',
        'hire_date',
        'termination_date',
        'employment_status',
        'job_title',
        'department_id',
        'manager_id',
        'salary_amount',
        'salary_currency',
        'salary_type',
        'bank_account_number',
        'bank_name',
        'tax_number',
        'social_security_number',
        'contract_type',
        'work_schedule_id',
        'photo',
        'documents',
        'notes',
        'is_active',
        'meta_data',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'hire_date' => 'date',
        'termination_date' => 'date',
        'salary_amount' => 'decimal:2',
        'documents' => 'array',
        'is_active' => 'boolean',
        'meta_data' => 'array',
    ];

    public $translatable = ['job_title', 'notes'];

    // Employment Status
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_TERMINATED = 'terminated';
    const STATUS_ON_LEAVE = 'on_leave';
    const STATUS_SUSPENDED = 'suspended';

    // Gender
    const GENDER_MALE = 'male';
    const GENDER_FEMALE = 'female';

    // Marital Status
    const MARITAL_SINGLE = 'single';
    const MARITAL_MARRIED = 'married';
    const MARITAL_DIVORCED = 'divorced';
    const MARITAL_WIDOWED = 'widowed';

    // Salary Types
    const SALARY_MONTHLY = 'monthly';
    const SALARY_HOURLY = 'hourly';
    const SALARY_DAILY = 'daily';

    // Contract Types
    const CONTRACT_PERMANENT = 'permanent';
    const CONTRACT_TEMPORARY = 'temporary';
    const CONTRACT_PART_TIME = 'part_time';
    const CONTRACT_FREELANCE = 'freelance';

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function manager()
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    public function subordinates()
    {
        return $this->hasMany(Employee::class, 'manager_id');
    }

    public function workSchedule()
    {
        return $this->belongsTo(WorkSchedule::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function payrollEntries()
    {
        return $this->hasMany(PayrollEntry::class);
    }

    public function performanceReviews()
    {
        return $this->hasMany(PerformanceReview::class);
    }

    public function trainings()
    {
        return $this->belongsToMany(Training::class, 'employee_trainings')
                    ->withPivot(['completion_date', 'score', 'certificate_path'])
                    ->withTimestamps();
    }

    public function medicalRep()
    {
        return $this->hasOne(\App\Modules\MedicalReps\Models\MedicalRep::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                     ->where('employment_status', self::STATUS_ACTIVE);
    }

    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeByManager($query, $managerId)
    {
        return $query->where('manager_id', $managerId);
    }

    // Accessors
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function getAgeAttribute(): ?int
    {
        return $this->date_of_birth ? $this->date_of_birth->age : null;
    }

    public function getTenureAttribute(): ?int
    {
        return $this->hire_date ? $this->hire_date->diffInDays(now()) : null;
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->employment_status) {
            self::STATUS_ACTIVE => 'success',
            self::STATUS_INACTIVE => 'secondary',
            self::STATUS_TERMINATED => 'danger',
            self::STATUS_ON_LEAVE => 'warning',
            self::STATUS_SUSPENDED => 'dark',
            default => 'light',
        };
    }

    public function getContractTypeColorAttribute(): string
    {
        return match($this->contract_type) {
            self::CONTRACT_PERMANENT => 'success',
            self::CONTRACT_TEMPORARY => 'warning',
            self::CONTRACT_PART_TIME => 'info',
            self::CONTRACT_FREELANCE => 'secondary',
            default => 'light',
        };
    }

    // Methods
    public function generateEmployeeCode(): string
    {
        $department = $this->department;
        $departmentCode = $department ? strtoupper(substr($department->name, 0, 3)) : 'GEN';
        $year = now()->format('y');
        $sequence = str_pad(Employee::count() + 1, 4, '0', STR_PAD_LEFT);
        
        return "{$departmentCode}{$year}{$sequence}";
    }

    public function calculateMonthlySalary(): float
    {
        switch ($this->salary_type) {
            case self::SALARY_MONTHLY:
                return $this->salary_amount;
            
            case self::SALARY_HOURLY:
                $workSchedule = $this->workSchedule;
                $hoursPerMonth = $workSchedule ? $workSchedule->hours_per_week * 4.33 : 160;
                return $this->salary_amount * $hoursPerMonth;
            
            case self::SALARY_DAILY:
                $workSchedule = $this->workSchedule;
                $daysPerMonth = $workSchedule ? $workSchedule->days_per_week * 4.33 : 22;
                return $this->salary_amount * $daysPerMonth;
            
            default:
                return 0;
        }
    }

    public function getCurrentLeaveBalance(): array
    {
        $currentYear = now()->year;
        $totalAnnualLeave = 30; // Default annual leave days
        
        $usedLeave = $this->leaveRequests()
            ->whereYear('start_date', $currentYear)
            ->where('status', LeaveRequest::STATUS_APPROVED)
            ->sum('days_requested');

        return [
            'total_annual' => $totalAnnualLeave,
            'used' => $usedLeave,
            'remaining' => $totalAnnualLeave - $usedLeave,
        ];
    }

    public function getAttendanceStats(string $month = null): array
    {
        $month = $month ?: now()->format('Y-m');
        
        $attendances = $this->attendances()
            ->where('date', 'like', $month . '%')
            ->get();

        $totalDays = $attendances->count();
        $presentDays = $attendances->where('status', Attendance::STATUS_PRESENT)->count();
        $lateDays = $attendances->where('is_late', true)->count();
        $overtimeDays = $attendances->where('overtime_hours', '>', 0)->count();
        
        $totalHours = $attendances->sum('hours_worked');
        $overtimeHours = $attendances->sum('overtime_hours');

        return [
            'total_days' => $totalDays,
            'present_days' => $presentDays,
            'absent_days' => $totalDays - $presentDays,
            'late_days' => $lateDays,
            'overtime_days' => $overtimeDays,
            'total_hours' => $totalHours,
            'overtime_hours' => $overtimeHours,
            'attendance_rate' => $totalDays > 0 ? ($presentDays / $totalDays) * 100 : 0,
        ];
    }

    public function getLatestPerformanceReview(): ?PerformanceReview
    {
        return $this->performanceReviews()
            ->latest('review_period_end')
            ->first();
    }

    public function canRequestLeave(int $days): bool
    {
        $leaveBalance = $this->getCurrentLeaveBalance();
        return $leaveBalance['remaining'] >= $days;
    }

    public function hasActiveLeave(): bool
    {
        return $this->leaveRequests()
            ->where('status', LeaveRequest::STATUS_APPROVED)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->exists();
    }

    public function getUpcomingBirthday(): ?int
    {
        if (!$this->date_of_birth) {
            return null;
        }

        $birthday = $this->date_of_birth->setYear(now()->year);
        if ($birthday->isPast()) {
            $birthday = $birthday->addYear();
        }

        return $birthday->diffInDays(now());
    }

    public function getWorkAnniversary(): ?int
    {
        if (!$this->hire_date) {
            return null;
        }

        $anniversary = $this->hire_date->setYear(now()->year);
        if ($anniversary->isPast()) {
            $anniversary = $anniversary->addYear();
        }

        return $anniversary->diffInDays(now());
    }

    // Activity Log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['first_name', 'last_name', 'email', 'employment_status', 'salary_amount'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Static Methods
    public static function getEmploymentStatuses(): array
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
            self::STATUS_TERMINATED => 'Terminated',
            self::STATUS_ON_LEAVE => 'On Leave',
            self::STATUS_SUSPENDED => 'Suspended',
        ];
    }

    public static function getGenders(): array
    {
        return [
            self::GENDER_MALE => 'Male',
            self::GENDER_FEMALE => 'Female',
        ];
    }

    public static function getMaritalStatuses(): array
    {
        return [
            self::MARITAL_SINGLE => 'Single',
            self::MARITAL_MARRIED => 'Married',
            self::MARITAL_DIVORCED => 'Divorced',
            self::MARITAL_WIDOWED => 'Widowed',
        ];
    }

    public static function getSalaryTypes(): array
    {
        return [
            self::SALARY_MONTHLY => 'Monthly',
            self::SALARY_HOURLY => 'Hourly',
            self::SALARY_DAILY => 'Daily',
        ];
    }

    public static function getContractTypes(): array
    {
        return [
            self::CONTRACT_PERMANENT => 'Permanent',
            self::CONTRACT_TEMPORARY => 'Temporary',
            self::CONTRACT_PART_TIME => 'Part Time',
            self::CONTRACT_FREELANCE => 'Freelance',
        ];
    }
}
