@extends('tenant.layouts.app')

@section('title', __('Suppliers Report'))
@section('page-title', __('Suppliers Report'))

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">{{ __('app.dashboard') }}</a></li>
<li class="breadcrumb-item"><a href="{{ route('reports.dashboard') }}">{{ __('Reports') }}</a></li>
<li class="breadcrumb-item active">{{ __('Suppliers Report') }}</li>
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
                            <i class="fas fa-truck {{ marginEnd('2') }}"></i>
                            {{ __('Suppliers Performance Report') }}
                        </h5>
                        <div>
                            <button type="button" class="btn btn-outline-danger btn-sm {{ marginEnd('2') }}" onclick="exportReport(event, 'pdf')" id="export-pdf-btn">
                                <i class="fas fa-file-pdf {{ marginEnd('2') }}"></i>{{ __('Export PDF') }}
                            </button>
                            <button type="button" class="btn btn-outline-success btn-sm" onclick="exportReport(event, 'excel')" id="export-excel-btn">
                                <i class="fas fa-file-excel {{ marginEnd('2') }}"></i>{{ __('Export Excel') }}
                            </button>
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
                    <h6 class="card-title mb-0">{{ __('Report Filters') }}</h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('reports.suppliers') }}">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="date_from" class="form-label">{{ __('From Date') }}</label>
                                <input type="date" class="form-control" id="date_from" name="date_from" 
                                       value="{{ request('date_from', date('Y-m-01')) }}">
                            </div>
                            <div class="col-md-3">
                                <label for="date_to" class="form-label">{{ __('To Date') }}</label>
                                <input type="date" class="form-control" id="date_to" name="date_to" 
                                       value="{{ request('date_to', date('Y-m-d')) }}">
                            </div>
                            <div class="col-md-2">
                                <label for="status_filter" class="form-label">{{ __('Status') }}</label>
                                <select class="form-select" id="status_filter" name="status">
                                    <option value="">{{ __('All Status') }}</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="sort_by" class="form-label">{{ __('Sort By') }}</label>
                                <select class="form-select" id="sort_by" name="sort_by">
                                    <option value="name" {{ request('sort_by') == 'name' ? 'selected' : '' }}>{{ __('Name') }}</option>
                                    <option value="total_spent" {{ request('sort_by') == 'total_spent' ? 'selected' : '' }}>{{ __('Total Spent') }}</option>
                                    <option value="total_orders" {{ request('sort_by') == 'total_orders' ? 'selected' : '' }}>{{ __('Total Orders') }}</option>
                                    <option value="last_order" {{ request('sort_by') == 'last_order' ? 'selected' : '' }}>{{ __('Last Order') }}</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search {{ marginEnd('2') }}"></i>{{ __('Apply Filters') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-primary">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col {{ marginEnd('2') }}">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                {{ __('Total Suppliers') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $suppliers->count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-truck fa-2x text-gray-300"></i>
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
                                {{ __('Active Suppliers') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $suppliers->where('is_active', true)->count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
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
                                {{ __('Total Orders') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $suppliers->sum('total_orders') }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
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
                                {{ __('Total Spent') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ formatCurrency($suppliers->sum('total_spent')) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Suppliers Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-0">{{ __('Suppliers Performance') }}</h6>
                        <span class="text-muted">{{ __('Showing') }} {{ $suppliers->count() }} {{ __('suppliers') }}</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('Supplier') }}</th>
                                    <th>{{ __('Contact') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th class="text-center">{{ __('Orders') }}</th>
                                    <th class="text-end">{{ __('Total Spent') }}</th>
                                    <th class="text-center">{{ __('Rating') }}</th>
                                    <th class="text-center">{{ __('Last Order') }}</th>
                                    <th class="text-center">{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($suppliers as $supplier)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center {{ marginEnd('3') }}">
                                                <i class="fas fa-building text-white"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $supplier['name'] }}</h6>
                                                <small class="text-muted">{{ $supplier['contact_person'] ?? 'N/A' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <div><i class="fas fa-phone {{ marginEnd('2') }}"></i>{{ $supplier['phone'] ?? 'N/A' }}</div>
                                            <div><i class="fas fa-envelope {{ marginEnd('2') }}"></i>{{ $supplier['email'] ?? 'N/A' }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        @if(($supplier['supplier_type'] ?? '') == 'manufacturer')
                                            <span class="badge bg-primary">{{ __('Manufacturer') }}</span>
                                        @elseif(($supplier['supplier_type'] ?? '') == 'distributor')
                                            <span class="badge bg-success">{{ __('Distributor') }}</span>
                                        @elseif(($supplier['supplier_type'] ?? '') == 'wholesaler')
                                            <span class="badge bg-info">{{ __('Wholesaler') }}</span>
                                        @else
                                            <span class="badge bg-secondary">{{ __('Other') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark">{{ $supplier['total_orders'] ?? 0 }}</span>
                                    </td>
                                    <td class="text-end">
                                        <strong class="text-success">{{ formatCurrency($supplier['total_spent'] ?? 0) }}</strong>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex align-items-center justify-content-center">
                                            @php $rating = $supplier['rating'] ?? 0; @endphp
                                            @for($i = 1; $i <= 5; $i++)
                                                @if($i <= floor($rating))
                                                    <i class="fas fa-star text-warning"></i>
                                                @elseif($i - 0.5 <= $rating)
                                                    <i class="fas fa-star-half-alt text-warning"></i>
                                                @else
                                                    <i class="far fa-star text-muted"></i>
                                                @endif
                                            @endfor
                                            <span class="ms-1 text-muted">({{ number_format($rating, 1) }})</span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        @if(isset($supplier['last_order']))
                                            <small class="text-muted">{{ \Carbon\Carbon::parse($supplier['last_order'])->format('M d, Y') }}</small>
                                        @else
                                            <small class="text-muted">N/A</small>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($supplier['is_active'] ?? false)
                                            <span class="badge bg-success">{{ __('Active') }}</span>
                                        @else
                                            <span class="badge bg-secondary">{{ __('Inactive') }}</span>
                                        @endif
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
</div>
@endsection

@push('scripts')
<script>
function exportReport(event, format) {
    // Get the clicked button
    const button = event.target.closest('button');
    const originalText = button.innerHTML;

    // Show loading state
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>' + '{{ __("Exporting...") }}';
    button.disabled = true;
    
    // Get current filters
    const filters = {
        date_from: document.getElementById('date_from').value,
        date_to: document.getElementById('date_to').value,
        status: document.getElementById('status_filter').value,
        sort_by: document.getElementById('sort_by').value,
        format: format
    };
    
    // Create form and submit
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("reports.suppliers.export") }}';
    form.style.display = 'none';
    
    // Add CSRF token
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = '{{ csrf_token() }}';
    form.appendChild(csrfToken);
    
    // Add filters as hidden inputs
    Object.keys(filters).forEach(key => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = filters[key];
        form.appendChild(input);
    });
    
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
    
    // Reset button after delay
    setTimeout(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    }, 3000);
    
    // Show success message
    setTimeout(() => {
        showNotification('{{ __("Report exported successfully!") }}', 'success');
    }, 1000);
}

// Helper function to show notifications
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 5000);
}
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
</style>
@endpush
