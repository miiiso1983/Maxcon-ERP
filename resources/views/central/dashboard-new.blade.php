@extends('central.layouts.master')

@section('title', __('Master Admin Dashboard'))
@section('page-title', __('Master Admin Dashboard'))

@section('content')
<div class="container-fluid">
    <!-- Welcome Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-crown text-warning me-2"></i>
                        {{ __('Master Admin Dashboard') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('System Control Center - Welcome back, ') }}{{ auth()->user()->name }}</p>
                </div>
                <div>
                    <span class="badge bg-success fs-6">
                        <i class="fas fa-circle me-1"></i>{{ __('System Online') }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- System Alerts -->
    @if(isset($alerts) && count($alerts) > 0)
    <div class="row mb-4">
        <div class="col-12">
            @foreach($alerts as $alert)
            <div class="alert alert-{{ $alert['type'] }} alert-dismissible fade show" role="alert">
                <i class="fas fa-{{ $alert['type'] == 'danger' ? 'exclamation-triangle' : ($alert['type'] == 'warning' ? 'exclamation-circle' : 'info-circle') }} me-2"></i>
                <strong>{{ $alert['message'] }}</strong>
                @if(isset($alert['action']))
                    <br><small>{{ __('Action required: ') }}{{ $alert['action'] }}</small>
                @endif
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                {{ __('Total Tenants') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['total_tenants'] ?? 0) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-building fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                {{ __('Active Tenants') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['active_tenants'] ?? 0) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                {{ __('Total Users') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['total_users'] ?? 0) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                {{ __('System Health') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['system_health'] ?? 100, 1) }}%
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-heartbeat fa-2x text-gray-300"></i>
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
                    <h5 class="mb-0">{{ __('Quick Actions') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('central.tenants.create') }}" class="btn btn-primary w-100">
                                <i class="fas fa-plus me-2"></i>{{ __('Create New Tenant') }}
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('central.tenants.index') }}" class="btn btn-outline-primary w-100">
                                <i class="fas fa-building me-2"></i>{{ __('Manage Tenants') }}
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('central.users.index') }}" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-users me-2"></i>{{ __('Manage Users') }}
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('central.system.info') }}" class="btn btn-outline-info w-100">
                                <i class="fas fa-server me-2"></i>{{ __('System Info') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity and Tenants -->
    <div class="row">
        <!-- Recent Tenants -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 font-weight-bold">{{ __('Recent Tenants') }}</h6>
                    <a href="{{ route('central.tenants.index') }}" class="btn btn-sm btn-primary">{{ __('View All') }}</a>
                </div>
                <div class="card-body">
                    @if(isset($recentTenants) && $recentTenants->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('License') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Created') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentTenants as $tenant)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-2">
                                                <i class="fas fa-building text-white"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $tenant->name }}</h6>
                                                <small class="text-muted">{{ $tenant->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $tenant->license_type === 'enterprise' ? 'success' : ($tenant->license_type === 'premium' ? 'warning' : 'info') }}">
                                            {{ ucfirst($tenant->license_type) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $tenant->status === 'active' ? 'success' : ($tenant->status === 'suspended' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($tenant->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $tenant->created_at->format('M d, Y') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-3">
                        <i class="fas fa-building fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">{{ __('No tenants found') }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0 font-weight-bold">{{ __('Recent Activities') }}</h6>
                </div>
                <div class="card-body">
                    @if(isset($stats['recent_activities']) && count($stats['recent_activities']) > 0)
                    <div class="timeline">
                        @foreach($stats['recent_activities'] as $activity)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-{{ $activity['type'] === 'admin' ? 'warning' : 'primary' }}"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">{{ $activity['user'] }}</h6>
                                <p class="mb-1 text-muted">{{ $activity['action'] }}</p>
                                <small class="text-muted">{{ $activity['time']->diffForHumans() }}</small>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-3">
                        <i class="fas fa-clock fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">{{ __('No recent activities') }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- System Information -->
    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0 font-weight-bold">{{ __('System Information') }}</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="text-center">
                                <div class="h4 mb-0 text-primary">{{ number_format($stats['storage_usage'] ?? 0, 1) }}%</div>
                                <small class="text-muted">{{ __('Storage Used') }}</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center">
                                <div class="h4 mb-0 text-info">{{ $stats['database_size'] ?? 'N/A' }}</div>
                                <small class="text-muted">{{ __('Database Size') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0 font-weight-bold">{{ __('License Distribution') }}</h6>
                </div>
                <div class="card-body">
                    <canvas id="licenseChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -35px;
    top: 5px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
}

.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: -31px;
    top: 15px;
    width: 2px;
    height: calc(100% + 5px);
    background: #e9ecef;
}

.avatar-sm {
    width: 32px;
    height: 32px;
    font-size: 12px;
}
</style>
@endpush

@push('scripts')
<script>
// License Distribution Chart
const ctx = document.getElementById('licenseChart').getContext('2d');
const licenseChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: ['Basic', 'Standard', 'Premium', 'Enterprise'],
        datasets: [{
            data: [30, 25, 20, 15],
            backgroundColor: [
                '#17a2b8',
                '#ffc107', 
                '#fd7e14',
                '#28a745'
            ],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
            }
        }
    }
});

// Auto-refresh dashboard data every 30 seconds
setInterval(() => {
    // You can add AJAX call here to refresh data
    console.log('Dashboard data refresh...');
}, 30000);
</script>
@endpush
