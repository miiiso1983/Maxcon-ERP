@extends('tenant.layouts.app')

@section('title', __('Medical Reps Performance'))

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">📊 {{ __('Medical Reps Performance') }}</h1>
            <p class="text-muted">{{ __('Track and analyze medical representatives performance metrics') }}</p>
        </div>
        <div>
            <a href="{{ route('medical-reps.dashboard') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> {{ __('Back to Dashboard') }}
            </a>
        </div>
    </div>

    <!-- Performance Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">{{ __('Medical Rep') }}</label>
                    <select name="rep_id" class="form-select">
                        <option value="">{{ __('All Representatives') }}</option>
                        <!-- Add medical reps options here -->
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ __('Period') }}</label>
                    <select name="period" class="form-select">
                        <option value="today">{{ __('Today') }}</option>
                        <option value="week">{{ __('This Week') }}</option>
                        <option value="month" selected>{{ __('This Month') }}</option>
                        <option value="quarter">{{ __('This Quarter') }}</option>
                        <option value="year">{{ __('This Year') }}</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ __('From Date') }}</label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ __('To Date') }}</label>
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> {{ __('Filter Results') }}
                    </button>
                    <a href="{{ route('medical-reps.performance.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-refresh"></i> {{ __('Reset') }}
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Performance Overview Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ number_format(150) }}</h4>
                            <p class="mb-0">{{ __('Total Visits') }}</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-map-marker-alt fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ number_format(85000, 0) }} {{ __('SAR') }}</h4>
                            <p class="mb-0">{{ __('Sales Generated') }}</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-dollar-sign fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ number_format(45) }}</h4>
                            <p class="mb-0">{{ __('New Customers') }}</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ number_format(92, 1) }}%</h4>
                            <p class="mb-0">{{ __('Success Rate') }}</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-chart-line fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Charts -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Sales Performance Trend') }}</h5>
                </div>
                <div class="card-body">
                    <canvas id="salesChart" height="100"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Visit Types Distribution') }}</h5>
                </div>
                <div class="card-body">
                    <canvas id="visitTypesChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Performers Table -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ __('Top Performers') }}</h5>
            <div>
                <button class="btn btn-sm btn-outline-primary" onclick="exportToExcel()">
                    <i class="fas fa-file-excel"></i> {{ __('Export Excel') }}
                </button>
                <button class="btn btn-sm btn-outline-secondary" onclick="printReport()">
                    <i class="fas fa-print"></i> {{ __('Print') }}
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>{{ __('Rank') }}</th>
                            <th>{{ __('Medical Rep') }}</th>
                            <th>{{ __('Visits') }}</th>
                            <th>{{ __('Sales') }}</th>
                            <th>{{ __('New Customers') }}</th>
                            <th>{{ __('Success Rate') }}</th>
                            <th>{{ __('Performance Score') }}</th>
                            <th>{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for($i = 1; $i <= 10; $i++)
                        <tr>
                            <td>
                                <span class="badge bg-{{ $i <= 3 ? 'warning' : 'secondary' }}">
                                    #{{ $i }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm me-2">
                                        <div class="avatar-title bg-primary rounded-circle">
                                            {{ substr('Rep ' . $i, 0, 2) }}
                                        </div>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ __('Medical Rep') }} {{ $i }}</h6>
                                        <small class="text-muted">rep{{ $i }}@maxcon.com</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ rand(20, 50) }}</td>
                            <td>{{ number_format(rand(15000, 45000)) }} {{ __('SAR') }}</td>
                            <td>{{ rand(3, 12) }}</td>
                            <td>
                                @php $rate = rand(75, 98) @endphp
                                <span class="badge bg-{{ $rate >= 90 ? 'success' : ($rate >= 80 ? 'warning' : 'danger') }}">
                                    {{ $rate }}%
                                </span>
                            </td>
                            <td>
                                @php $score = rand(70, 95) @endphp
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-{{ $score >= 85 ? 'success' : ($score >= 70 ? 'warning' : 'danger') }}" 
                                         style="width: {{ $score }}%"></div>
                                </div>
                                <small>{{ $score }}/100</small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="viewDetails({{ $i }})">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-info" onclick="viewReport({{ $i }})">
                                        <i class="fas fa-chart-bar"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Performance Metrics -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Key Performance Indicators') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="text-center p-3 border rounded">
                                <h4 class="text-primary">{{ number_format(8.5, 1) }}</h4>
                                <small class="text-muted">{{ __('Avg Visits/Day') }}</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-3 border rounded">
                                <h4 class="text-success">{{ number_format(567, 0) }}</h4>
                                <small class="text-muted">{{ __('Avg Sale Value') }}</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-3 border rounded">
                                <h4 class="text-info">{{ number_format(2.3, 1) }}</h4>
                                <small class="text-muted">{{ __('Conversion Rate') }}</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-3 border rounded">
                                <h4 class="text-warning">{{ number_format(45, 0) }}</h4>
                                <small class="text-muted">{{ __('Avg Visit Duration') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Performance Goals') }}</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>{{ __('Monthly Sales Target') }}</span>
                            <span>85%</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-success" style="width: 85%"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>{{ __('Visit Target') }}</span>
                            <span>92%</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-info" style="width: 92%"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>{{ __('Customer Acquisition') }}</span>
                            <span>78%</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-warning" style="width: 78%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Sales Performance Chart
const salesCtx = document.getElementById('salesChart').getContext('2d');
new Chart(salesCtx, {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [{
            label: 'Sales (SAR)',
            data: [12000, 19000, 15000, 25000, 22000, 30000],
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.1)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

// Visit Types Chart
const visitTypesCtx = document.getElementById('visitTypesChart').getContext('2d');
new Chart(visitTypesCtx, {
    type: 'doughnut',
    data: {
        labels: ['Sales Visit', 'Follow-up', 'Presentation', 'Support'],
        datasets: [{
            data: [45, 25, 20, 10],
            backgroundColor: [
                'rgba(255, 99, 132, 0.8)',
                'rgba(54, 162, 235, 0.8)',
                'rgba(255, 205, 86, 0.8)',
                'rgba(75, 192, 192, 0.8)'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

function viewDetails(repId) {
    alert('View details for rep ' + repId);
}

function viewReport(repId) {
    alert('View report for rep ' + repId);
}

function exportToExcel() {
    alert('Export to Excel functionality');
}

function printReport() {
    window.print();
}
</script>
@endpush
