<?php

namespace App\Modules\HR\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'date',
        'check_in_time',
        'check_out_time',
        'break_start_time',
        'break_end_time',
        'hours_worked',
        'overtime_hours',
        'status',
        'is_late',
        'is_early_departure',
        'late_minutes',
        'early_departure_minutes',
        'notes',
        'approved_by',
        'ip_address',
        'location',
        'meta_data',
    ];

    protected $casts = [
        'date' => 'date',
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
        'break_start_time' => 'datetime',
        'break_end_time' => 'datetime',
        'hours_worked' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'is_late' => 'boolean',
        'is_early_departure' => 'boolean',
        'late_minutes' => 'integer',
        'early_departure_minutes' => 'integer',
        'meta_data' => 'array',
    ];

    // Status Constants
    const STATUS_PRESENT = 'present';
    const STATUS_ABSENT = 'absent';
    const STATUS_LATE = 'late';
    const STATUS_HALF_DAY = 'half_day';
    const STATUS_ON_LEAVE = 'on_leave';
    const STATUS_HOLIDAY = 'holiday';

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
    public function scopeForDate($query, $date)
    {
        return $query->where('date', $date);
    }

    public function scopeForMonth($query, $year, $month)
    {
        return $query->whereYear('date', $year)
                     ->whereMonth('date', $month);
    }

    public function scopePresent($query)
    {
        return $query->where('status', self::STATUS_PRESENT);
    }

    public function scopeLate($query)
    {
        return $query->where('is_late', true);
    }

    public function scopeOvertime($query)
    {
        return $query->where('overtime_hours', '>', 0);
    }

    // Accessors
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PRESENT => 'success',
            self::STATUS_ABSENT => 'danger',
            self::STATUS_LATE => 'warning',
            self::STATUS_HALF_DAY => 'info',
            self::STATUS_ON_LEAVE => 'secondary',
            self::STATUS_HOLIDAY => 'primary',
            default => 'light',
        };
    }

    public function getWorkDurationAttribute(): ?string
    {
        if (!$this->check_in_time || !$this->check_out_time) {
            return null;
        }

        $duration = $this->check_out_time->diff($this->check_in_time);
        return $duration->format('%H:%I');
    }

    public function getBreakDurationAttribute(): ?string
    {
        if (!$this->break_start_time || !$this->break_end_time) {
            return null;
        }

        $duration = $this->break_end_time->diff($this->break_start_time);
        return $duration->format('%H:%I');
    }

    // Methods
    public function checkIn(Carbon $time = null, array $metadata = []): void
    {
        $time = $time ?: now();
        $this->check_in_time = $time;
        
        // Check if late
        $workSchedule = $this->employee->workSchedule;
        if ($workSchedule && $workSchedule->start_time) {
            $expectedStartTime = Carbon::parse($this->date->format('Y-m-d') . ' ' . $workSchedule->start_time);
            if ($time->gt($expectedStartTime)) {
                $this->is_late = true;
                $this->late_minutes = $time->diffInMinutes($expectedStartTime);
                $this->status = self::STATUS_LATE;
            } else {
                $this->status = self::STATUS_PRESENT;
            }
        } else {
            $this->status = self::STATUS_PRESENT;
        }

        $this->meta_data = array_merge($this->meta_data ?? [], $metadata);
        $this->save();
    }

    public function checkOut(Carbon $time = null, array $metadata = []): void
    {
        $time = $time ?: now();
        $this->check_out_time = $time;

        // Calculate hours worked
        if ($this->check_in_time) {
            $totalMinutes = $this->check_in_time->diffInMinutes($time);
            
            // Subtract break time if recorded
            if ($this->break_start_time && $this->break_end_time) {
                $breakMinutes = $this->break_start_time->diffInMinutes($this->break_end_time);
                $totalMinutes -= $breakMinutes;
            }

            $this->hours_worked = $totalMinutes / 60;

            // Calculate overtime
            $workSchedule = $this->employee->workSchedule;
            if ($workSchedule && $workSchedule->hours_per_day) {
                $regularHours = $workSchedule->hours_per_day;
                if ($this->hours_worked > $regularHours) {
                    $this->overtime_hours = $this->hours_worked - $regularHours;
                }
            }

            // Check for early departure
            if ($workSchedule && $workSchedule->end_time) {
                $expectedEndTime = Carbon::parse($this->date->format('Y-m-d') . ' ' . $workSchedule->end_time);
                if ($time->lt($expectedEndTime)) {
                    $this->is_early_departure = true;
                    $this->early_departure_minutes = $expectedEndTime->diffInMinutes($time);
                }
            }
        }

        $this->meta_data = array_merge($this->meta_data ?? [], $metadata);
        $this->save();
    }

    public function startBreak(Carbon $time = null): void
    {
        $time = $time ?: now();
        $this->break_start_time = $time;
        $this->save();
    }

    public function endBreak(Carbon $time = null): void
    {
        $time = $time ?: now();
        $this->break_end_time = $time;
        $this->save();
    }

    public function markAbsent(string $reason = null): void
    {
        $this->status = self::STATUS_ABSENT;
        $this->hours_worked = 0;
        $this->notes = $reason;
        $this->save();
    }

    public function markHalfDay(string $reason = null): void
    {
        $this->status = self::STATUS_HALF_DAY;
        
        $workSchedule = $this->employee->workSchedule;
        if ($workSchedule && $workSchedule->hours_per_day) {
            $this->hours_worked = $workSchedule->hours_per_day / 2;
        } else {
            $this->hours_worked = 4; // Default half day
        }
        
        $this->notes = $reason;
        $this->save();
    }

    public function approve(Employee $approver, string $notes = null): void
    {
        $this->approved_by = $approver->id;
        if ($notes) {
            $this->notes = $notes;
        }
        $this->save();
    }

    // Static Methods
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PRESENT => 'Present',
            self::STATUS_ABSENT => 'Absent',
            self::STATUS_LATE => 'Late',
            self::STATUS_HALF_DAY => 'Half Day',
            self::STATUS_ON_LEAVE => 'On Leave',
            self::STATUS_HOLIDAY => 'Holiday',
        ];
    }

    public static function createDailyAttendance(Employee $employee, Carbon $date): self
    {
        return self::firstOrCreate([
            'employee_id' => $employee->id,
            'date' => $date,
        ], [
            'status' => self::STATUS_ABSENT,
            'hours_worked' => 0,
        ]);
    }

    public static function bulkCreateAttendance(array $employeeIds, Carbon $date): int
    {
        $created = 0;
        
        foreach ($employeeIds as $employeeId) {
            $attendance = self::firstOrCreate([
                'employee_id' => $employeeId,
                'date' => $date,
            ], [
                'status' => self::STATUS_ABSENT,
                'hours_worked' => 0,
            ]);

            if ($attendance->wasRecentlyCreated) {
                $created++;
            }
        }

        return $created;
    }

    public static function getAttendanceReport(array $employeeIds, Carbon $startDate, Carbon $endDate): array
    {
        $attendances = self::whereIn('employee_id', $employeeIds)
            ->whereBetween('date', [$startDate, $endDate])
            ->with('employee')
            ->get()
            ->groupBy('employee_id');

        $report = [];
        
        foreach ($attendances as $employeeId => $employeeAttendances) {
            $employee = $employeeAttendances->first()->employee;
            
            $totalDays = $employeeAttendances->count();
            $presentDays = $employeeAttendances->where('status', self::STATUS_PRESENT)->count();
            $lateDays = $employeeAttendances->where('is_late', true)->count();
            $totalHours = $employeeAttendances->sum('hours_worked');
            $overtimeHours = $employeeAttendances->sum('overtime_hours');

            $report[] = [
                'employee' => $employee,
                'total_days' => $totalDays,
                'present_days' => $presentDays,
                'absent_days' => $totalDays - $presentDays,
                'late_days' => $lateDays,
                'total_hours' => $totalHours,
                'overtime_hours' => $overtimeHours,
                'attendance_rate' => $totalDays > 0 ? ($presentDays / $totalDays) * 100 : 0,
            ];
        }

        return $report;
    }
}
