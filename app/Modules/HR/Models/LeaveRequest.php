<?php

namespace App\Modules\HR\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'leave_type',
        'start_date',
        'end_date',
        'days_requested',
        'reason',
        'status',
        'approved_by',
        'approved_at',
        'rejected_reason',
        'emergency_contact',
        'emergency_phone',
        'handover_notes',
        'documents',
        'meta_data',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
        'documents' => 'array',
        'meta_data' => 'array',
    ];

    // Leave Types
    const TYPE_ANNUAL = 'annual';
    const TYPE_SICK = 'sick';
    const TYPE_MATERNITY = 'maternity';
    const TYPE_PATERNITY = 'paternity';
    const TYPE_EMERGENCY = 'emergency';
    const TYPE_UNPAID = 'unpaid';
    const TYPE_STUDY = 'study';
    const TYPE_RELIGIOUS = 'religious';
    const TYPE_BEREAVEMENT = 'bereavement';

    // Status
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_CANCELLED = 'cancelled';

    // Relationships
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeForYear($query, $year)
    {
        return $query->whereYear('start_date', $year);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('leave_type', $type);
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_APPROVED)
                     ->where('start_date', '<=', now())
                     ->where('end_date', '>=', now());
    }

    // Accessors
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_APPROVED => 'success',
            self::STATUS_REJECTED => 'danger',
            self::STATUS_CANCELLED => 'secondary',
            default => 'light',
        };
    }

    public function getTypeColorAttribute(): string
    {
        return match($this->leave_type) {
            self::TYPE_ANNUAL => 'primary',
            self::TYPE_SICK => 'danger',
            self::TYPE_MATERNITY, self::TYPE_PATERNITY => 'info',
            self::TYPE_EMERGENCY => 'warning',
            self::TYPE_UNPAID => 'secondary',
            self::TYPE_STUDY => 'success',
            self::TYPE_RELIGIOUS => 'purple',
            self::TYPE_BEREAVEMENT => 'dark',
            default => 'light',
        };
    }

    public function getDurationAttribute(): int
    {
        return $this->start_date->diffInDays($this->end_date) + 1;
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === self::STATUS_APPROVED &&
               $this->start_date <= now() &&
               $this->end_date >= now();
    }

    public function getIsUpcomingAttribute(): bool
    {
        return $this->status === self::STATUS_APPROVED &&
               $this->start_date > now();
    }

    // Methods
    public function calculateDays(): int
    {
        $days = 0;
        $current = $this->start_date->copy();
        
        while ($current <= $this->end_date) {
            // Skip weekends for most leave types
            if (!in_array($this->leave_type, [self::TYPE_SICK, self::TYPE_EMERGENCY]) && 
                !$current->isWeekend()) {
                $days++;
            } elseif (in_array($this->leave_type, [self::TYPE_SICK, self::TYPE_EMERGENCY])) {
                $days++;
            }
            
            $current->addDay();
        }

        $this->days_requested = $days;
        return $days;
    }

    public function approve(Employee $approver, string $notes = null): bool
    {
        // Check if employee has sufficient leave balance
        if (!$this->employee->canRequestLeave($this->days_requested)) {
            return false;
        }

        $this->status = self::STATUS_APPROVED;
        $this->approved_by = $approver->id;
        $this->approved_at = now();
        
        if ($notes) {
            $this->meta_data = array_merge($this->meta_data ?? [], ['approval_notes' => $notes]);
        }

        $this->save();

        // Create attendance records for leave period
        $this->createAttendanceRecords();

        return true;
    }

    public function reject(Employee $approver, string $reason): void
    {
        $this->status = self::STATUS_REJECTED;
        $this->approved_by = $approver->id;
        $this->approved_at = now();
        $this->rejected_reason = $reason;
        $this->save();
    }

    public function cancel(string $reason = null): void
    {
        $this->status = self::STATUS_CANCELLED;
        
        if ($reason) {
            $this->meta_data = array_merge($this->meta_data ?? [], ['cancellation_reason' => $reason]);
        }

        $this->save();

        // Remove attendance records if leave was approved
        if ($this->approved_at) {
            $this->removeAttendanceRecords();
        }
    }

    private function createAttendanceRecords(): void
    {
        $current = $this->start_date->copy();
        
        while ($current <= $this->end_date) {
            Attendance::updateOrCreate([
                'employee_id' => $this->employee_id,
                'date' => $current,
            ], [
                'status' => Attendance::STATUS_ON_LEAVE,
                'hours_worked' => 0,
                'notes' => "On {$this->leave_type} leave",
            ]);
            
            $current->addDay();
        }
    }

    private function removeAttendanceRecords(): void
    {
        Attendance::where('employee_id', $this->employee_id)
            ->whereBetween('date', [$this->start_date, $this->end_date])
            ->where('status', Attendance::STATUS_ON_LEAVE)
            ->delete();
    }

    public function hasConflict(): bool
    {
        return self::where('employee_id', $this->employee_id)
            ->where('id', '!=', $this->id)
            ->where('status', self::STATUS_APPROVED)
            ->where(function ($query) {
                $query->whereBetween('start_date', [$this->start_date, $this->end_date])
                      ->orWhereBetween('end_date', [$this->start_date, $this->end_date])
                      ->orWhere(function ($q) {
                          $q->where('start_date', '<=', $this->start_date)
                            ->where('end_date', '>=', $this->end_date);
                      });
            })
            ->exists();
    }

    public function getConflictingRequests()
    {
        return self::where('employee_id', $this->employee_id)
            ->where('id', '!=', $this->id)
            ->where('status', self::STATUS_APPROVED)
            ->where(function ($query) {
                $query->whereBetween('start_date', [$this->start_date, $this->end_date])
                      ->orWhereBetween('end_date', [$this->start_date, $this->end_date])
                      ->orWhere(function ($q) {
                          $q->where('start_date', '<=', $this->start_date)
                            ->where('end_date', '>=', $this->end_date);
                      });
            })
            ->get();
    }

    // Static Methods
    public static function getLeaveTypes(): array
    {
        return [
            self::TYPE_ANNUAL => 'Annual Leave',
            self::TYPE_SICK => 'Sick Leave',
            self::TYPE_MATERNITY => 'Maternity Leave',
            self::TYPE_PATERNITY => 'Paternity Leave',
            self::TYPE_EMERGENCY => 'Emergency Leave',
            self::TYPE_UNPAID => 'Unpaid Leave',
            self::TYPE_STUDY => 'Study Leave',
            self::TYPE_RELIGIOUS => 'Religious Leave',
            self::TYPE_BEREAVEMENT => 'Bereavement Leave',
        ];
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    public static function getLeaveBalance(Employee $employee, int $year = null): array
    {
        $year = $year ?: now()->year;
        
        $leaveTypes = [
            self::TYPE_ANNUAL => 30,
            self::TYPE_SICK => 15,
            self::TYPE_EMERGENCY => 5,
        ];

        $balance = [];
        
        foreach ($leaveTypes as $type => $allocation) {
            $used = self::where('employee_id', $employee->id)
                ->where('leave_type', $type)
                ->where('status', self::STATUS_APPROVED)
                ->whereYear('start_date', $year)
                ->sum('days_requested');

            $balance[$type] = [
                'allocated' => $allocation,
                'used' => $used,
                'remaining' => $allocation - $used,
            ];
        }

        return $balance;
    }

    public static function getPendingApprovals(Employee $manager): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('status', self::STATUS_PENDING)
            ->whereHas('employee', function ($query) use ($manager) {
                $query->where('manager_id', $manager->id);
            })
            ->with(['employee', 'employee.department'])
            ->orderBy('created_at')
            ->get();
    }

    public static function getUpcomingLeaves(int $days = 7): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('status', self::STATUS_APPROVED)
            ->where('start_date', '>', now())
            ->where('start_date', '<=', now()->addDays($days))
            ->with(['employee', 'employee.department'])
            ->orderBy('start_date')
            ->get();
    }
}
