@extends('tenant.layouts.app')

@section('title', __('Medical Sales Representatives'))
@section('page-title', __('Medical Sales Representatives Dashboard'))

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">{{ __('app.dashboard') }}</a></li>
<li class="breadcrumb-item active">{{ __('Medical Reps') }}</li>
@endsection

@section('content')
<!-- Overview Metrics -->
<div class="row mb-4">
    <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
        <div class="card stats-card-primary">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col {{ marginEnd('2') }}">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            {{ __('Total Reps') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ $metrics['total_reps'] ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-md fa-2x opacity-75"></i>
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
                            {{ __('Territories') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ $metrics['total_territories'] ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-map fa-2x opacity-75"></i>
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
                            {{ __('Visits Today') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ $metrics['visits_today'] ?? 0 }}</div>
                        <small class="text-muted">{{ $metrics['completed_visits_today'] ?? 0 }} {{ __('completed') }}</small>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar-check fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
        <div class="card stats-card-danger">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col {{ marginEnd('2') }}">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            {{ __('Overdue Visits') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ $metrics['overdue_visits'] ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
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
                            {{ __('Monthly Sales') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ formatCurrency($metrics['monthly_sales'] ?? 0) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-dollar-sign fa-2x opacity-75"></i>
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
                            {{ __('Active Customers') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ $metrics['active_customers'] ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x opacity-75"></i>
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
                                <h6>{{ __('Add Rep') }}</h6>
                                <p class="text-muted small">{{ __('Register new medical representative') }}</p>
                                <a href="{{ route('medical-reps.reps.create') }}" class="btn btn-primary btn-sm">
                                    {{ __('Add Rep') }}
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card h-100 border-success">
                            <div class="card-body text-center">
                                <i class="fas fa-calendar-plus fa-3x text-success mb-3"></i>
                                <h6>{{ __('Schedule Visit') }}</h6>
                                <p class="text-muted small">{{ __('Plan customer visits') }}</p>
                                <a href="{{ route('medical-reps.visits.create') }}" class="btn btn-success btn-sm">
                                    {{ __('Schedule Visit') }}
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card h-100 border-info">
                            <div class="card-body text-center">
                                <i class="fas fa-chart-line fa-3x text-info mb-3"></i>
                                <h6>{{ __('Performance') }}</h6>
                                <p class="text-muted small">{{ __('View performance analytics') }}</p>
                                <a href="{{ route('medical-reps.performance.index') }}" class="btn btn-info btn-sm">
                                    {{ __('View Performance') }}
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card h-100 border-warning">
                            <div class="card-body text-center">
                                <i class="fas fa-mobile-alt fa-3x text-warning mb-3"></i>
                                <h6>{{ __('Mobile App') }}</h6>
                                <p class="text-muted small">{{ __('Field rep mobile interface') }}</p>
                                <a href="{{ route('medical-reps.mobile') }}" class="btn btn-warning btn-sm">
                                    {{ __('Mobile View') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Performance Summary & Today's Activities -->
<div class="row mb-4">
    <!-- Performance Summary -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="m-0">{{ __('Performance Summary') }}</h6>
                <span class="badge bg-info">{{ __('This Month') }}</span>
            </div>
            <div class="card-body">
                @if(isset($performanceSummary))
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-end">
                            <div class="h4 mb-0 text-primary">{{ formatCurrency($performanceSummary['total_target'] ?? 0) }}</div>
                            <small class="text-muted">{{ __('Target') }}</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div>
                            <div class="h4 mb-0 text-success">{{ formatCurrency($performanceSummary['total_sales'] ?? 0) }}</div>
                            <small class="text-muted">{{ __('Achieved') }}</small>
                        </div>
                    </div>
                </div>
                
                <div class="mt-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <small>{{ __('Achievement Rate') }}</small>
                        <small>{{ number_format($performanceSummary['achievement_rate'] ?? 0, 1) }}%</small>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-{{ ($performanceSummary['achievement_rate'] ?? 0) >= 100 ? 'success' : (($performanceSummary['achievement_rate'] ?? 0) >= 75 ? 'warning' : 'danger') }}" 
                             style="width: {{ min(100, $performanceSummary['achievement_rate'] ?? 0) }}%"></div>
                    </div>
                </div>

                @if(isset($performanceSummary['top_performer']) && $performanceSummary['top_performer'])
                <div class="mt-3 pt-3 border-top">
                    <small class="text-muted">{{ __('Top Performer') }}</small>
                    <div class="d-flex align-items-center mt-1">
                        <i class="fas fa-trophy text-warning {{ marginEnd('2') }}"></i>
                        <strong>{{ $performanceSummary['top_performer']->full_name }}</strong>
                    </div>
                </div>
                @endif
                @else
                <div class="text-center py-3">
                    <i class="fas fa-chart-bar fa-2x text-muted mb-2"></i>
                    <p class="text-muted">{{ __('No performance data available') }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Today's Activities -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="m-0">{{ __('Today\'s Activities') }}</h6>
                <span class="badge bg-primary">{{ now()->format('M d, Y') }}</span>
            </div>
            <div class="card-body p-0">
                @if(isset($todayActivities) && (count($todayActivities['scheduled_visits'] ?? []) > 0 || count($todayActivities['completed_visits'] ?? []) > 0))
                <div class="list-group list-group-flush">
                    @foreach(($todayActivities['scheduled_visits'] ?? []) as $visit)
                    <div class="list-group-item">
                        <div class="d-flex align-items-center">
                            <div class="{{ marginEnd('3') }}">
                                <i class="fas fa-clock text-warning"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="small">
                                    <strong>{{ $visit->customer->name ?? 'N/A' }}</strong>
                                    - {{ $visit->medicalRep->full_name ?? 'N/A' }}
                                </div>
                                <small class="text-muted">
                                    {{ $visit->visit_time ? $visit->visit_time->format('H:i') : 'N/A' }} -
                                    {{ ucfirst(str_replace('_', ' ', $visit->visit_type)) }}
                                </small>
                            </div>
                            <span class="badge bg-{{ $visit->status_color }}">{{ ucfirst($visit->status) }}</span>
                        </div>
                    </div>
                    @endforeach

                    @foreach(($todayActivities['completed_visits'] ?? []) as $visit)
                    <div class="list-group-item">
                        <div class="d-flex align-items-center">
                            <div class="{{ marginEnd('3') }}">
                                <i class="fas fa-check-circle text-success"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="small">
                                    <strong>{{ $visit->customer->name ?? 'N/A' }}</strong>
                                    - {{ $visit->medicalRep->full_name ?? 'N/A' }}
                                </div>
                                <small class="text-muted">
                                    {{ $visit->check_out_time ? $visit->check_out_time->format('H:i') : 'N/A' }} -
                                    {{ ucfirst(str_replace('_', ' ', $visit->visit_type)) }}
                                </small>
                            </div>
                            <span class="badge bg-{{ $visit->status_color }}">{{ ucfirst($visit->status) }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-4">
                    <i class="fas fa-calendar fa-2x text-muted mb-2"></i>
                    <p class="text-muted">{{ __('No activities scheduled for today') }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Alerts & Notifications -->
@if(isset($alerts) && count($alerts) > 0)
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="m-0">{{ __('Alerts & Notifications') }}</h6>
                <span class="badge bg-danger">{{ count($alerts) }}</span>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @foreach($alerts as $alert)
                    <div class="list-group-item">
                        <div class="d-flex align-items-center">
                            <div class="{{ marginEnd('3') }}">
                                <i class="fas fa-{{ $alert['type'] === 'warning' ? 'exclamation-triangle' : 'exclamation-circle' }} text-{{ $alert['type'] }}"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="small"><strong>{{ $alert['title'] }}</strong></div>
                                <small class="text-muted">{{ $alert['message'] }}</small>
                            </div>
                            @if(isset($alert['action_url']))
                            <a href="{{ $alert['action_url'] }}" class="btn btn-outline-{{ $alert['type'] }} btn-sm">
                                {{ __('View') }}
                            </a>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
// Auto-refresh dashboard every 5 minutes
setInterval(function() {
    location.reload();
}, 300000);

// Real-time notifications (placeholder for WebSocket implementation)
function checkForUpdates() {
    // This would typically connect to a WebSocket or poll an API
    // for real-time updates on visit status, new alerts, etc.
}

// Initialize real-time features
document.addEventListener('DOMContentLoaded', function() {
    checkForUpdates();
});
</script>
@endpush
