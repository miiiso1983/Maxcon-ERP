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

@php
    $salesTrendLabels = isset($analytics['sales_trend']) ? collect($analytics['sales_trend'])->pluck('date') : [];
    $salesTrendData = isset($analytics['sales_trend']) ? collect($analytics['sales_trend'])->pluck('total_amount') : [];
    $paymentMethodLabels = isset($analytics['payment_method_distribution']) ? collect($analytics['payment_method_distribution'])->pluck('payment_method') : [];
    $paymentMethodData = isset($analytics['payment_method_distribution']) ? collect($analytics['payment_method_distribution'])->pluck('total_amount') : [];
    $salesByHourLabels = isset($analytics['sales_by_hour']) ? collect($analytics['sales_by_hour'])->pluck('hour') : [];
    $salesByHourData = isset($analytics['sales_by_hour']) ? collect($analytics['sales_by_hour'])->pluck('total_amount') : [];
    $salesByDayLabels = isset($analytics['sales_by_day_of_week']) ? collect($analytics['sales_by_day_of_week'])->pluck('day') : [];
    $salesByDayData = isset($analytics['sales_by_day_of_week']) ? collect($analytics['sales_by_day_of_week'])->pluck('total_amount') : [];
@endphp
<script id="sales-trend-labels" type="application/json">{!! json_encode($salesTrendLabels) !!}</script>
<script id="sales-trend-data" type="application/json">{!! json_encode($salesTrendData) !!}</script>
<script id="payment-method-labels" type="application/json">{!! json_encode($paymentMethodLabels) !!}</script>
<script id="payment-method-data" type="application/json">{!! json_encode($paymentMethodData) !!}</script>
<script id="sales-by-hour-labels" type="application/json">{!! json_encode($salesByHourLabels) !!}</script>
<script id="sales-by-hour-data" type="application/json">{!! json_encode($salesByHourData) !!}</script>
<script id="sales-by-day-labels" type="application/json">{!! json_encode($salesByDayLabels) !!}</script>
<script id="sales-by-day-data" type="application/json">{!! json_encode($salesByDayData) !!}</script>
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    const salesTrendLabels = JSON.parse(document.getElementById('sales-trend-labels').textContent);
    const salesTrendData = JSON.parse(document.getElementById('sales-trend-data').textContent);
    const paymentMethodLabels = JSON.parse(document.getElementById('payment-method-labels').textContent);
    const paymentMethodData = JSON.parse(document.getElementById('payment-method-data').textContent);
    const salesByHourLabels = JSON.parse(document.getElementById('sales-by-hour-labels').textContent);
    const salesByHourData = JSON.parse(document.getElementById('sales-by-hour-data').textContent);
    const salesByDayLabels = JSON.parse(document.getElementById('sales-by-day-labels').textContent);
    const salesByDayData = JSON.parse(document.getElementById('sales-by-day-data').textContent);
    // Sales Trend Chart
    if (salesTrendLabels.length > 0) {
        const salesTrendCtx = document.getElementById('salesTrendChart').getContext('2d');
        new Chart(salesTrendCtx, {
            type: 'line',
            data: {
                labels: salesTrendLabels,
                datasets: [{
                    label: '{{ __("Sales Amount") }}',
                    data: salesTrendData,
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    }
    // Payment Method Chart
    if (paymentMethodLabels.length > 0) {
        const paymentMethodCtx = document.getElementById('paymentMethodChart').getContext('2d');
        new Chart(paymentMethodCtx, {
            type: 'doughnut',
            data: {
                labels: paymentMethodLabels,
                datasets: [{
                    data: paymentMethodData,
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF'
                    ]
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    }
    // Sales by Hour Chart
    if (salesByHourLabels.length > 0) {
        const salesByHourCtx = document.getElementById('salesByHourChart').getContext('2d');
        new Chart(salesByHourCtx, {
            type: 'bar',
            data: {
                labels: salesByHourLabels,
                datasets: [{
                    label: '{{ __("Sales Amount") }}',
                    data: salesByHourData,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    }
    // Sales by Day Chart
    if (salesByDayLabels.length > 0) {
        const salesByDayCtx = document.getElementById('salesByDayChart').getContext('2d');
        new Chart(salesByDayCtx, {
            type: 'bar',
            data: {
                labels: salesByDayLabels,
                datasets: [{
                    label: '{{ __("Sales Amount") }}',
                    data: salesByDayData,
                    backgroundColor: 'rgba(255, 206, 86, 0.5)',
                    borderColor: 'rgba(255, 206, 86, 1)',
                    borderWidth: 1
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    }
});
</script>
@endpush
