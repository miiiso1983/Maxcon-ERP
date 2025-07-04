@extends('tenant.layouts.app')

@section('title', __('Sales Analytics'))
@section('page-title', __('Sales Performance Analytics'))

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">{{ __('app.dashboard') }}</a></li>
<li class="breadcrumb-item"><a href="{{ route('reports.dashboard') }}">{{ __('Reports') }}</a></li>
<li class="breadcrumb-item"><a href="{{ route('analytics.dashboard') }}">{{ __('Analytics') }}</a></li>
<li class="breadcrumb-item active">{{ __('Sales Analytics') }}</li>
@endsection

@section('content')
<!-- Period Selection -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('analytics.sales') }}">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label for="period" class="form-label">{{ __('Analysis Period') }}</label>
                            <select name="period" id="period" class="form-select">
                                <option value="7" {{ request('period') == '7' ? 'selected' : '' }}>{{ __('Last 7 Days') }}</option>
                                <option value="30" {{ request('period', '30') == '30' ? 'selected' : '' }}>{{ __('Last 30 Days') }}</option>
                                <option value="90" {{ request('period') == '90' ? 'selected' : '' }}>{{ __('Last 90 Days') }}</option>
                                <option value="365" {{ request('period') == '365' ? 'selected' : '' }}>{{ __('Last Year') }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search {{ marginEnd('2') }}"></i>{{ __('Analyze') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Sales Metrics -->
@if(isset($analytics['sales_trend']))
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stats-card-success">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col {{ marginEnd('2') }}">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            {{ __('Total Sales') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">
                            {{ formatCurrency(collect($analytics['sales_trend'])->sum('total_amount')) }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-dollar-sign fa-2x opacity-75"></i>
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
                            {{ __('Total Orders') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">
                            {{ collect($analytics['sales_trend'])->sum('order_count') }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-shopping-cart fa-2x opacity-75"></i>
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
                            {{ __('Avg Order Value') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">
                            {{ formatCurrency($analytics['average_order_value'] ?? 0) }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-chart-line fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stats-card-primary">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col {{ marginEnd('2') }}">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            {{ __('Daily Average') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">
                            {{ formatCurrency(collect($analytics['sales_trend'])->avg('total_amount')) }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar-day fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Sales Trend Chart -->
@if(isset($analytics['sales_trend']))
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0">{{ __('Sales Trend') }}</h6>
            </div>
            <div class="card-body">
                <canvas id="salesTrendChart" height="100"></canvas>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Payment Methods Distribution -->
@if(isset($analytics['payment_method_distribution']))
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0">{{ __('Payment Methods') }}</h6>
            </div>
            <div class="card-body">
                <canvas id="paymentMethodChart" height="200"></canvas>
            </div>
        </div>
    </div>

    <!-- Sales by Hour -->
    @if(isset($analytics['sales_by_hour']))
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0">{{ __('Sales by Hour') }}</h6>
            </div>
            <div class="card-body">
                <canvas id="salesByHourChart" height="200"></canvas>
            </div>
        </div>
    </div>
    @endif
</div>
@endif

<!-- Sales by Day of Week -->
@if(isset($analytics['sales_by_day_of_week']))
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0">{{ __('Sales by Day of Week') }}</h6>
            </div>
            <div class="card-body">
                <canvas id="salesByDayChart" height="100"></canvas>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Conversion Metrics -->
@if(isset($analytics['conversion_metrics']))
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0">{{ __('Conversion Metrics') }}</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    @foreach($analytics['conversion_metrics'] as $metric => $value)
                    <div class="col-md-3 mb-3">
                        <div class="h4 mb-0">{{ is_numeric($value) ? number_format($value, 2) : $value }}</div>
                        <small class="text-muted">{{ __(ucwords(str_replace('_', ' ', $metric))) }}</small>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Back to Analytics -->
<div class="row">
    <div class="col-12">
        <a href="{{ route('analytics.dashboard') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left {{ marginEnd('2') }}"></i>{{ __('Back to Analytics Dashboard') }}
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Sales Trend Chart
    @if(isset($analytics['sales_trend']))
    const salesTrendCtx = document.getElementById('salesTrendChart').getContext('2d');
    new Chart(salesTrendCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode(collect($analytics['sales_trend'])->pluck('date')) !!},
            datasets: [{
                label: '{{ __("Sales Amount") }}',
                data: {!! json_encode(collect($analytics['sales_trend'])->pluck('total_amount')) !!},
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
    @endif

    // Payment Method Chart
    @if(isset($analytics['payment_method_distribution']))
    const paymentMethodCtx = document.getElementById('paymentMethodChart').getContext('2d');
    new Chart(paymentMethodCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode(collect($analytics['payment_method_distribution'])->pluck('payment_method')) !!},
            datasets: [{
                data: {!! json_encode(collect($analytics['payment_method_distribution'])->pluck('total_amount')) !!},
                backgroundColor: [
                    '#FF6384',
                    '#36A2EB',
                    '#FFCE56',
                    '#4BC0C0',
                    '#9966FF'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
    @endif

    // Sales by Hour Chart
    @if(isset($analytics['sales_by_hour']))
    const salesByHourCtx = document.getElementById('salesByHourChart').getContext('2d');
    new Chart(salesByHourCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode(collect($analytics['sales_by_hour'])->pluck('hour')) !!},
            datasets: [{
                label: '{{ __("Sales Amount") }}',
                data: {!! json_encode(collect($analytics['sales_by_hour'])->pluck('total_amount')) !!},
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
    @endif

    // Sales by Day Chart
    @if(isset($analytics['sales_by_day_of_week']))
    const salesByDayCtx = document.getElementById('salesByDayChart').getContext('2d');
    new Chart(salesByDayCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode(collect($analytics['sales_by_day_of_week'])->pluck('day_name')) !!},
            datasets: [{
                label: '{{ __("Sales Amount") }}',
                data: {!! json_encode(collect($analytics['sales_by_day_of_week'])->pluck('total_amount')) !!},
                backgroundColor: 'rgba(255, 99, 132, 0.5)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
    @endif
});
</script>
@endpush
