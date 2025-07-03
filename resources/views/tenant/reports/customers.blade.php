@extends('tenant.layouts.app')

@section('title', __('Customers Report'))
@section('page-title', __('Customers Report'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">{{ __('app.dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">{{ __('Reports') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Customers Report') }}</li>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Report Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-users {{ marginEnd('2') }}"></i>
                            {{ __('Customers Report') }}
                        </h5>
                        <div>
                            <button class="btn btn-outline-danger btn-sm {{ marginEnd('2') }}" onclick="exportReport('pdf')" id="export-pdf-btn">
                                <i class="fas fa-file-pdf {{ marginEnd('2') }}"></i>{{ __('Export PDF') }}
                            </button>
                            <button class="btn btn-outline-success btn-sm" onclick="exportReport('excel')" id="export-excel-btn">
                                <i class="fas fa-file-excel {{ marginEnd('2') }}"></i>{{ __('Export Excel') }}
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="date_from" class="form-label">{{ __('From Date') }}</label>
                            <input type="date" class="form-control" id="date_from" value="{{ date('Y-m-01') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="date_to" class="form-label">{{ __('To Date') }}</label>
                            <input type="date" class="form-control" id="date_to" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="status_filter" class="form-label">{{ __('Status') }}</label>
                            <select class="form-select" id="status_filter" data-placeholder="{{ __('All Status') }}">
                                <option value="">{{ __('All Status') }}</option>
                                <option value="active">{{ __('Active') }}</option>
                                <option value="inactive">{{ __('Inactive') }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="sort_by" class="form-label">{{ __('Sort By') }}</label>
                            <select class="form-select" id="sort_by" data-placeholder="{{ __('Sort By') }}">
                                <option value="name">{{ __('Name') }}</option>
                                <option value="total_spent">{{ __('Total Spent') }}</option>
                                <option value="total_orders">{{ __('Total Orders') }}</option>
                                <option value="last_order">{{ __('Last Order') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <button class="btn btn-primary" onclick="generateReport()">
                                <i class="fas fa-search {{ marginEnd('2') }}"></i>{{ __('Generate Report') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-primary">{{ $customers->count() }}</h3>
                    <p class="mb-0">{{ __('Total Customers') }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-success">{{ formatCurrency($customers->sum('total_spent')) }}</h3>
                    <p class="mb-0">{{ __('Total Revenue') }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-info">{{ $customers->sum('total_orders') }}</h3>
                    <p class="mb-0">{{ __('Total Orders') }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-warning">{{ formatCurrency($customers->avg('total_spent')) }}</h3>
                    <p class="mb-0">{{ __('Average Spent') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Customers Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">{{ __('Customers Details') }}</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="customers-table">
                            <thead class="table-dark">
                                <tr>
                                    <th>{{ __('Customer') }}</th>
                                    <th>{{ __('Contact') }}</th>
                                    <th class="text-center">{{ __('Total Orders') }}</th>
                                    <th class="text-end">{{ __('Total Spent') }}</th>
                                    <th class="text-center">{{ __('Last Order') }}</th>
                                    <th class="text-center">{{ __('Status') }}</th>
                                    <th class="text-center">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($customers as $customer)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center {{ marginEnd('3') }}">
                                                <i class="fas fa-user text-white"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $customer['name'] }}</h6>
                                                <small class="text-muted">ID: {{ $customer['id'] }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <div><i class="fas fa-envelope {{ marginEnd('2') }}"></i>{{ $customer['email'] }}</div>
                                            <div><i class="fas fa-phone {{ marginEnd('2') }}"></i>{{ $customer['phone'] }}</div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info">{{ $customer['total_orders'] }}</span>
                                    </td>
                                    <td class="text-end">
                                        <strong class="text-success">{{ formatCurrency($customer['total_spent']) }}</strong>
                                    </td>
                                    <td class="text-center">
                                        <span class="text-muted">{{ $customer['last_order']->diffForHumans() }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($customer['status'] == 'active')
                                            <span class="badge bg-success">{{ __('Active') }}</span>
                                        @else
                                            <span class="badge bg-secondary">{{ __('Inactive') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-primary view-customer-btn"
                                                    data-customer-id="{{ $customer['id'] }}" title="{{ __('View Details') }}">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-info customer-orders-btn"
                                                    data-customer-id="{{ $customer['id'] }}" title="{{ __('View Orders') }}">
                                                <i class="fas fa-shopping-cart"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-success contact-customer-btn"
                                                    data-customer-id="{{ $customer['id'] }}" title="{{ __('Contact') }}">
                                                <i class="fas fa-envelope"></i>
                                            </button>
                                        </div>
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

    <!-- Charts -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">{{ __('Customer Spending Distribution') }}</h6>
                </div>
                <div class="card-body">
                    <canvas id="spendingChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">{{ __('Customer Activity Timeline') }}</h6>
                </div>
                <div class="card-body">
                    <canvas id="activityChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Chart Data -->
<script id="chart-data" type="application/json">
{!! json_encode([
    'customers' => [
        'names' => $customers->pluck('name'),
        'totalSpent' => $customers->pluck('total_spent'),
        'totalOrders' => $customers->pluck('total_orders')
    ],
    'labels' => [
        'totalOrders' => __('Total Orders'),
        'exporting' => __('Exporting...'),
        'exportSuccess' => __('Report exported successfully!'),
        'viewingCustomer' => __('Viewing customer'),
        'viewingOrders' => __('Viewing orders for customer'),
        'contactingCustomer' => __('Contacting customer')
    ],
    'routes' => [
        'export' => route('reports.customers.export')
    ],
    'csrfToken' => csrf_token()
]) !!}
</script>

<script>
// Get chart data
const chartData = JSON.parse(document.getElementById('chart-data').textContent);

// Initialize charts
document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
    initializeEventHandlers();
});

function initializeCharts() {
    // Spending Distribution Chart
    const spendingCtx = document.getElementById('spendingChart').getContext('2d');
    new Chart(spendingCtx, {
        type: 'doughnut',
        data: {
            labels: chartData.customers.names,
            datasets: [{
                data: chartData.customers.totalSpent,
                backgroundColor: [
                    '#FF6384',
                    '#36A2EB',
                    '#FFCE56',
                    '#4BC0C0',
                    '#9966FF',
                    '#FF9F40'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Activity Timeline Chart
    const activityCtx = document.getElementById('activityChart').getContext('2d');
    new Chart(activityCtx, {
        type: 'line',
        data: {
            labels: chartData.customers.names,
            datasets: [{
                label: chartData.labels.totalOrders,
                data: chartData.customers.totalOrders,
                borderColor: '#36A2EB',
                backgroundColor: 'rgba(54, 162, 235, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

function initializeEventHandlers() {
    // Event delegation for customer action buttons
    document.addEventListener('click', function(e) {
        const customerId = e.target.closest('[data-customer-id]')?.getAttribute('data-customer-id');

        if (e.target.closest('.view-customer-btn')) {
            viewCustomer(customerId);
        } else if (e.target.closest('.customer-orders-btn')) {
            customerOrders(customerId);
        } else if (e.target.closest('.contact-customer-btn')) {
            contactCustomer(customerId);
        }
    });
}

function viewCustomer(customerId) {
    // Implementation for viewing customer details
    console.log('Viewing customer:', customerId);
    // You can add modal or redirect logic here
}

function customerOrders(customerId) {
    // Implementation for viewing customer orders
    console.log('Viewing orders for customer:', customerId);
    // You can add modal or redirect logic here
}

function contactCustomer(customerId) {
    // Implementation for contacting customer
    console.log('Contacting customer:', customerId);
    // You can add modal or redirect logic here
}

function generateReport() {
    // In a real application, this would filter and regenerate the report
    console.log('Generating report with filters...');
    // You can add AJAX call here to filter data
}

function exportReport(format) {
    // Get the clicked button
    const button = event.target.closest('button');
    const originalText = button.innerHTML;

    // Show loading state
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>' + chartData.labels.exporting;
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
    form.action = chartData.routes.export;
    form.style.display = 'none';

    // Add CSRF token
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = chartData.csrfToken;
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
        showNotification(chartData.labels.exportSuccess, 'success');
    }, 1000);
}

// Helper function to show notifications
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    document.body.appendChild(notification);

    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 5000);
}

function viewCustomer(customerId) {
    // In a real application, this would open customer details
    alert(`{{ __("Viewing customer") }} ${customerId}`);
}

function customerOrders(customerId) {
    // In a real application, this would show customer orders
    alert(`{{ __("Viewing orders for customer") }} ${customerId}`);
}

function contactCustomer(customerId) {
    // In a real application, this would open contact form
    alert(`{{ __("Contacting customer") }} ${customerId}`);
}
</script>
@endpush

@push('styles')
<style>
.avatar-sm {
    width: 40px;
    height: 40px;
}

.table th {
    border-top: none;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.btn-group .btn {
    margin: 0 1px;
}
</style>
@endpush
