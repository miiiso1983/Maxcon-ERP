@extends('central.layouts.master')

@section('title', __('Tenants Management'))
@section('page-title', __('Tenants Management'))

@section('content')
<div class="container-fluid">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-2 col-md-4 mb-4">
            <div class="stats-card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                {{ __('Total Tenants') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['total']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-building fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-4">
            <div class="stats-card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                {{ __('Active') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['active']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-4">
            <div class="stats-card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                {{ __('Suspended') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['suspended']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-pause-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-4">
            <div class="stats-card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                {{ __('Expired') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['expired']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-4">
            <div class="stats-card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                {{ __('Expiring Soon') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['expiring_soon']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-4">
            <div class="stats-card border-left-secondary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                                {{ __('Inactive') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['inactive']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-ban fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Actions -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control" 
                                   placeholder="{{ __('Search tenants...') }}" 
                                   value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <select name="status" class="form-select">
                                <option value="">{{ __('All Statuses') }}</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                                <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>{{ __('Suspended') }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="license_type" class="form-select">
                                <option value="">{{ __('All License Types') }}</option>
                                <option value="basic" {{ request('license_type') == 'basic' ? 'selected' : '' }}>{{ __('Basic') }}</option>
                                <option value="standard" {{ request('license_type') == 'standard' ? 'selected' : '' }}>{{ __('Standard') }}</option>
                                <option value="premium" {{ request('license_type') == 'premium' ? 'selected' : '' }}>{{ __('Premium') }}</option>
                                <option value="enterprise" {{ request('license_type') == 'enterprise' ? 'selected' : '' }}>{{ __('Enterprise') }}</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> {{ __('Filter') }}
                            </button>
                        </div>
                    </form>
                </div>
                <div class="col-md-4 text-end">
                    <a href="{{ route('central.tenants.create') }}" class="btn btn-success">
                        <i class="fas fa-plus"></i> {{ __('Create New Tenant') }}
                    </a>
                    <button class="btn btn-outline-primary" onclick="exportTenants()">
                        <i class="fas fa-download"></i> {{ __('Export') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tenants Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ __('System Administrators') }}</h5>
            <div>
                <button class="btn btn-sm btn-outline-success" onclick="bulkAction('activate')">
                    <i class="fas fa-check"></i> {{ __('Bulk Activate') }}
                </button>
                <button class="btn btn-sm btn-outline-warning" onclick="bulkAction('suspend')">
                    <i class="fas fa-pause"></i> {{ __('Bulk Suspend') }}
                </button>
            </div>
        </div>
        <div class="card-body">
            @if($tenants->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th width="30">
                                <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                            </th>
                            <th>{{ __('Tenant Info') }}</th>
                            <th>{{ __('System Admin') }}</th>
                            <th>{{ __('License') }}</th>
                            <th>{{ __('Usage') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tenants as $tenant)
                        <tr>
                            <td>
                                <input type="checkbox" class="tenant-checkbox" value="{{ $tenant->id }}">
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-3">
                                        <i class="fas fa-building text-white"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ $tenant->name }}</h6>
                                        <small class="text-muted">{{ $tenant->email }}</small>
                                        <br><small class="text-muted">ID: {{ $tenant->id }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($tenant->adminUser)
                                <div>
                                    <span class="fw-medium">{{ $tenant->admin_name }}</span>
                                    <br><small class="text-muted">{{ $tenant->admin_email }}</small>
                                    @if($tenant->last_login_at)
                                        <br><small class="text-success">{{ __('Last login') }}: {{ $tenant->last_login_at->diffForHumans() }}</small>
                                    @else
                                        <br><small class="text-warning">{{ __('Never logged in') }}</small>
                                    @endif
                                </div>
                                @else
                                <span class="text-muted">{{ __('No admin assigned') }}</span>
                                @endif
                            </td>
                            <td>
                                <div>
                                    <span class="badge bg-{{ $tenant->license_type === 'enterprise' ? 'success' : ($tenant->license_type === 'premium' ? 'warning' : 'info') }}">
                                        {{ ucfirst($tenant->license_type) }}
                                    </span>
                                    <br>
                                    @if($tenant->license_expires_at)
                                        <small class="text-{{ $tenant->isExpired() ? 'danger' : ($tenant->isNearExpiry() ? 'warning' : 'success') }}">
                                            {{ __('Expires') }}: {{ $tenant->license_expires_at->format('M d, Y') }}
                                        </small>
                                    @else
                                        <small class="text-muted">{{ __('No expiry') }}</small>
                                    @endif
                                    <br><small class="text-muted">${{ number_format($tenant->monthly_fee, 0) }}/{{ __('month') }}</small>
                                </div>
                            </td>
                            <td>
                                <div class="small">
                                    <div class="d-flex justify-content-between">
                                        <span>{{ __('Users') }}:</span>
                                        <span>{{ $tenant->current_users }}/{{ $tenant->max_users == -1 ? '∞' : $tenant->max_users }}</span>
                                    </div>
                                    <div class="progress mb-1" style="height: 4px;">
                                        <div class="progress-bar bg-primary" style="width: {{ $tenant->getUsersUsagePercentage() }}%"></div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between">
                                        <span>{{ __('Storage') }}:</span>
                                        <span>{{ $tenant->current_storage }}/{{ $tenant->max_storage == -1 ? '∞' : $tenant->max_storage }} MB</span>
                                    </div>
                                    <div class="progress" style="height: 4px;">
                                        <div class="progress-bar bg-info" style="width: {{ $tenant->getStorageUsagePercentage() }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <span class="badge bg-{{ $tenant->status === 'active' ? 'success' : ($tenant->status === 'suspended' ? 'warning' : 'secondary') }}">
                                        {{ ucfirst($tenant->status) }}
                                    </span>
                                    @if($tenant->isExpired())
                                        <br><span class="badge bg-danger">{{ __('Expired') }}</span>
                                    @elseif($tenant->isNearExpiry())
                                        <br><span class="badge bg-warning">{{ __('Expiring Soon') }}</span>
                                    @endif
                                    @if($tenant->isBillingOverdue())
                                        <br><span class="badge bg-danger">{{ __('Payment Overdue') }}</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="viewTenant('{{ $tenant->id }}')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-secondary" onclick="editTenant('{{ $tenant->id }}')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    @if($tenant->status === 'active')
                                    <button class="btn btn-outline-warning" onclick="suspendTenant('{{ $tenant->id }}')">
                                        <i class="fas fa-pause"></i>
                                    </button>
                                    @else
                                    <button class="btn btn-outline-success" onclick="activateTenant('{{ $tenant->id }}')">
                                        <i class="fas fa-play"></i>
                                    </button>
                                    @endif
                                    <button class="btn btn-outline-danger" onclick="deleteTenant('{{ $tenant->id }}')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($tenants->hasPages())
            <div class="d-flex justify-content-center mt-3">
                {{ $tenants->appends(request()->query())->links() }}
            </div>
            @endif
            @else
            <div class="text-center py-5">
                <i class="fas fa-building fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">{{ __('No tenants found') }}</h5>
                <p class="text-muted">{{ __('Create your first tenant to get started with the multi-tenant system.') }}</p>
                <a href="{{ route('central.tenants.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> {{ __('Create First Tenant') }}
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.tenant-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
}

function viewTenant(tenantId) {
    window.location.href = `/master-admin/tenants/${tenantId}`;
}

function editTenant(tenantId) {
    window.location.href = `/master-admin/tenants/${tenantId}/edit`;
}

function activateTenant(tenantId) {
    if (confirm('{{ __("Are you sure you want to activate this tenant?") }}')) {
        updateTenantStatus(tenantId, 'active');
    }
}

function suspendTenant(tenantId) {
    if (confirm('{{ __("Are you sure you want to suspend this tenant? They will lose access to the system.") }}')) {
        updateTenantStatus(tenantId, 'suspended');
    }
}

function deleteTenant(tenantId) {
    if (confirm('{{ __("Are you sure you want to delete this tenant? This action cannot be undone and will permanently delete all their data.") }}')) {
        fetch(`/master-admin/tenants/${tenantId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || '{{ __("An error occurred") }}');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('{{ __("An error occurred") }}');
        });
    }
}

function updateTenantStatus(tenantId, status) {
    fetch(`/master-admin/tenants/${tenantId}/status`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ status: status })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || '{{ __("An error occurred") }}');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('{{ __("An error occurred") }}');
    });
}

function bulkAction(action) {
    const selectedTenants = Array.from(document.querySelectorAll('.tenant-checkbox:checked')).map(cb => cb.value);
    
    if (selectedTenants.length === 0) {
        alert('{{ __("Please select at least one tenant") }}');
        return;
    }
    
    if (confirm(`{{ __("Are you sure you want to") }} ${action} {{ __("the selected tenants?") }}`)) {
        fetch('/master-admin/tenants/bulk-action', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: action,
                tenant_ids: selectedTenants
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || '{{ __("An error occurred") }}');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('{{ __("An error occurred") }}');
        });
    }
}

function exportTenants() {
    window.location.href = '/master-admin/tenants/export';
}
</script>
@endpush
