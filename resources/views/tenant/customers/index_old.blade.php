@extends('tenant.layouts.app')

@section('title', __('Customers'))
@section('page-title', __('Customers Management'))

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">{{ __('app.dashboard') }}</a></li>
<li class="breadcrumb-item active">{{ __('Customers') }}</li>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-users {{ marginEnd('2') }}"></i>
                            {{ __('Customers Management') }}
                        </h5>
                        <div class="btn-group" role="group">
                            <a href="{{ route('customers.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus {{ marginEnd('2') }}"></i>{{ __('Add Customer') }}
                            </a>
                            <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="visually-hidden">{{ __('Toggle Dropdown') }}</span>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="{{ route('customers.import') }}">
                                        <i class="fas fa-upload {{ marginEnd('2') }}"></i>{{ __('Import from Excel') }}
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('customers.export') }}">
                                        <i class="fas fa-download {{ marginEnd('2') }}"></i>{{ __('Export to Excel') }}
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('customers.template') }}">
                                        <i class="fas fa-file-excel {{ marginEnd('2') }}"></i>{{ __('Download Template') }}
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stats-card">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col {{ marginEnd('2') }}">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            {{ __('Total Customers') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ $stats['total_customers'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stats-card-success">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col {{ marginEnd('2') }}">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            {{ __('Active Customers') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ $stats['active_customers'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-check fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stats-card-warning">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col {{ marginEnd('2') }}">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            {{ __('With Outstanding Debt') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ $stats['customers_with_debt'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stats-card-danger">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col {{ marginEnd('2') }}">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            {{ __('Total Outstanding') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ formatCurrency($stats['total_debt']) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-dollar-sign fa-2x opacity-75"></i>
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
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2 mb-2">
                        <div class="btn-group w-100" role="group">
                            <a href="{{ route('customers.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus {{ marginEnd('2') }}"></i>{{ __('Add Customer') }}
                            </a>
                            <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="visually-hidden">{{ __('Toggle Dropdown') }}</span>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="{{ route('customers.import') }}">
                                        <i class="fas fa-upload {{ marginEnd('2') }}"></i>{{ __('Import from Excel') }}
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('customers.export') }}">
                                        <i class="fas fa-download {{ marginEnd('2') }}"></i>{{ __('Export to Excel') }}
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('customers.template') }}">
                                        <i class="fas fa-file-excel {{ marginEnd('2') }}"></i>{{ __('Download Template') }}
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-2 mb-2">
                        <a href="{{ route('customers.index', ['customer_type' => 'hospital']) }}" class="btn btn-danger w-100">
                            <i class="fas fa-hospital {{ marginEnd('2') }}"></i>{{ __('Hospitals') }}
                        </a>
                    </div>
                    <div class="col-md-2 mb-2">
                        <a href="{{ route('customers.index', ['customer_type' => 'pharmacy']) }}" class="btn btn-info w-100">
                            <i class="fas fa-pills {{ marginEnd('2') }}"></i>{{ __('Pharmacies') }}
                        </a>
                    </div>
                    <div class="col-md-2 mb-2">
                        <a href="{{ route('customers.index', ['customer_type' => 'clinic']) }}" class="btn btn-warning w-100">
                            <i class="fas fa-clinic-medical {{ marginEnd('2') }}"></i>{{ __('Clinics') }}
                        </a>
                    </div>
                    <div class="col-md-2 mb-2">
                        <a href="{{ route('customers.index', ['status' => 'debt']) }}" class="btn btn-danger w-100">
                            <i class="fas fa-exclamation-circle {{ marginEnd('2') }}"></i>{{ __('With Debt') }}
                        </a>
                    </div>
                    <div class="col-md-2 mb-2">
                        <a href="{{ route('reports.customers') }}" class="btn btn-secondary w-100">
                            <i class="fas fa-chart-bar {{ marginEnd('2') }}"></i>{{ __('Reports') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters and Search -->
<div class="card mb-4">
    <div class="card-header">
        <h6 class="m-0">{{ __('app.search') }} & {{ __('app.filter') }}</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('customers.index') }}">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="search" class="form-label">{{ __('app.search') }}</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="{{ request('search') }}" placeholder="{{ __('Search customers...') }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="customer_type" class="form-label">{{ __('Customer Type') }}</label>
                    <select class="form-select" id="customer_type" name="customer_type">
                        <option value="">{{ __('All Types') }}</option>
                        <option value="individual" {{ request('customer_type') == 'individual' ? 'selected' : '' }}>{{ __('Individual') }}</option>
                        <option value="business" {{ request('customer_type') == 'business' ? 'selected' : '' }}>{{ __('Business') }}</option>
                        <option value="hospital" {{ request('customer_type') == 'hospital' ? 'selected' : '' }}>{{ __('Hospital') }}</option>
                        <option value="clinic" {{ request('customer_type') == 'clinic' ? 'selected' : '' }}>{{ __('Clinic') }}</option>
                        <option value="pharmacy" {{ request('customer_type') == 'pharmacy' ? 'selected' : '' }}>{{ __('Pharmacy') }}</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="status" class="form-label">{{ __('app.status') }}</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">{{ __('All Status') }}</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('app.active') }}</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>{{ __('app.inactive') }}</option>
                        <option value="debt" {{ request('status') == 'debt' ? 'selected' : '' }}>{{ __('With Debt') }}</option>
                    </select>
                </div>
                <div class="col-md-2 mb-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search {{ marginEnd('2') }}"></i>{{ __('app.search') }}
                    </button>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times {{ marginEnd('2') }}"></i>{{ __('Clear') }}
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Customers Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="m-0">{{ __('Customers') }}</h6>
        <div>
            <span class="text-muted">{{ $customers->total() }} {{ __('customers found') }}</span>
        </div>
    </div>
    <div class="card-body p-0">
        @if($customers->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>
                            <input type="checkbox" id="selectAll" class="form-check-input">
                        </th>
                        <th>{{ __('Customer') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Contact') }}</th>
                        <th>{{ __('Credit Info') }}</th>
                        <th>{{ __('Loyalty Points') }}</th>
                        <th>{{ __('app.status') }}</th>
                        <th width="120">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($customers as $customer)
                    <tr>
                        <td>
                            <input type="checkbox" name="customers[]" value="{{ $customer->id }}" class="form-check-input customer-checkbox">
                        </td>
                        <td>
                            <div>
                                <strong>{{ $customer->name }}</strong>
                                <br>
                                <small class="text-muted">{{ $customer->customer_code }}</small>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-{{ $customer->customer_type_color }}">
                                {{ ucfirst($customer->customer_type) }}
                            </span>
                        </td>
                        <td>
                            <div>
                                @if($customer->email)
                                <div><i class="fas fa-envelope {{ marginEnd('1') }}"></i>{{ $customer->email }}</div>
                                @endif
                                @if($customer->phone)
                                <div><i class="fas fa-phone {{ marginEnd('1') }}"></i>{{ $customer->phone }}</div>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div>
                                <small class="text-muted">{{ __('Limit') }}: {{ formatCurrency($customer->credit_limit) }}</small>
                                @if($customer->total_debt > 0)
                                <br>
                                <small class="text-danger">{{ __('Debt') }}: {{ formatCurrency($customer->total_debt) }}</small>
                                @endif
                                @if($customer->isOverCreditLimit())
                                <br>
                                <small class="text-danger"><i class="fas fa-exclamation-triangle"></i> {{ __('Over Limit') }}</small>
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-success">{{ $customer->total_loyalty_points }}</span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $customer->is_active ? 'success' : 'secondary' }}">
                                {{ $customer->is_active ? __('app.active') : __('app.inactive') }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('customers.show', $customer) }}" 
                                   class="btn btn-outline-info" title="{{ __('View') }}">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('customers.edit', $customer) }}" 
                                   class="btn btn-outline-primary" title="{{ __('app.edit') }}">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route('customers.statements', $customer) }}" 
                                   class="btn btn-outline-secondary" title="{{ __('Statement') }}">
                                    <i class="fas fa-file-invoice"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        @if($customers->hasPages())
        <div class="card-footer">
            {{ $customers->appends(request()->query())->links() }}
        </div>
        @endif
        @else
        <div class="text-center py-5">
            <i class="fas fa-users fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">{{ __('No customers found') }}</h5>
            <p class="text-muted">{{ __('Try adjusting your search criteria or add new customers.') }}</p>
            <a href="{{ route('customers.create') }}" class="btn btn-primary">
                <i class="fas fa-plus {{ marginEnd('2') }}"></i>{{ __('Add Customer') }}
            </a>
        </div>
        @endif
    </div>
</div>

<!-- Bulk Actions -->
@if($customers->count() > 0)
<div class="card mt-3" id="bulkActions" style="display: none;">
    <div class="card-body">
        <form method="POST" action="{{ route('customers.bulk-action') }}" id="bulkActionForm">
            @csrf
            <div class="row align-items-center">
                <div class="col-md-3">
                    <select name="action" class="form-select" required>
                        <option value="">{{ __('Select Action') }}</option>
                        <option value="activate">{{ __('Activate') }}</option>
                        <option value="deactivate">{{ __('Deactivate') }}</option>
                        <option value="delete">{{ __('Delete') }}</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-bolt {{ marginEnd('2') }}"></i>{{ __('Apply to Selected') }}
                    </button>
                </div>
                <div class="col-md-6">
                    <span id="selectedCount" class="text-muted"></span>
                </div>
            </div>
        </form>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    const customerCheckboxes = document.querySelectorAll('.customer-checkbox');
    const bulkActions = document.getElementById('bulkActions');
    const selectedCount = document.getElementById('selectedCount');
    const bulkActionForm = document.getElementById('bulkActionForm');

    function updateBulkActions() {
        const checkedBoxes = document.querySelectorAll('.customer-checkbox:checked');
        const count = checkedBoxes.length;
        
        if (count > 0) {
            bulkActions.style.display = 'block';
            selectedCount.textContent = `${count} customer(s) selected`;
            
            // Add hidden inputs for selected customers
            const existingInputs = bulkActionForm.querySelectorAll('input[name="customers[]"]');
            existingInputs.forEach(input => input.remove());
            
            checkedBoxes.forEach(checkbox => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'customers[]';
                input.value = checkbox.value;
                bulkActionForm.appendChild(input);
            };
        } else {
            bulkActions.style.display = 'none';
        }
    }

    selectAll.addEventListener('change', function() {
        customerCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        };
        updateBulkActions();
    };

    customerCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkActions);
    };

    bulkActionForm.addEventListener('submit', function(e) {
        const action = this.querySelector('select[name="action"]').value;
        if (action === 'delete') {
            if (!confirm('Are you sure you want to delete the selected customers? This action cannot be undone.')) {
                e.preventDefault();
            }
        }
    };
};
</script>
@endpush
