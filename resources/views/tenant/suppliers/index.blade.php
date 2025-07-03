@extends('tenant.layouts.app')

@section('title', __('app.suppliers'))
@section('page-title', __('app.suppliers'))

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">{{ __('app.dashboard') }}</a></li>
<li class="breadcrumb-item active">{{ __('app.suppliers') }}</li>
@endsection

@section('content')
<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stats-card">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col {{ marginEnd('2') }}">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            {{ __('Total Suppliers') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ $stats['total_suppliers'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-truck fa-2x opacity-75"></i>
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
                            {{ __('Active Suppliers') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ $stats['active_suppliers'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle fa-2x opacity-75"></i>
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
                            {{ __('With Outstanding Orders') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ $stats['suppliers_with_orders'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clipboard-list fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stats-card-info">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col {{ marginEnd('2') }}">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            {{ __('Average Rating') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ number_format($stats['average_rating'], 1) }}/5</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-star fa-2x opacity-75"></i>
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
                        <a href="{{ route('suppliers.create') }}" class="btn btn-primary w-100">
                            <i class="fas fa-plus {{ marginEnd('2') }}"></i>{{ __('Add Supplier') }}
                        </a>
                    </div>
                    <div class="col-md-2 mb-2">
                        <div class="dropdown w-100">
                            <button class="btn btn-info dropdown-toggle w-100" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-upload {{ marginEnd('2') }}"></i>{{ __('Import/Export') }}
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="{{ route('suppliers.import') }}">
                                        <i class="fas fa-upload {{ marginEnd('2') }}"></i>{{ __('Import from Excel') }}
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('suppliers.export') }}">
                                        <i class="fas fa-download {{ marginEnd('2') }}"></i>{{ __('Export to Excel') }}
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('templates.suppliers') }}">
                                        <i class="fas fa-file-excel {{ marginEnd('2') }}"></i>{{ __('Download Template') }}
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-2 mb-2">
                        <a href="{{ route('purchase-orders.create') }}" class="btn btn-success w-100">
                            <i class="fas fa-shopping-cart {{ marginEnd('2') }}"></i>{{ __('New Order') }}
                        </a>
                    </div>
                    <div class="col-md-2 mb-2">
                        <a href="{{ route('suppliers.index', ['supplier_type' => 'manufacturer']) }}" class="btn btn-info w-100">
                            <i class="fas fa-industry {{ marginEnd('2') }}"></i>{{ __('Manufacturers') }}
                        </a>
                    </div>
                    <div class="col-md-2 mb-2">
                        <a href="{{ route('suppliers.index', ['supplier_type' => 'distributor']) }}" class="btn btn-warning w-100">
                            <i class="fas fa-shipping-fast {{ marginEnd('2') }}"></i>{{ __('Distributors') }}
                        </a>
                    </div>
                    <div class="col-md-2 mb-2">
                        <a href="{{ route('suppliers.index', ['rating' => '4']) }}" class="btn btn-success w-100">
                            <i class="fas fa-star {{ marginEnd('2') }}"></i>{{ __('Top Rated') }}
                        </a>
                    </div>
                    <div class="col-md-2 mb-2">
                        <a href="{{ route('reports.suppliers') }}" class="btn btn-secondary w-100">
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
        <form method="GET" action="{{ route('suppliers.index') }}">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="search" class="form-label">{{ __('app.search') }}</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="{{ request('search') }}" placeholder="{{ __('Search suppliers...') }}">
                </div>
                <div class="col-md-2 mb-3">
                    <label for="supplier_type" class="form-label">{{ __('Supplier Type') }}</label>
                    <select class="form-select" id="supplier_type" name="supplier_type">
                        <option value="">{{ __('All Types') }}</option>
                        <option value="manufacturer" {{ request('supplier_type') == 'manufacturer' ? 'selected' : '' }}>{{ __('Manufacturer') }}</option>
                        <option value="distributor" {{ request('supplier_type') == 'distributor' ? 'selected' : '' }}>{{ __('Distributor') }}</option>
                        <option value="wholesaler" {{ request('supplier_type') == 'wholesaler' ? 'selected' : '' }}>{{ __('Wholesaler') }}</option>
                        <option value="importer" {{ request('supplier_type') == 'importer' ? 'selected' : '' }}>{{ __('Importer') }}</option>
                        <option value="local" {{ request('supplier_type') == 'local' ? 'selected' : '' }}>{{ __('Local') }}</option>
                        <option value="international" {{ request('supplier_type') == 'international' ? 'selected' : '' }}>{{ __('International') }}</option>
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <label for="status" class="form-label">{{ __('app.status') }}</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">{{ __('All Status') }}</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('app.active') }}</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>{{ __('app.inactive') }}</option>
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <label for="rating" class="form-label">{{ __('Min Rating') }}</label>
                    <select class="form-select" id="rating" name="rating">
                        <option value="">{{ __('Any Rating') }}</option>
                        <option value="4" {{ request('rating') == '4' ? 'selected' : '' }}>4+ Stars</option>
                        <option value="3" {{ request('rating') == '3' ? 'selected' : '' }}>3+ Stars</option>
                        <option value="2" {{ request('rating') == '2' ? 'selected' : '' }}>2+ Stars</option>
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
                    <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times {{ marginEnd('2') }}"></i>{{ __('Clear') }}
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Suppliers Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="m-0">{{ __('Suppliers') }}</h6>
        <div>
            <span class="text-muted">{{ $suppliers->total() }} {{ __('suppliers found') }}</span>
        </div>
    </div>
    <div class="card-body p-0">
        @if($suppliers->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>
                            <input type="checkbox" id="selectAll" class="form-check-input">
                        </th>
                        <th>{{ __('Supplier') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Contact') }}</th>
                        <th>{{ __('Rating') }}</th>
                        <th>{{ __('Credit Info') }}</th>
                        <th>{{ __('app.status') }}</th>
                        <th width="120">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($suppliers as $supplier)
                    <tr>
                        <td>
                            <input type="checkbox" name="suppliers[]" value="{{ $supplier->id }}" class="form-check-input supplier-checkbox">
                        </td>
                        <td>
                            <div>
                                <strong>{{ $supplier->full_name }}</strong>
                                <br>
                                <small class="text-muted">{{ $supplier->supplier_code }}</small>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-{{ $supplier->supplier_type_color }}">
                                {{ ucfirst($supplier->supplier_type) }}
                            </span>
                        </td>
                        <td>
                            <div>
                                @if($supplier->email)
                                <div><i class="fas fa-envelope {{ marginEnd('1') }}"></i>{{ $supplier->email }}</div>
                                @endif
                                @if($supplier->phone)
                                <div><i class="fas fa-phone {{ marginEnd('1') }}"></i>{{ $supplier->phone }}</div>
                                @endif
                                @if($supplier->contact_person)
                                <div><i class="fas fa-user {{ marginEnd('1') }}"></i>{{ $supplier->contact_person }}</div>
                                @endif
                            </div>
                        </td>
                        <td>
                            @if($supplier->rating > 0)
                            <div class="d-flex align-items-center">
                                <span class="badge bg-{{ $supplier->rating_color }} {{ marginEnd('2') }}">{{ number_format($supplier->rating, 1) }}</span>
                                <div class="text-warning">
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= $supplier->rating)
                                            <i class="fas fa-star"></i>
                                        @elseif($i - 0.5 <= $supplier->rating)
                                            <i class="fas fa-star-half-alt"></i>
                                        @else
                                            <i class="far fa-star"></i>
                                        @endif
                                    @endfor
                                </div>
                            </div>
                            @else
                            <span class="text-muted">{{ __('Not Rated') }}</span>
                            @endif
                        </td>
                        <td>
                            <div>
                                <small class="text-muted">{{ __('Limit') }}: {{ formatCurrency($supplier->credit_limit) }}</small>
                                @if($supplier->total_outstanding > 0)
                                <br>
                                <small class="text-warning">{{ __('Outstanding') }}: {{ formatCurrency($supplier->total_outstanding) }}</small>
                                @endif
                                @if($supplier->payment_terms > 0)
                                <br>
                                <small class="text-info">{{ __('Terms') }}: {{ $supplier->payment_terms }} {{ __('days') }}</small>
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-{{ $supplier->is_active ? 'success' : 'secondary' }}">
                                {{ $supplier->is_active ? __('app.active') : __('app.inactive') }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('suppliers.show', $supplier) }}" 
                                   class="btn btn-outline-info" title="{{ __('View') }}">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('suppliers.edit', $supplier) }}" 
                                   class="btn btn-outline-primary" title="{{ __('app.edit') }}">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route('suppliers.performance', $supplier) }}" 
                                   class="btn btn-outline-success" title="{{ __('Performance') }}">
                                    <i class="fas fa-chart-line"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        @if($suppliers->hasPages())
        <div class="card-footer">
            {{ $suppliers->appends(request()->query())->links() }}
        </div>
        @endif
        @else
        <div class="text-center py-5">
            <i class="fas fa-truck fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">{{ __('No suppliers found') }}</h5>
            <p class="text-muted">{{ __('Try adjusting your search criteria or add new suppliers.') }}</p>
            <a href="{{ route('suppliers.create') }}" class="btn btn-primary">
                <i class="fas fa-plus {{ marginEnd('2') }}"></i>{{ __('Add Supplier') }}
            </a>
        </div>
        @endif
    </div>
</div>

<!-- Bulk Actions -->
@if($suppliers->count() > 0)
<div class="card mt-3" id="bulkActions" style="display: none;">
    <div class="card-body">
        <form method="POST" action="{{ route('suppliers.bulk-action') }}" id="bulkActionForm">
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
    const supplierCheckboxes = document.querySelectorAll('.supplier-checkbox');
    const bulkActions = document.getElementById('bulkActions');
    const selectedCount = document.getElementById('selectedCount');
    const bulkActionForm = document.getElementById('bulkActionForm');

    function updateBulkActions() {
        const checkedBoxes = document.querySelectorAll('.supplier-checkbox:checked');
        const count = checkedBoxes.length;
        
        if (count > 0) {
            bulkActions.style.display = 'block';
            selectedCount.textContent = `${count} supplier(s) selected`;
            
            // Add hidden inputs for selected suppliers
            const existingInputs = bulkActionForm.querySelectorAll('input[name="suppliers[]"]');
            existingInputs.forEach(input => input.remove());
            
            checkedBoxes.forEach(checkbox => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'suppliers[]';
                input.value = checkbox.value;
                bulkActionForm.appendChild(input);
            };
        } else {
            bulkActions.style.display = 'none';
        }
    }

    selectAll.addEventListener('change', function() {
        supplierCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkActions();
    });

    supplierCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkActions);
    });

    bulkActionForm.addEventListener('submit', function(e) {
        const action = this.querySelector('select[name="action"]').value;
        if (action === 'delete') {
            if (!confirm('Are you sure you want to delete the selected suppliers? This action cannot be undone.')) {
                e.preventDefault();
            }
        }
    });
});
</script>
@endpush
