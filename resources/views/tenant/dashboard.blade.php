@extends('tenant.layouts.app')

@section('title', __('app.dashboard'))
@section('page-title', __('app.dashboard'))

@section('content')
<div class="row">
    <!-- Quick Stats Cards -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col {{ marginEnd('2') }}">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            {{ __('Total Products') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">1,234</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-boxes fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card-success">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col {{ marginEnd('2') }}">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            {{ __('Today Sales') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ formatCurrency(15420) }} }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-cash-register fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card-warning">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col {{ marginEnd('2') }}">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            {{ __('Low Stock Items') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">23</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card-danger">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col {{ marginEnd('2') }}">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            {{ __('Expiring Soon') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">8</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Sales Chart -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold">{{ __('Sales Overview') }}</h6>
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        {{ __('Last 7 Days') }}
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#">{{ __('Last 7 Days') }}</a></li>
                        <li><a class="dropdown-item" href="#">{{ __('Last 30 Days') }}</a></li>
                        <li><a class="dropdown-item" href="#">{{ __('Last 3 Months') }}</a></li>
                    </ul>
                </div>
            </div>
            <div class="card-body">
                <canvas id="salesChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>

    <!-- Top Products -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold">{{ __('Top Selling Products') }}</h6>
                <a href="{{ route('reports.index') }}" class="btn btn-sm btn-primary">{{ __('View All') }}</a>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Paracetamol 500mg</h6>
                            <small class="text-muted">{{ __('Pain Relief') }}</small>
                        </div>
                        <span class="badge bg-primary rounded-pill">245</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Amoxicillin 250mg</h6>
                            <small class="text-muted">{{ __('Antibiotics') }}</small>
                        </div>
                        <span class="badge bg-primary rounded-pill">189</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Vitamin C 1000mg</h6>
                            <small class="text-muted">{{ __('Vitamins') }}</small>
                        </div>
                        <span class="badge bg-primary rounded-pill">156</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Ibuprofen 400mg</h6>
                            <small class="text-muted">{{ __('Pain Relief') }}</small>
                        </div>
                        <span class="badge bg-primary rounded-pill">134</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Transactions -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold">{{ __('Recent Transactions') }}</h6>
                <a href="{{ route('sales.index') }}" class="btn btn-sm btn-primary">{{ __('View All') }}</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('Invoice') }}</th>
                                <th>{{ __('Customer') }}</th>
                                <th>{{ __('Amount') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Date') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>#INV-001</strong></td>
                                <td>Ahmed Ali</td>
                                <td>{{ formatCurrency(125.50) }} }}</td>
                                <td><span class="badge bg-success">{{ __('Paid') }}</span></td>
                                <td>{{ now()->format('M d, Y') }}</td>
                            </tr>
                            <tr>
                                <td><strong>#INV-002</strong></td>
                                <td>Sara Mohammed</td>
                                <td>{{ formatCurrency(89.25) }} }}</td>
                                <td><span class="badge bg-success">{{ __('Paid') }}</span></td>
                                <td>{{ now()->subHour()->format('M d, Y') }}</td>
                            </tr>
                            <tr>
                                <td><strong>#INV-003</strong></td>
                                <td>Omar Hassan</td>
                                <td>{{ formatCurrency(234.75) }} }}</td>
                                <td><span class="badge bg-warning">{{ __('Pending') }}</span></td>
                                <td>{{ now()->subHours(2)->format('M d, Y') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold">{{ __('Quick Actions') }}</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('sales.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus {{ marginEnd('2') }}"></i>{{ __('New Sale') }}
                    </a>
                    <a href="{{ route('inventory.products.create') }}" class="btn btn-info">
                        <i class="fas fa-box {{ marginEnd('2') }}"></i>{{ __('Add Product') }}
                    </a>
                    <a href="{{ route('customers.create') }}" class="btn btn-success">
                        <i class="fas fa-user-plus {{ marginEnd('2') }}"></i>{{ __('Add Customer') }}
                    </a>
                    <a href="{{ route('reports.index') }}" class="btn btn-secondary">
                        <i class="fas fa-chart-bar {{ marginEnd('2') }}"></i>{{ __('View Reports') }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Alerts -->
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-warning">{{ __('Alerts') }}</h6>
            </div>
            <div class="card-body">
                <div class="alert alert-warning alert-sm mb-2">
                    <i class="fas fa-exclamation-triangle {{ marginEnd('2') }}"></i>
                    <strong>23</strong> {{ __('products are running low on stock') }}
                </div>
                <div class="alert alert-danger alert-sm mb-2">
                    <i class="fas fa-clock {{ marginEnd('2') }}"></i>
                    <strong>8</strong> {{ __('products expiring in 30 days') }}
                </div>
                <div class="alert alert-info alert-sm mb-0">
                    <i class="fas fa-info-circle {{ marginEnd('2') }}"></i>
                    {{ __('License expires in 45 days') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Sales Chart
const ctx = document.getElementById('salesChart').getContext('2d');
const salesChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        datasets: [{
            label: 'Sales',
            data: [12000, 19000, 15000, 25000, 22000, 30000, 28000],
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return 'IQD ' + value.toLocaleString();
                    }
                }
            }
        },
        plugins: {
            legend: {
                display: false
            }
        }
    }
};
</script>
@endpush
