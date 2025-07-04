@extends('tenant.layouts.app')

@section('title', __('Product Analytics'))
@section('page-title', __('Product Performance Analytics'))

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">{{ __('app.dashboard') }}</a></li>
<li class="breadcrumb-item"><a href="{{ route('reports.dashboard') }}">{{ __('Reports') }}</a></li>
<li class="breadcrumb-item"><a href="{{ route('analytics.dashboard') }}">{{ __('Analytics') }}</a></li>
<li class="breadcrumb-item active">{{ __('Product Analytics') }}</li>
@endsection

@section('content')
<!-- Period Selection -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('analytics.products') }}">
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

<!-- Product Performance Overview -->
@if(isset($analytics['product_performance']))
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0">{{ __('Top Performing Products') }}</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>{{ __('Product') }}</th>
                                <th>{{ __('SKU') }}</th>
                                <th>{{ __('Units Sold') }}</th>
                                <th>{{ __('Revenue') }}</th>
                                <th>{{ __('Profit') }}</th>
                                <th>{{ __('Margin %') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(array_slice($analytics['product_performance'], 0, 15) as $product)
                            <tr>
                                <td>{{ $product['product_name'] }}</td>
                                <td>{{ $product['product_sku'] }}</td>
                                <td>{{ number_format($product['total_quantity']) }}</td>
                                <td>{{ formatCurrency($product['total_revenue']) }}</td>
                                <td>{{ formatCurrency($product['total_profit']) }}</td>
                                <td>
                                    <span class="badge bg-{{ $product['profit_margin'] > 20 ? 'success' : ($product['profit_margin'] > 10 ? 'warning' : 'danger') }}">
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
</div>
@endif

<!-- Category Analysis -->
@if(isset($analytics['category_analysis']))
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0">{{ __('Sales by Category') }}</h6>
            </div>
            <div class="card-body">
                <canvas id="categoryAnalysisChart" height="150"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0">{{ __('Category Performance') }}</h6>
            </div>
            <div class="card-body">
                @foreach(array_slice($analytics['category_analysis'], 0, 8) as $category)
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-sm">{{ $category['category_name'] }}</span>
                    <span class="badge bg-primary">{{ formatCurrency($category['total_revenue']) }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif

<!-- Inventory Turnover -->
@if(isset($analytics['inventory_turnover']))
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0">{{ __('Inventory Turnover Analysis') }}</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>{{ __('Product') }}</th>
                                <th>{{ __('Current Stock') }}</th>
                                <th>{{ __('Units Sold') }}</th>
                                <th>{{ __('Turnover Ratio') }}</th>
                                <th>{{ __('Days to Sell') }}</th>
                                <th>{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(array_slice($analytics['inventory_turnover'], 0, 15) as $item)
                            <tr>
                                <td>{{ $item['product_name'] }}</td>
                                <td>{{ number_format($item['current_stock']) }}</td>
                                <td>{{ number_format($item['units_sold']) }}</td>
                                <td>{{ number_format($item['turnover_ratio'], 2) }}</td>
                                <td>{{ number_format($item['days_to_sell']) }}</td>
                                <td>
                                    <span class="badge bg-{{ $item['turnover_ratio'] > 4 ? 'success' : ($item['turnover_ratio'] > 2 ? 'warning' : 'danger') }}">
                                        {{ $item['turnover_ratio'] > 4 ? __('Fast Moving') : ($item['turnover_ratio'] > 2 ? __('Normal') : __('Slow Moving')) }}
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
</div>
@endif

<!-- Price Elasticity & Cross-Selling -->
<div class="row mb-4">
    @if(isset($analytics['price_elasticity']))
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0">{{ __('Price Elasticity') }}</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>{{ __('Product') }}</th>
                                <th>{{ __('Elasticity') }}</th>
                                <th>{{ __('Type') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(array_slice($analytics['price_elasticity'], 0, 8) as $item)
                            <tr>
                                <td>{{ Str::limit($item['product_name'], 20) }}</td>
                                <td>{{ number_format($item['elasticity'], 2) }}</td>
                                <td>
                                    <span class="badge bg-{{ abs($item['elasticity']) > 1 ? 'success' : 'warning' }}">
                                        {{ abs($item['elasticity']) > 1 ? __('Elastic') : __('Inelastic') }}
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
    @endif

    @if(isset($analytics['cross_selling']))
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0">{{ __('Cross-Selling Opportunities') }}</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>{{ __('Product A') }}</th>
                                <th>{{ __('Product B') }}</th>
                                <th>{{ __('Frequency') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(array_slice($analytics['cross_selling'], 0, 8) as $pair)
                            <tr>
                                <td>{{ Str::limit($pair['product_a'], 15) }}</td>
                                <td>{{ Str::limit($pair['product_b'], 15) }}</td>
                                <td>
                                    <span class="badge bg-info">{{ $pair['frequency'] }}</span>
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
@endif

<!-- Stock Optimization -->
@if(isset($analytics['stock_optimization']))
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0">{{ __('Stock Optimization Recommendations') }}</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach(['overstock', 'understock', 'optimal'] as $category)
                    @if(isset($analytics['stock_optimization'][$category]))
                    <div class="col-md-4">
                        <div class="card border-{{ $category === 'overstock' ? 'danger' : ($category === 'understock' ? 'warning' : 'success') }}">
                            <div class="card-header bg-{{ $category === 'overstock' ? 'danger' : ($category === 'understock' ? 'warning' : 'success') }} text-white">
                                <h6 class="m-0">{{ __(ucfirst($category)) }} ({{ count($analytics['stock_optimization'][$category]) }})</h6>
                            </div>
                            <div class="card-body">
                                @foreach(array_slice($analytics['stock_optimization'][$category], 0, 5) as $item)
                                <div class="mb-2">
                                    <small class="text-muted">{{ Str::limit($item['product_name'], 25) }}</small><br>
                                    <span class="text-sm">{{ __('Stock') }}: {{ number_format($item['current_stock']) }}</span>
                                </div>
                                @endforeach
                                @if(count($analytics['stock_optimization'][$category]) > 5)
                                <small class="text-muted">{{ __('and :count more...', ['count' => count($analytics['stock_optimization'][$category]) - 5]) }}</small>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif
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
    // Category Analysis Chart
    @if(isset($analytics['category_analysis']))
    const categoryCtx = document.getElementById('categoryAnalysisChart').getContext('2d');
    new Chart(categoryCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode(collect($analytics['category_analysis'])->pluck('category_name')) !!},
            datasets: [{
                data: {!! json_encode(collect($analytics['category_analysis'])->pluck('total_revenue')) !!},
                backgroundColor: [
                    '#FF6384',
                    '#36A2EB',
                    '#FFCE56',
                    '#4BC0C0',
                    '#9966FF',
                    '#FF9F40',
                    '#FF6384',
                    '#C9CBCF'
                ]
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
