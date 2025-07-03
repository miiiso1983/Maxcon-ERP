@extends('central.layouts.app')

@section('title', 'Dashboard')
@section('page-title', __('app.dashboard'))

@section('content')
<div class="row">
    <!-- Stats Cards -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            Total Tenants
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ $stats['total_tenants'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-building fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            Active Tenants
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ $stats['active_tenants'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            Expired Licenses
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ $stats['expired_licenses'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            Total Users
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ $stats['total_users'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Tenants -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold">Recent Tenants</h6>
                <a href="{{ route('admin.tenants.index') }}" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body">
                @if($recent_tenants->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>License</th>
                                <th>Status</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recent_tenants as $tenant)
                            <tr>
                                <td>{{ $tenant->name }}</td>
                                <td>
                                    <span class="badge bg-info">{{ ucfirst($tenant->license_type) }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $tenant->status === 'active' ? 'success' : 'secondary' }}">
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
                <p class="text-muted text-center">No tenants found.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Expiring Licenses -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-warning">Expiring Licenses (Next 30 Days)</h6>
            </div>
            <div class="card-body">
                @if($expiring_licenses->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Tenant</th>
                                <th>License Type</th>
                                <th>Expires</th>
                                <th>Days Left</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($expiring_licenses as $tenant)
                            <tr>
                                <td>{{ $tenant->name }}</td>
                                <td>
                                    <span class="badge bg-info">{{ ucfirst($tenant->license_type) }}</span>
                                </td>
                                <td>{{ $tenant->license_expires_at->format('M d, Y') }}</td>
                                <td>
                                    <span class="badge bg-warning">
                                        {{ $tenant->license_expires_at->diffInDays(now() days
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-muted text-center">No licenses expiring soon.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold">Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('admin.tenants.create') }}" class="btn btn-primary btn-block">
                            <i class="fas fa-plus me-2"></i>Create New Tenant
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('admin.tenants.index') }}" class="btn btn-info btn-block">
                            <i class="fas fa-list me-2"></i>Manage Tenants
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('admin.licenses.index') }}" class="btn btn-warning btn-block">
                            <i class="fas fa-key me-2"></i>License Management
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('admin.settings.index') }}" class="btn btn-secondary btn-block">
                            <i class="fas fa-cog me-2"></i>System Settings
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
