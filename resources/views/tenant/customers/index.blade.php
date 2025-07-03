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
            <div class="card border-left-primary">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col {{ marginEnd('2') }}">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                {{ __('Total Customers') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">156</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-success">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col {{ marginEnd('2') }}">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                {{ __('Active Customers') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">142</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-info">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col {{ marginEnd('2') }}">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                {{ __('This Month') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">23</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-warning">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col {{ marginEnd('2') }}">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                {{ __('Total Revenue') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ formatCurrency(4350000) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">{{ __('Search & Filter') }}</h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('customers.index') }}">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="search" class="form-label">{{ __('Search') }}</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="{{ request('search') }}" placeholder="{{ __('Search customers...') }}">
                            </div>
                            <div class="col-md-2">
                                <label for="customer_type" class="form-label">{{ __('Customer Type') }}</label>
                                <select class="form-select" id="customer_type" name="customer_type" 
                                        data-placeholder="{{ __('All Types') }}">
                                    <option value="">{{ __('All Types') }}</option>
                                    <option value="individual" {{ request('customer_type') == 'individual' ? 'selected' : '' }}>👤 {{ __('Individual') }}</option>
                                    <option value="pharmacy" {{ request('customer_type') == 'pharmacy' ? 'selected' : '' }}>💊 {{ __('Pharmacy') }}</option>
                                    <option value="hospital" {{ request('customer_type') == 'hospital' ? 'selected' : '' }}>🏥 {{ __('Hospital') }}</option>
                                    <option value="clinic" {{ request('customer_type') == 'clinic' ? 'selected' : '' }}>🩺 {{ __('Clinic') }}</option>
                                    <option value="distributor" {{ request('customer_type') == 'distributor' ? 'selected' : '' }}>🚚 {{ __('Distributor') }}</option>
                                    <option value="government" {{ request('customer_type') == 'government' ? 'selected' : '' }}>🏛️ {{ __('Government') }}</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="city" class="form-label">{{ __('City') }}</label>
                                <select class="form-select" id="city" name="city" 
                                        data-placeholder="{{ __('All Cities') }}">
                                    <option value="">{{ __('All Cities') }}</option>
                                    <option value="baghdad" {{ request('city') == 'baghdad' ? 'selected' : '' }}>🏛️ {{ __('Baghdad') }}</option>
                                    <option value="basra" {{ request('city') == 'basra' ? 'selected' : '' }}>🏭 {{ __('Basra') }}</option>
                                    <option value="erbil" {{ request('city') == 'erbil' ? 'selected' : '' }}>🏔️ {{ __('Erbil') }}</option>
                                    <option value="mosul" {{ request('city') == 'mosul' ? 'selected' : '' }}>🕌 {{ __('Mosul') }}</option>
                                    <option value="najaf" {{ request('city') == 'najaf' ? 'selected' : '' }}>🕌 {{ __('Najaf') }}</option>
                                    <option value="karbala" {{ request('city') == 'karbala' ? 'selected' : '' }}>🕌 {{ __('Karbala') }}</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="status" class="form-label">{{ __('Status') }}</label>
                                <select class="form-select" id="status" name="status" 
                                        data-placeholder="{{ __('All Status') }}">
                                    <option value="">{{ __('All Status') }}</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>✅ {{ __('Active') }}</option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>❌ {{ __('Inactive') }}</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search {{ marginEnd('2') }}"></i>{{ __('Search') }}
                                    </button>
                                    <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times {{ marginEnd('2') }}"></i>{{ __('Clear') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Customers Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-0">{{ __('Customers List') }}</h6>
                        <div class="d-flex align-items-center">
                            <span class="text-muted {{ marginEnd('3') }}">{{ __('Showing 1-10 of 156 customers') }}</span>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleView('table')">
                                    <i class="fas fa-table"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleView('grid')">
                                    <i class="fas fa-th"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="customers-table">
                            <thead class="table-light">
                                <tr>
                                    <th>
                                        <input type="checkbox" class="form-check-input" id="select-all">
                                    </th>
                                    <th>{{ __('Customer') }}</th>
                                    <th>{{ __('Contact') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('City') }}</th>
                                    <th class="text-center">{{ __('Orders') }}</th>
                                    <th class="text-end">{{ __('Total Spent') }}</th>
                                    <th class="text-center">{{ __('Status') }}</th>
                                    <th class="text-center">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @for($i = 1; $i <= 10; $i++)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input customer-checkbox" value="{{ $i }}">
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center {{ marginEnd('3') }}">
                                                <i class="fas fa-user text-white"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">
                                                    @if($i == 1) Ahmed Al-Rashid
                                                    @elseif($i == 2) Fatima Hassan
                                                    @elseif($i == 3) Omar Khalil
                                                    @elseif($i == 4) Layla Ahmed
                                                    @elseif($i == 5) Hassan Ali
                                                    @elseif($i == 6) Noor Mohammed
                                                    @elseif($i == 7) Zaid Ibrahim
                                                    @elseif($i == 8) Maryam Saleh
                                                    @elseif($i == 9) Ali Hassan
                                                    @else Sara Ahmed
                                                    @endif
                                                </h6>
                                                <small class="text-muted">ID: {{ str_pad($i, 3, '0', STR_PAD_LEFT) }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <div><i class="fas fa-phone {{ marginEnd('2') }}"></i>+964 77{{ $i }} {{ rand(100, 999) }} {{ rand(1000, 9999) }}</div>
                                            <div><i class="fas fa-envelope {{ marginEnd('2') }}"></i>customer{{ $i }}@email.com</div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($i <= 2)
                                            <span class="badge bg-info">{{ __('Individual') }}</span>
                                        @elseif($i <= 4)
                                            <span class="badge bg-success">{{ __('Pharmacy') }}</span>
                                        @elseif($i <= 6)
                                            <span class="badge bg-warning">{{ __('Hospital') }}</span>
                                        @elseif($i <= 8)
                                            <span class="badge bg-primary">{{ __('Clinic') }}</span>
                                        @else
                                            <span class="badge bg-secondary">{{ __('Distributor') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($i <= 3) Baghdad
                                        @elseif($i <= 5) Basra
                                        @elseif($i <= 7) Erbil
                                        @else Najaf
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark">{{ rand(5, 25) }}</span>
                                    </td>
                                    <td class="text-end">
                                        <strong class="text-success">{{ formatCurrency(rand(100000, 2000000)) }}</strong>
                                    </td>
                                    <td class="text-center">
                                        @if($i <= 8)
                                            <span class="badge bg-success">{{ __('Active') }}</span>
                                        @else
                                            <span class="badge bg-secondary">{{ __('Inactive') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('customers.show', $i) }}" class="btn btn-sm btn-outline-primary" title="{{ __('View') }}">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('customers.edit', $i) }}" class="btn btn-sm btn-outline-warning" title="{{ __('Edit') }}">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger delete-customer-btn" data-customer-id="{{ $i }}" title="{{ __('Delete') }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            <span class="text-muted">{{ __('Showing 1 to 10 of 156 results') }}</span>
                        </div>
                        <nav>
                            <ul class="pagination pagination-sm mb-0">
                                <li class="page-item disabled">
                                    <span class="page-link">{{ __('Previous') }}</span>
                                </li>
                                <li class="page-item active">
                                    <span class="page-link">1</span>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">2</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">3</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">{{ __('Next') }}</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Actions -->
    <div class="row mt-3" id="bulk-actions" style="display: none;">
        <div class="col-12">
            <div class="card border-warning">
                <div class="card-body">
                    <form id="bulk-action-form" method="POST">
                        @csrf
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <span class="text-warning">
                                    <i class="fas fa-exclamation-triangle {{ marginEnd('2') }}"></i>
                                    <span id="selected-count">0</span> {{ __('customers selected') }}
                                </span>
                            </div>
                            <div class="col-md-6">
                                <select class="form-select" name="action" required>
                                    <option value="">{{ __('Choose Action') }}</option>
                                    <option value="activate">{{ __('Activate Selected') }}</option>
                                    <option value="deactivate">{{ __('Deactivate Selected') }}</option>
                                    <option value="delete">{{ __('Delete Selected') }}</option>
                                    <option value="export">{{ __('Export Selected') }}</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-play {{ marginEnd('2') }}"></i>{{ __('Execute') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Localization Data -->
<script id="localization-data" type="application/json">
{!! json_encode([
    'selectAction' => __('Please select an action'),
    'confirmDeleteSelected' => __('Are you sure you want to delete the selected customers?'),
    'confirmDeleteSingle' => __('Are you sure you want to delete this customer?'),
    'csrfToken' => csrf_token()
]) !!}
</script>

<script>
// Get localization data
const localizationData = JSON.parse(document.getElementById('localization-data').textContent);

// Bulk actions functionality
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('select-all');
    const customerCheckboxes = document.querySelectorAll('.customer-checkbox');
    const bulkActions = document.getElementById('bulk-actions');
    const selectedCount = document.getElementById('selected-count');
    const bulkActionForm = document.getElementById('bulk-action-form');

    function updateBulkActions() {
        const checkedBoxes = document.querySelectorAll('.customer-checkbox:checked');
        const count = checkedBoxes.length;

        selectedCount.textContent = count;

        if (count > 0) {
            bulkActions.style.display = 'block';

            // Clear existing hidden inputs
            const existingInputs = bulkActionForm.querySelectorAll('input[name="customers[]"]');
            existingInputs.forEach(input => input.remove());

            // Add selected customer IDs
            checkedBoxes.forEach(checkbox => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'customers[]';
                input.value = checkbox.value;
                bulkActionForm.appendChild(input);
            });
        } else {
            bulkActions.style.display = 'none';
        }
    }

    selectAll.addEventListener('change', function() {
        customerCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkActions();
    });

    customerCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkActions);
    });

    bulkActionForm.addEventListener('submit', function(e) {
        const action = this.querySelector('select[name="action"]').value;
        if (!action) {
            e.preventDefault();
            alert(localizationData.selectAction);
        } else if (action === 'delete') {
            if (!confirm(localizationData.confirmDeleteSelected)) {
                e.preventDefault();
            }
        }
    });
});

// View toggle functionality
function toggleView(viewType) {
    console.log('Toggling to ' + viewType + ' view');
    // Implementation would go here
}

// Delete customer function
function deleteCustomer(customerId) {
    if (confirm(localizationData.confirmDeleteSingle)) {
        // Create form and submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/customers/' + customerId;
        form.style.display = 'none';

        // Add CSRF token
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = localizationData.csrfToken;
        form.appendChild(csrfToken);

        // Add method override
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);

        document.body.appendChild(form);
        form.submit();
    }
}

// Add event delegation for delete buttons
document.addEventListener('DOMContentLoaded', function() {
    document.addEventListener('click', function(e) {
        if (e.target.closest('.delete-customer-btn')) {
            e.preventDefault();
            const button = e.target.closest('.delete-customer-btn');
            const customerId = button.getAttribute('data-customer-id');
            deleteCustomer(customerId);
        }
    });
});
</script>
@endpush

@push('styles')
<style>
.avatar-sm {
    width: 40px;
    height: 40px;
}

.border-left-primary {
    border-left: 4px solid #4e73df !important;
}

.border-left-success {
    border-left: 4px solid #1cc88a !important;
}

.border-left-info {
    border-left: 4px solid #36b9cc !important;
}

.border-left-warning {
    border-left: 4px solid #f6c23e !important;
}

.table th {
    border-top: none;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.8rem;
    letter-spacing: 0.5px;
}

.btn-group .btn {
    margin: 0 1px;
}

.card {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    border: 1px solid #e3e6f0;
}

.text-gray-800 {
    color: #5a5c69 !important;
}

.text-gray-300 {
    color: #dddfeb !important;
}

#bulk-actions {
    position: sticky;
    bottom: 20px;
    z-index: 1000;
}
</style>
@endpush
