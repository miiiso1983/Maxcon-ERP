@extends('tenant.layouts.app')

@section('title', __('Profitability Analysis'))
@section('page-title', __('Profitability & Financial Analytics'))

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">{{ __('app.dashboard') }}</a></li>
<li class="breadcrumb-item"><a href="{{ route('reports.dashboard') }}">{{ __('Reports') }}</a></li>
<li class="breadcrumb-item"><a href="{{ route('analytics.dashboard') }}">{{ __('Analytics') }}</a></li>
<li class="breadcrumb-item active">{{ __('Profitability') }}</li>
@endsection

@section('content')
<!-- Period Selection -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('analytics.profitability') }}">
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

<!-- Gross Margin Analysis -->
@if(isset($analytics['gross_margin_analysis']))
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0">{{ __('Gross Margin Analysis') }}</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="card stats-card-success">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col {{ marginEnd('2') }}">
                                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                                            {{ __('Total Revenue') }}
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold">
                                            {{ formatCurrency($analytics['gross_margin_analysis']['total_revenue'] ?? 0) }}
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
                                            {{ __('Total Cost') }}
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold">
                                            {{ formatCurrency($analytics['gross_margin_analysis']['total_cost'] ?? 0) }}
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-receipt fa-2x opacity-75"></i>
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
                                            {{ __('Gross Profit') }}
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold">
                                            {{ formatCurrency($analytics['gross_margin_analysis']['gross_profit'] ?? 0) }}
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
                                            {{ __('Margin %') }}
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold">
                                            {{ number_format($analytics['gross_margin_analysis']['margin_percentage'] ?? 0, 1) }}%
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-percentage fa-2x opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Profit by Product -->
@if(isset($analytics['profit_by_product']))
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0">{{ __('Most Profitable Products') }}</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>{{ __('Product') }}</th>
                                <th>{{ __('Revenue') }}</th>
                                <th>{{ __('Cost') }}</th>
                                <th>{{ __('Profit') }}</th>
                                <th>{{ __('Margin %') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(array_slice($analytics['profit_by_product'], 0, 10) as $product)
                            <tr>
                                <td>{{ $product['product_name'] }}</td>
                                <td>{{ formatCurrency($product['total_revenue']) }}</td>
                                <td>{{ formatCurrency($product['total_cost']) }}</td>
                                <td>{{ formatCurrency($product['total_profit']) }}</td>
                                <td>
                                    <span class="badge bg-{{ $product['profit_margin'] > 30 ? 'success' : ($product['profit_margin'] > 15 ? 'warning' : 'danger') }}">
                                        {{ number_format($product['profit_margin'], 1) }}%
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0">{{ __('Profit Distribution') }}</h6>
            </div>
            <div class="card-body">
                <canvas id="profitDistributionChart" height="250"></canvas>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Profit by Customer -->
@if(isset($analytics['profit_by_customer']))
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0">{{ __('Most Profitable Customers') }}</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>{{ __('Customer') }}</th>
                                <th>{{ __('Total Revenue') }}</th>
                                <th>{{ __('Total Cost') }}</th>
                                <th>{{ __('Total Profit') }}</th>
                                <th>{{ __('Profit Margin %') }}</th>
                                <th>{{ __('Orders') }}</th>
                                <th>{{ __('Avg Profit/Order') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(array_slice($analytics['profit_by_customer'], 0, 15) as $customer)
                            <tr>
                                <td>{{ $customer['customer_name'] }}</td>
                                <td>{{ formatCurrency($customer['total_revenue']) }}</td>
                                <td>{{ formatCurrency($customer['total_cost']) }}</td>
                                <td>{{ formatCurrency($customer['total_profit']) }}</td>
                                <td>
                                    <span class="badge bg-{{ $customer['profit_margin'] > 25 ? 'success' : ($customer['profit_margin'] > 10 ? 'warning' : 'danger') }}">
                                        {{ number_format($customer['profit_margin'], 1) }}%
                                    </span>
                                </td>
                                <td>{{ $customer['order_count'] }}</td>
                                <td>{{ formatCurrency($customer['avg_profit_per_order']) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Cost Analysis -->
@if(isset($analytics['cost_analysis']))
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0">{{ __('Cost Breakdown') }}</h6>
            </div>
            <div class="card-body">
                <canvas id="costAnalysisChart" height="200"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0">{{ __('Cost Categories') }}</h6>
            </div>
            <div class="card-body">
                @foreach($analytics['cost_analysis'] as $category => $amount)
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-sm">{{ __(ucwords(str_replace('_', ' ', $category))) }}</span>
                    <div>
                        <span class="badge bg-info">{{ formatCurrency($amount) }}</span>
                        <small class="text-muted">
                            ({{ number_format(($amount / array_sum($analytics['cost_analysis'])) * 100, 1) }}%)
                        </small>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif

<!-- Break-Even Analysis -->
@if(isset($analytics['break_even_analysis']))
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0">{{ __('Break-Even Analysis') }}</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    @foreach($analytics['break_even_analysis'] as $metric => $value)
                    <div class="col-md-3 mb-3">
                        <div class="h4 mb-0">
                            @if(str_contains($metric, 'amount') || str_contains($metric, 'revenue') || str_contains($metric, 'cost'))
                                {{ formatCurrency($value) }}
                            @elseif(str_contains($metric, 'percentage') || str_contains($metric, 'margin'))
                                {{ number_format($value, 1) }}%
                            @else
                                {{ is_numeric($value) ? number_format($value) : $value }}
                            @endif
                        </div>
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
    $profitLabels = isset($analytics['profit_by_product']) ? collect($analytics['profit_by_product'])->take(8)->pluck('product_name') : [];
    $profitData = isset($analytics['profit_by_product']) ? collect($analytics['profit_by_product'])->take(8)->pluck('total_profit') : [];
    $costLabels = isset($analytics['cost_analysis']) ? collect($analytics['cost_analysis'])->pluck('cost_type') : [];
    $costData = isset($analytics['cost_analysis']) ? collect($analytics['cost_analysis'])->pluck('total_cost') : [];
@endphp
<script id="profit-labels" type="application/json">{!! json_encode($profitLabels) !!}</script>
<script id="profit-data" type="application/json">{!! json_encode($profitData) !!}</script>
<script id="cost-labels" type="application/json">{!! json_encode($costLabels) !!}</script>
<script id="cost-data" type="application/json">{!! json_encode($costData) !!}</script>
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    const profitLabels = JSON.parse(document.getElementById('profit-labels').textContent);
    const profitData = JSON.parse(document.getElementById('profit-data').textContent);
    const costLabels = JSON.parse(document.getElementById('cost-labels').textContent);
    const costData = JSON.parse(document.getElementById('cost-data').textContent);
    // Profit Distribution Chart
    if (profitLabels.length > 0) {
        const profitCtx = document.getElementById('profitDistributionChart').getContext('2d');
        new Chart(profitCtx, {
            type: 'doughnut',
            data: {
                labels: profitLabels,
                datasets: [{
                    data: profitData,
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#FF6384', '#C9CBCF'
                    ]
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    }
    // Cost Analysis Chart
    if (costLabels.length > 0) {
        const costCtx = document.getElementById('costAnalysisChart').getContext('2d');
        new Chart(costCtx, {
            type: 'bar',
            data: {
                labels: costLabels,
                datasets: [{
                    label: '{{ __("Total Cost") }}',
                    data: costData,
                    backgroundColor: 'rgba(255, 99, 132, 0.5)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    }
});
</script>
@endpush
