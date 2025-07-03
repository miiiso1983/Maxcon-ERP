@extends('tenant.layouts.app')

@section('title', __('Regulatory Compliance'))
@section('page-title', __('Regulatory Compliance Dashboard'))

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">{{ __('app.dashboard') }}</a></li>
<li class="breadcrumb-item active">{{ __('Compliance') }}</li>
@endsection

@section('content')
<!-- Compliance Overview Metrics -->
<div class="row mb-4">
    <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
        <div class="card stats-card-primary">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col {{ marginEnd('2') }}">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            {{ __('Total Items') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ $overview['total_items'] ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clipboard-list fa-2x opacity-75"></i>
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
                            {{ __('Active Items') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ $overview['active_items'] ?? 0 }}</div>
                        <small class="text-muted">{{ number_format($overview['compliance_rate'] ?? 0, 1) }}% {{ __('compliant') }}</small>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle fa-2x opacity-75"></i>
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
                            {{ __('Expired Items') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ $overview['expired_items'] ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
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
                            {{ __('Expiring Soon') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ $overview['expiring_items'] ?? 0 }}</div>
                        <small class="text-muted">{{ __('Next 30 days') }}</small>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock fa-2x opacity-75"></i>
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
                            {{ __('Inspections') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ $overview['upcoming_inspections'] ?? 0 }}</div>
                        <small class="text-muted">{{ __('Upcoming') }}</small>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-search fa-2x opacity-75"></i>
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
                            {{ __('Violations') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ $overview['open_violations'] ?? 0 }}</div>
                        <small class="text-muted">{{ $overview['critical_violations'] ?? 0 }} {{ __('critical') }}</small>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-circle fa-2x opacity-75"></i>
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
                                <i class="fas fa-plus-circle fa-3x text-primary mb-3"></i>
                                <h6>{{ __('Add Compliance Item') }}</h6>
                                <p class="text-muted small">{{ __('Register new compliance requirement') }}</p>
                                <a href="{{ route('compliance.items.create') }}" class="btn btn-primary btn-sm">
                                    {{ __('Add Item') }}
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card h-100 border-success">
                            <div class="card-body text-center">
                                <i class="fas fa-calendar-plus fa-3x text-success mb-3"></i>
                                <h6>{{ __('Schedule Inspection') }}</h6>
                                <p class="text-muted small">{{ __('Plan compliance inspection') }}</p>
                                <a href="{{ route('compliance.inspections.create') }}" class="btn btn-success btn-sm">
                                    {{ __('Schedule') }}
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card h-100 border-warning">
                            <div class="card-body text-center">
                                <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                                <h6>{{ __('Report Violation') }}</h6>
                                <p class="text-muted small">{{ __('Report compliance violation') }}</p>
                                <a href="{{ route('compliance.violations.create') }}" class="btn btn-warning btn-sm">
                                    {{ __('Report') }}
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card h-100 border-info">
                            <div class="card-body text-center">
                                <i class="fas fa-chart-bar fa-3x text-info mb-3"></i>
                                <h6>{{ __('Compliance Reports') }}</h6>
                                <p class="text-muted small">{{ __('View compliance analytics') }}</p>
                                <a href="{{ route('compliance.reports') }}" class="btn btn-info btn-sm">
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

<!-- Critical Alerts & Upcoming Deadlines -->
<div class="row mb-4">
    <!-- Critical Alerts -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="m-0">{{ __('Critical Alerts') }}</h6>
                <span class="badge bg-danger">{{ count($alerts ?? []) }}</span>
            </div>
            <div class="card-body p-0">
                @if(!empty($alerts) && count($alerts) > 0)
                <div class="list-group list-group-flush">
                    @foreach(array_slice($alerts, 0, 8) as $alert)
                    <div class="list-group-item">
                        <div class="d-flex align-items-center">
                            <div class="{{ marginEnd('3') }}">
                                <i class="fas fa-{{ $alert['type'] === 'danger' ? 'exclamation-circle' : 'exclamation-triangle' }} text-{{ $alert['type'] }}"></i>
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
                @else
                <div class="text-center py-4">
                    <i class="fas fa-shield-alt fa-2x text-success mb-2"></i>
                    <p class="text-muted">{{ __('No critical alerts') }}</p>
                    <small class="text-success">{{ __('All compliance items are in good standing') }}</small>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Upcoming Deadlines -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="m-0">{{ __('Upcoming Deadlines') }}</h6>
                <span class="badge bg-warning">{{ count($upcomingDeadlines ?? []) }}</span>
            </div>
            <div class="card-body p-0">
                @if(!empty($upcomingDeadlines) && count($upcomingDeadlines) > 0)
                <div class="list-group list-group-flush">
                    @foreach(array_slice($upcomingDeadlines, 0, 8) as $deadline)
                    <div class="list-group-item">
                        <div class="d-flex align-items-center">
                            <div class="{{ marginEnd('3') }}">
                                <i class="fas fa-{{ $deadline['type'] === 'expiration' ? 'calendar-times' : 'search' }} text-{{ $deadline['days_left'] <= 7 ? 'danger' : ($deadline['days_left'] <= 30 ? 'warning' : 'info') }}"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="small">{{ $deadline['title'] }}</div>
                                <small class="text-muted">
                                    {{ $deadline['date']->format('M d Y') }} -
                                    @if($deadline['days_left'] >= 0)
                                        {{ $deadline['days_left'] }} {{ __('days left') }}
                                    @else
                                        {{ abs($deadline['days_left']) }} {{ __('days overdue') }}
                                    @endif
                                </small>
                            </div>
                            <a href="{{ $deadline['url'] }}" class="btn btn-outline-primary btn-sm">
                                {{ __('View') }}
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-4">
                    <i class="fas fa-calendar-check fa-2x text-success mb-2"></i>
                    <p class="text-muted">{{ __('No upcoming deadlines') }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Recent Activities & Compliance Trends -->
<div class="row">
    <!-- Recent Activities -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0">{{ __('Recent Activities') }}</h6>
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

    <!-- Compliance Trends Chart -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0">{{ __('Compliance Trends') }}</h6>
            </div>
            <div class="card-body">
                @if(!empty($trends) && count($trends) > 0)
                <canvas id="complianceTrendsChart" height="200"></canvas>
                @else
                <div class="text-center py-4">
                    <i class="fas fa-chart-line fa-2x text-muted mb-2"></i>
                    <p class="text-muted">{{ __('No trend data available') }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="{{ asset('js/compliance-dashboard.js') }}"></script>
@if(!empty($trends) && count($trends) > 0)
<!-- Pass data to JavaScript -->
<script id="trends-data" type="application/json">{!! json_encode($trends ?? []) !!}</script>

<script>
// Pass translations to JavaScript
window.translations = {
    newItems: '{{ __("New Items") }}',
    violations: '{{ __("Violations") }}',
    inspections: '{{ __("Inspections") }}'
};

// Initialize chart with data
document.addEventListener('DOMContentLoaded', function() {
    const trendsDataElement = document.getElementById('trends-data');
    const trendsData = trendsDataElement ? JSON.parse(trendsDataElement.textContent) : [];
    initializeComplianceTrendsChart(trendsData);
});
</script>
@endif
@endpush
