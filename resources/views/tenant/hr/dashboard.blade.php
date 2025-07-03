@extends('tenant.layouts.app')

@section('title', __('Human Resources'))
@section('page-title', __('Human Resources Dashboard'))

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">{{ __('app.dashboard') }}</a></li>
<li class="breadcrumb-item active">{{ __('Human Resources') }}</li>
@endsection

@section('content')
<!-- HR Metrics Overview -->
<div class="row mb-4">
    <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
        <div class="card stats-card-primary">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col {{ marginEnd('2') }}">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            {{ __('Total Employees') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ $metrics['total_employees'] ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
        <div class="card stats-card-success">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col {{ marginEnd('2') }}">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            {{ __('Present Today') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ $metrics['present_today'] ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-check fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
        <div class="card stats-card-warning">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col {{ marginEnd('2') }}">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            {{ __('Pending Leaves') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ $metrics['pending_leaves'] ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar-times fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
        <div class="card stats-card-info">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col {{ marginEnd('2') }}">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            {{ __('New Hires') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ $metrics['new_hires_month'] ?? 0 }}</div>
                        <small class="text-muted">{{ __('This month') }}</small>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-plus fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
        <div class="card stats-card-secondary">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col {{ marginEnd('2') }}">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            {{ __('Departments') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ $metrics['departments'] ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-building fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
        <div class="card stats-card-primary">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col {{ marginEnd('2') }}">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            {{ __('Attendance Rate') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ number_format($metrics['attendance_rate'] ?? 0, 1) }}%</div>
                        <small class="text-muted">{{ __('This month') }}</small>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-chart-line fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0">{{ __('Quick Actions') }}</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <div class="card h-100 border-primary">
                            <div class="card-body text-center">
                                <i class="fas fa-user-plus fa-3x text-primary mb-3"></i>
                                <h6>{{ __('Add Employee') }}</h6>
                                <p class="text-muted small">{{ __('Register new employee') }}</p>
                                <a href="{{ route('hr.employees.create') }}" class="btn btn-primary btn-sm">
                                    {{ __('Add Employee') }}
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card h-100 border-success">
                            <div class="card-body text-center">
                                <i class="fas fa-clock fa-3x text-success mb-3"></i>
                                <h6>{{ __('Mark Attendance') }}</h6>
                                <p class="text-muted small">{{ __('Record employee attendance') }}</p>
                                <button class="btn btn-success btn-sm" onclick="showAttendanceModal()">
                                    {{ __('Mark Attendance') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card h-100 border-warning">
                            <div class="card-body text-center">
                                <i class="fas fa-calendar-plus fa-3x text-warning mb-3"></i>
                                <h6>{{ __('Leave Request') }}</h6>
                                <p class="text-muted small">{{ __('Submit leave application') }}</p>
                                <a href="{{ route('hr.leave.create') }}" class="btn btn-warning btn-sm">
                                    {{ __('Request Leave') }}
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card h-100 border-info">
                            <div class="card-body text-center">
                                <i class="fas fa-chart-bar fa-3x text-info mb-3"></i>
                                <h6>{{ __('Attendance Report') }}</h6>
                                <p class="text-muted small">{{ __('Generate attendance reports') }}</p>
                                <a href="{{ route('hr.attendance.report') }}" class="btn btn-info btn-sm">
                                    {{ __('View Reports') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Row -->
<div class="row">
    <!-- Recent Activities -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="m-0">{{ __('Recent Activities') }}</h6>
                <span class="badge bg-info">{{ count($recentActivities ?? []) }}</span>
            </div>
            <div class="card-body p-0">
                @if(!empty($recentActivities) && count($recentActivities) > 0)
                <div class="list-group list-group-flush">
                    @foreach($recentActivities as $activity)
                    <div class="list-group-item">
                        <div class="d-flex align-items-center">
                            <div class="{{ marginEnd('3') }}">
                                <i class="fas fa-{{ $activity['icon'] }} text-{{ $activity['color'] }}"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="small">{{ $activity['message'] }}</div>
                                <small class="text-muted">{{ $activity['date']->format('M d, Y') }}</small>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-4">
                    <i class="fas fa-history fa-2x text-muted mb-2"></i>
                    <p class="text-muted">{{ __('No recent activities') }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Upcoming Events -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="m-0">{{ __('Upcoming Events') }}</h6>
                <span class="badge bg-warning">{{ count($upcomingEvents ?? []) }}</span>
            </div>
            <div class="card-body p-0">
                @if(!empty($upcomingEvents) && count($upcomingEvents) > 0)
                <div class="list-group list-group-flush">
                    @foreach($upcomingEvents as $event)
                    <div class="list-group-item">
                        <div class="d-flex align-items-center">
                            <div class="{{ marginEnd('3') }}">
                                <i class="fas fa-{{ $event['icon'] }} text-{{ $event['color'] }}"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="small">{{ $event['title'] }}</div>
                                <small class="text-muted">{{ $event['date'] }}</small>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-4">
                    <i class="fas fa-calendar fa-2x text-muted mb-2"></i>
                    <p class="text-muted">{{ __('No upcoming events') }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@if(!empty($pendingApprovals) && count($pendingApprovals) > 0)
<!-- Pending Approvals -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="m-0">{{ __('Pending Leave Approvals') }}</h6>
                <span class="badge bg-danger">{{ count($pendingApprovals) }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('Employee') }}</th>
                                <th>{{ __('Leave Type') }}</th>
                                <th>{{ __('Duration') }}</th>
                                <th>{{ __('Dates') }}</th>
                                <th>{{ __('Reason') }}</th>
                                <th width="120">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingApprovals as $approval)
                            <tr>
                                <td>
                                    <div>
                                        <strong>{{ $approval['employee']['full_name'] ?? 'N/A' }}</strong>
                                        <br><small class="text-muted">{{ $approval['employee']['department']['name'] ?? 'N/A' }}</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $approval['type_color'] ?? 'secondary' }}">
                                        {{ ucfirst(str_replace('_', ' ', $approval['leave_type'])) }}
                                    </span>
                                </td>
                                <td>{{ $approval['days_requested'] }} {{ __('days') }}</td>
                                <td>
                                    <small>
                                        {{ \Carbon\Carbon::parse($approval['start_date'])->format('M d, Y') }} -
                                        {{ \Carbon\Carbon::parse($approval['end_date'])->format('M d, Y') }}
                                    </small>
                                </td>
                                <td>
                                    <small>{{ Str::limit($approval['reason'], 50) }}</small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-success btn-sm approve-leave-btn" data-leave-id="{{ $approval['id'] }}" data-action="approve" title="{{ __('Approve') }}">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm approve-leave-btn" data-leave-id="{{ $approval['id'] }}" data-action="reject" title="{{ __('Reject') }}">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Attendance Modal -->
<div class="modal fade" id="attendanceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Mark Attendance') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="attendanceForm">
                    <div class="mb-3">
                        <label class="form-label">{{ __('Employee') }}</label>
                        <select class="form-select" name="employee_id" required>
                            <option value="">{{ __('Select employee') }}</option>
                            <!-- Will be populated via AJAX -->
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">{{ __('Action') }}</label>
                        <select class="form-select" name="action" required>
                            <option value="">{{ __('Select action') }}</option>
                            <option value="check_in">{{ __('Check In') }}</option>
                            <option value="check_out">{{ __('Check Out') }}</option>
                            <option value="start_break">{{ __('Start Break') }}</option>
                            <option value="end_break">{{ __('End Break') }}</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">{{ __('Time') }}</label>
                        <input type="time" class="form-control" name="time" value="{{ now()->format('H:i') }}">
                        <div class="form-text">{{ __('Leave empty for current time') }}</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('Notes') }}</label>
                        <textarea class="form-control" name="notes" rows="2" placeholder="{{ __('Optional notes') }}"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-primary" onclick="markAttendance()">
                    <i class="fas fa-save {{ marginEnd('2') }}"></i>{{ __('Mark Attendance') }}
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function showAttendanceModal() {
    // Load employees list
    fetch('/api/employees/active')
        .then(response => response.json())
        .then(data => {
            const select = document.querySelector('select[name="employee_id"]');
            select.innerHTML = '<option value="">{{ __("Select employee") }}</option>';
            
            data.forEach(employee => {
                const option = document.createElement('option');
                option.value = employee.id;
                option.textContent = employee.full_name;
                select.appendChild(option);
            });
        })
        .catch(error => console.error('Error loading employees:', error));
    
    const modal = new bootstrap.Modal(document.getElementById('attendanceModal'));
    modal.show();
}

function markAttendance() {
    const form = document.getElementById('attendanceForm');
    const formData = new FormData(form);
    
    const data = {
        employee_id: formData.get('employee_id'),
        action: formData.get('action'),
        time: formData.get('time'),
        notes: formData.get('notes')
    };
    
    // Show loading state
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
    button.disabled = true;
    
    fetch('{{ route("hr.attendance.mark") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            const modal = bootstrap.Modal.getInstance(document.getElementById('attendanceModal'));
            if (modal) modal.hide();
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function approveLeave(leaveId, action) {
    const notes = action === 'reject' ? prompt('Rejection reason:') : null;
    
    if (action === 'reject' && !notes) {
        return;
    }
    
    fetch(`/hr/leave/${leaveId}/approve`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            action: action,
            notes: notes
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });
}
</script>
@endpush
