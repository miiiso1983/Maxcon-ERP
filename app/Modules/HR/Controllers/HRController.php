<?php

namespace App\Modules\HR\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\HR\Models\Employee;
use App\Modules\HR\Models\Department;
use App\Modules\HR\Models\Attendance;
use App\Modules\HR\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HRController extends Controller
{
    public function dashboard()
    {
        // Get HR metrics
        $metrics = $this->getHRMetrics();
        
        // Get recent activities
        $recentActivities = $this->getRecentActivities();
        
        // Get upcoming events
        $upcomingEvents = $this->getUpcomingEvents();
        
        // Get pending approvals
        $pendingApprovals = $this->getPendingApprovals();

        return view('tenant.hr.dashboard', compact('metrics', 'recentActivities', 'upcomingEvents', 'pendingApprovals'));
    }

    public function employees()
    {
        $employees = Employee::with(['department', 'manager'])
            ->active()
            ->paginate(20);

        $departments = Department::active()->get();
        $totalEmployees = Employee::active()->count();
        $newHires = Employee::where('hire_date', '>=', now()->subDays(30))->count();

        return view('tenant.hr.employees.index', compact('employees', 'departments', 'totalEmployees', 'newHires'));
    }

    public function createEmployee()
    {
        $departments = Department::active()->get();
        $managers = Employee::active()->get();
        
        return view('tenant.hr.employees.create', compact('departments', 'managers'));
    }

    public function storeEmployee(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email',
            'phone' => 'required|string|max:20',
            'national_id' => 'required|string|unique:employees,national_id',
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:' . implode(',', array_keys(Employee::getGenders())),
            'hire_date' => 'required|date',
            'department_id' => 'required|exists:departments,id',
            'job_title' => 'required|array',
            'salary_amount' => 'required|numeric|min:0',
            'salary_type' => 'required|in:' . implode(',', array_keys(Employee::getSalaryTypes())),
            'contract_type' => 'required|in:' . implode(',', array_keys(Employee::getContractTypes())),
        ]);

        DB::beginTransaction();
        try {
            $employee = Employee::create($request->all());
            $employee->employee_code = $employee->generateEmployeeCode();
            $employee->save();

            DB::commit();
            
            return redirect()->route('hr.employees')
                ->with('success', 'Employee created successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to create employee: ' . $e->getMessage()]);
        }
    }

    public function showEmployee(Employee $employee)
    {
        $employee->load(['department', 'manager', 'subordinates']);
        
        // Get attendance stats for current month
        $attendanceStats = $employee->getAttendanceStats();
        
        // Get leave balance
        $leaveBalance = $employee->getCurrentLeaveBalance();
        
        // Get recent attendance
        $recentAttendance = $employee->attendances()
            ->latest('date')
            ->take(10)
            ->get();
        
        // Get recent leave requests
        $recentLeaves = $employee->leaveRequests()
            ->latest()
            ->take(5)
            ->get();

        return view('tenant.hr.employees.show', compact(
            'employee', 'attendanceStats', 'leaveBalance', 'recentAttendance', 'recentLeaves'
        ));
    }

    public function attendance()
    {
        $today = now()->format('Y-m-d');
        $currentMonth = now()->format('Y-m');
        
        // Today's attendance summary
        $todayStats = [
            'total_employees' => Employee::active()->count(),
            'present' => Attendance::forDate($today)->present()->count(),
            'absent' => Attendance::forDate($today)->where('status', Attendance::STATUS_ABSENT)->count(),
            'late' => Attendance::forDate($today)->late()->count(),
            'on_leave' => Attendance::forDate($today)->where('status', Attendance::STATUS_ON_LEAVE)->count(),
        ];

        // Recent attendance records
        $recentAttendance = Attendance::with('employee')
            ->forDate($today)
            ->latest('check_in_time')
            ->take(10)
            ->get();

        // Monthly attendance summary
        $monthlyStats = $this->getMonthlyAttendanceStats($currentMonth);

        return view('tenant.hr.attendance.index', compact('todayStats', 'recentAttendance', 'monthlyStats'));
    }

    public function markAttendance(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'action' => 'required|in:check_in,check_out,start_break,end_break',
            'time' => 'sometimes|date_format:H:i',
            'notes' => 'sometimes|string|max:500',
        ]);

        $employee = Employee::findOrFail($request->employee_id);
        $today = now()->format('Y-m-d');
        $time = $request->time ? Carbon::parse($today . ' ' . $request->time) : now();
        
        $attendance = Attendance::firstOrCreate([
            'employee_id' => $employee->id,
            'date' => $today,
        ]);

        $metadata = [
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ];

        if ($request->notes) {
            $metadata['notes'] = $request->notes;
        }

        switch ($request->action) {
            case 'check_in':
                $attendance->checkIn($time, $metadata);
                $message = 'Check-in recorded successfully';
                break;
            
            case 'check_out':
                $attendance->checkOut($time, $metadata);
                $message = 'Check-out recorded successfully';
                break;
            
            case 'start_break':
                $attendance->startBreak($time);
                $message = 'Break started';
                break;
            
            case 'end_break':
                $attendance->endBreak($time);
                $message = 'Break ended';
                break;
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'attendance' => $attendance->fresh(),
        ]);
    }

    public function leaveRequests()
    {
        $leaveRequests = LeaveRequest::with(['employee', 'approvedBy'])
            ->latest()
            ->paginate(20);

        $pendingCount = LeaveRequest::pending()->count();
        $approvedThisMonth = LeaveRequest::approved()
            ->whereMonth('start_date', now()->month)
            ->count();

        return view('tenant.hr.leave.index', compact('leaveRequests', 'pendingCount', 'approvedThisMonth'));
    }

    public function createLeaveRequest()
    {
        $employees = Employee::active()->get();
        $leaveTypes = LeaveRequest::getLeaveTypes();
        
        return view('tenant.hr.leave.create', compact('employees', 'leaveTypes'));
    }

    public function storeLeaveRequest(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'leave_type' => 'required|in:' . implode(',', array_keys(LeaveRequest::getLeaveTypes())),
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
            'emergency_contact' => 'sometimes|string|max:255',
            'emergency_phone' => 'sometimes|string|max:20',
            'handover_notes' => 'sometimes|string|max:1000',
        ]);

        $leaveRequest = new LeaveRequest($request->all());
        $leaveRequest->calculateDays();

        // Check for conflicts
        if ($leaveRequest->hasConflict()) {
            return back()->withErrors(['error' => 'Leave request conflicts with existing approved leave']);
        }

        // Check leave balance
        $employee = Employee::findOrFail($request->employee_id);
        if (!$employee->canRequestLeave($leaveRequest->days_requested)) {
            return back()->withErrors(['error' => 'Insufficient leave balance']);
        }

        $leaveRequest->save();

        return redirect()->route('hr.leave-requests')
            ->with('success', 'Leave request submitted successfully');
    }

    public function approveLeaveRequest(Request $request, LeaveRequest $leaveRequest)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'notes' => 'sometimes|string|max:500',
        ]);

        $currentUser = auth()->user();
        $employee = Employee::where('user_id', $currentUser->id)->first();

        if (!$employee) {
            return response()->json(['error' => 'Employee record not found'], 403);
        }

        if ($request->action === 'approve') {
            if ($leaveRequest->approve($employee, $request->notes)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Leave request approved successfully',
                ]);
            } else {
                return response()->json(['error' => 'Failed to approve leave request'], 400);
            }
        } else {
            $leaveRequest->reject($employee, $request->notes ?? 'No reason provided');
            return response()->json([
                'success' => true,
                'message' => 'Leave request rejected',
            ]);
        }
    }

    public function departments()
    {
        $departments = Department::with(['manager', 'employees'])
            ->withCount('employees')
            ->get();

        return view('tenant.hr.departments.index', compact('departments'));
    }

    public function attendanceReport(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'department_id' => 'sometimes|exists:departments,id',
            'employee_ids' => 'sometimes|array',
            'employee_ids.*' => 'exists:employees,id',
        ]);

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        $query = Employee::active();
        
        if ($request->department_id) {
            $query->where('department_id', $request->department_id);
        }
        
        if ($request->employee_ids) {
            $query->whereIn('id', $request->employee_ids);
        }

        $employees = $query->get();
        $employeeIds = $employees->pluck('id')->toArray();

        $report = Attendance::getAttendanceReport($employeeIds, $startDate, $endDate);

        return view('tenant.hr.reports.attendance', compact('report', 'startDate', 'endDate'));
    }

    private function getHRMetrics(): array
    {
        $today = now()->format('Y-m-d');
        $thisMonth = now()->format('Y-m');

        return [
            'total_employees' => Employee::active()->count(),
            'present_today' => Attendance::forDate($today)->present()->count(),
            'pending_leaves' => LeaveRequest::pending()->count(),
            'new_hires_month' => Employee::whereMonth('hire_date', now()->month)->count(),
            'departments' => Department::active()->count(),
            'attendance_rate' => $this->calculateAttendanceRate($thisMonth),
        ];
    }

    private function getRecentActivities(): array
    {
        $activities = [];

        // Recent hires
        $recentHires = Employee::where('hire_date', '>=', now()->subDays(7))
            ->latest('hire_date')
            ->take(5)
            ->get();

        foreach ($recentHires as $employee) {
            $activities[] = [
                'type' => 'hire',
                'message' => "New employee {$employee->full_name} joined {$employee->department->name}",
                'date' => $employee->hire_date,
                'icon' => 'user-plus',
                'color' => 'success',
            ];
        }

        // Recent leave approvals
        $recentLeaves = LeaveRequest::approved()
            ->where('approved_at', '>=', now()->subDays(7))
            ->with('employee')
            ->latest('approved_at')
            ->take(5)
            ->get();

        foreach ($recentLeaves as $leave) {
            $activities[] = [
                'type' => 'leave',
                'message' => "{$leave->employee->full_name} approved for {$leave->leave_type} leave",
                'date' => $leave->approved_at,
                'icon' => 'calendar-check',
                'color' => 'info',
            ];
        }

        // Sort by date
        usort($activities, fn($a, $b) => $b['date'] <=> $a['date']);

        return array_slice($activities, 0, 10);
    }

    private function getUpcomingEvents(): array
    {
        $events = [];

        // Upcoming birthdays
        $upcomingBirthdays = Employee::active()
            ->get()
            ->filter(function ($employee) {
                $days = $employee->getUpcomingBirthday();
                return $days !== null && $days <= 7;
            })
            ->sortBy(fn($employee) => $employee->getUpcomingBirthday());

        foreach ($upcomingBirthdays as $employee) {
            $days = $employee->getUpcomingBirthday();
            $events[] = [
                'type' => 'birthday',
                'title' => "{$employee->full_name}'s Birthday",
                'date' => $days === 0 ? 'Today' : "In {$days} days",
                'icon' => 'birthday-cake',
                'color' => 'warning',
            ];
        }

        // Upcoming leaves
        $upcomingLeaves = LeaveRequest::getUpcomingLeaves(7);
        foreach ($upcomingLeaves as $leave) {
            $events[] = [
                'type' => 'leave',
                'title' => "{$leave->employee->full_name} - {$leave->leave_type} leave",
                'date' => $leave->start_date->format('M d'),
                'icon' => 'calendar-times',
                'color' => 'info',
            ];
        }

        return $events;
    }

    private function getPendingApprovals(): array
    {
        $currentUser = auth()->user();

        // Check if user is authenticated
        if (!$currentUser) {
            return [];
        }

        $employee = Employee::where('user_id', $currentUser->id)->first();

        if (!$employee) {
            return [];
        }

        return LeaveRequest::getPendingApprovals($employee)->toArray();
    }

    private function getMonthlyAttendanceStats(string $month): array
    {
        $year = substr($month, 0, 4);
        $monthNum = substr($month, 5, 2);

        $totalEmployees = Employee::active()->count();
        $workingDays = $this->getWorkingDaysInMonth($year, $monthNum);
        $totalPossibleAttendance = $totalEmployees * $workingDays;

        $actualAttendance = Attendance::forMonth($year, $monthNum)
            ->present()
            ->count();

        return [
            'total_employees' => $totalEmployees,
            'working_days' => $workingDays,
            'total_possible' => $totalPossibleAttendance,
            'actual_attendance' => $actualAttendance,
            'attendance_rate' => $totalPossibleAttendance > 0 ? ($actualAttendance / $totalPossibleAttendance) * 100 : 0,
        ];
    }

    private function calculateAttendanceRate(string $month): float
    {
        $stats = $this->getMonthlyAttendanceStats($month);
        return $stats['attendance_rate'];
    }

    private function getWorkingDaysInMonth(int $year, int $month): int
    {
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        $workingDays = 0;

        while ($startDate <= $endDate) {
            if (!$startDate->isWeekend()) {
                $workingDays++;
            }
            $startDate->addDay();
        }

        return $workingDays;
    }
}
