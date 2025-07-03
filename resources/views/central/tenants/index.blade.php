@extends('central.layouts.app')

@section('title', 'Tenants')
@section('page-title', __('app.tenants'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>{{ __('app.tenants') }}</h2>
    <a href="{{ route('admin.tenants.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>{{ __('app.create') }} {{ __('app.tenant_name') }}
    </a>
</div>

<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col">
                <h6 class="m-0">{{ __('app.tenants') }} {{ __('Management') }}</h6>
            </div>
            <div class="col-auto">
                <form method="GET" class="d-flex">
                    <input type="text" name="search" class="form-control form-control-sm me-2" 
                           placeholder="{{ __('app.search') }}..." value="{{ request('search') }}">
                    <button type="submit" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        @if($tenants->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('app.tenant_name') }}</th>
                        <th>{{ __('app.email') }}</th>
                        <th>{{ __('Domain') }}</th>
                        <th>{{ __('app.license_type') }}</th>
                        <th>{{ __('app.license_expires') }}</th>
                        <th>{{ __('app.status') }}</th>
                        <th>{{ __('Created') }}</th>
                        <th width="120">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tenants as $tenant)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-2">
                                    <i class="fas fa-building text-white"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $tenant->name }}</h6>
                                    <small class="text-muted">{{ $tenant->license_key }}</small>
                                </div>
                            </div>
                        </td>
                        <td>{{ $tenant->email }}</td>
                        <td>
                            @if($tenant->domains->count() > 0)
                                <span class="badge bg-info">{{ $tenant->domains->first()->domain }}</span>
                            @else
                                <span class="text-muted">No domain</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $tenant->license_type === 'enterprise' ? 'success' : ($tenant->license_type === 'premium' ? 'warning' : 'info') }}">
                                {{ ucfirst($tenant->license_type) }}
                            </span>
                        </td>
                        <td>
                            @if($tenant->license_expires_at)
                                <span class="badge bg-{{ $tenant->license_expires_at->isPast() ? 'danger' : ($tenant->license_expires_at->diffInDays() <= 30 ? 'warning' : 'success') }}">
                                    {{ $tenant->license_expires_at->format('M d, Y') }}
                                </span>
                            @else
                                <span class="text-muted">No expiry</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $tenant->status === 'active' ? 'success' : ($tenant->status === 'suspended' ? 'warning' : 'secondary') }}">
                                {{ ucfirst($tenant->status) }}
                            </span>
                        </td>
                        <td>{{ $tenant->created_at->format('M d, Y') }}</td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('admin.tenants.show', $tenant) }}" 
                                   class="btn btn-outline-info" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.tenants.edit', $tenant) }}" 
                                   class="btn btn-outline-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-outline-danger" 
                                        onclick="deleteTenant('{{ $tenant->id }}')" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        @if($tenants->hasPages())
        <div class="card-footer">
            {{ $tenants->links() }}
        </div>
        @endif
        @else
        <div class="text-center py-5">
            <i class="fas fa-building fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No tenants found</h5>
            <p class="text-muted">Create your first tenant to get started.</p>
            <a href="{{ route('admin.tenants.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Create Tenant
            </a>
        </div>
        @endif
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this tenant? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function deleteTenant(tenantId) {
    const form = document.getElementById('deleteForm');
    form.action = `/admin/tenants/${tenantId}`;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endpush

@push('styles')
<style>
.avatar-sm {
    width: 2rem;
    height: 2rem;
}
</style>
@endpush
