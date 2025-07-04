@extends('tenant.layouts.app')

@section('title', __('Analytics Dashboard'))
@section('page-title', __('Business Analytics'))

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">{{ __('app.dashboard') }}</a></li>
<li class="breadcrumb-item"><a href="{{ route('reports.dashboard') }}">{{ __('Reports') }}</a></li>
<li class="breadcrumb-item active">{{ __('Analytics') }}</li>
@endsection

@section('content')
<!-- Analytics Navigation -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2 mb-2">
                        <a href="{{ route('analytics.sales') }}" class="btn btn-primary w-100">
                            <i class="fas fa-chart-line {{ marginEnd('2') }}"></i>{{ __('Sales Analytics') }}
                        </a>
                    </div>
                    <div class="col-md-2 mb-2">
                        <a href="{{ route('analytics.customers') }}" class="btn btn-success w-100">
                            <i class="fas fa-users {{ marginEnd('2') }}"></i>{{ __('Customer Analytics') }}
                        </a>
                    </div>
                    <div class="col-md-2 mb-2">
                        <a href="{{ route('analytics.products') }}" class="btn btn-info w-100">
                            <i class="fas fa-box {{ marginEnd('2') }}"></i>{{ __('Product Analytics') }}
                        </a>
                    </div>
                    <div class="col-md-2 mb-2">
                        <a href="{{ route('analytics.profitability') }}" class="btn btn-warning w-100">
                            <i class="fas fa-dollar-sign {{ marginEnd('2') }}"></i>{{ __('Profitability') }}
                        </a>
                    </div>
                    <div class="col-md-2 mb-2">
                        <a href="{{ route('reports.dashboard') }}" class="btn btn-secondary w-100">
                            <i class="fas fa-file-alt {{ marginEnd('2') }}"></i>{{ __('Reports') }}
                        </a>
                    </div>
                    <div class="col-md-2 mb-2">
                        <a href="{{ route('ai.dashboard') }}" class="btn btn-dark w-100">
                            <i class="fas fa-robot {{ marginEnd('2') }}"></i>{{ __('AI Analytics') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Sales Analytics Overview -->
@if(isset($analytics['sales_analytics']))
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0">{{ __('Sales Performance') }}</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="card stats-card-success">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col {{ marginEnd('2') }}">
                                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                                            {{ __('Today Sales') }}
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold">
                                            {{ formatCurrency($analytics['sales_analytics']['today_sales'] ?? 0) }}
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
                                            {{ __('Month Sales') }}
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold">
                                            {{ formatCurrency($analytics['sales_analytics']['month_sales'] ?? 0) }}
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
                        <div class="card stats-card-warning">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col {{ marginEnd('2') }}">
                                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                                            {{ __('Growth Rate') }}
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold">
                                            {{ number_format($analytics['sales_analytics']['growth_rate'] ?? 0, 1) }}%
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-trending-up fa-2x opacity-75"></i>
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
                                            {{ __('Yesterday Sales') }}
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold">
                                            {{ formatCurrency($analytics['sales_analytics']['yesterday_sales'] ?? 0) }}
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
            </div>
        </div>
    </div>
</div>
@endif

<!-- Customer Analytics Overview -->
@if(isset($analytics['customer_analytics']))
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0">{{ __('Customer Insights') }}</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <div class="border-end">
                            <div class="h4 mb-0">{{ count($analytics['customer_analytics']) }}</div>
                            <small class="text-muted">{{ __('Customer Segments') }}</small>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div>
                            <div class="h4 mb-0">{{ array_sum($analytics['customer_analytics']) }}</div>
                            <small class="text-muted">{{ __('Total Customers') }}</small>
                        </div>
                    </div>
                </div>
                <div class="d-grid gap-2">
                    <a href="{{ route('analytics.customers') }}" class="btn btn-sm btn-outline-success">
                        <i class="fas fa-users {{ marginEnd('2') }}"></i>{{ __('View Customer Analytics') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0">{{ __('Product Performance') }}</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <div class="border-end">
                            <div class="h4 mb-0">{{ isset($analytics['product_analytics']) ? count($analytics['product_analytics']) : 0 }}</div>
                            <small class="text-muted">{{ __('Product Categories') }}</small>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div>
                            <div class="h4 mb-0">{{ isset($analytics['product_analytics']) ? array_sum(array_column($analytics['product_analytics'], 'total_sold')) : 0 }}</div>
                            <small class="text-muted">{{ __('Units Sold') }}</small>
                        </div>
                    </div>
                </div>
                <div class="d-grid gap-2">
                    <a href="{{ route('analytics.products') }}" class="btn btn-sm btn-outline-info">
                        <i class="fas fa-box {{ marginEnd('2') }}"></i>{{ __('View Product Analytics') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Financial Analytics Overview -->
@if(isset($analytics['financial_analytics']))
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0">{{ __('Financial Performance') }}</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <div class="text-center">
                            <div class="h4 mb-0 text-success">
                                {{ formatCurrency($analytics['financial_analytics']['total_revenue'] ?? 0) }}
                            </div>
                            <small class="text-muted">{{ __('Total Revenue') }}</small>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="text-center">
                            <div class="h4 mb-0 text-info">
                                {{ formatCurrency($analytics['financial_analytics']['total_profit'] ?? 0) }}
                            </div>
                            <small class="text-muted">{{ __('Total Profit') }}</small>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="text-center">
                            <div class="h4 mb-0 text-warning">
                                {{ number_format($analytics['financial_analytics']['profit_margin'] ?? 0, 1) }}%
                            </div>
                            <small class="text-muted">{{ __('Profit Margin') }}</small>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="text-center">
                            <div class="h4 mb-0 text-primary">
                                {{ formatCurrency($analytics['financial_analytics']['avg_order_value'] ?? 0) }}
                            </div>
                            <small class="text-muted">{{ __('Avg Order Value') }}</small>
                        </div>
                    </div>
                </div>
                <div class="d-grid gap-2">
                    <a href="{{ route('analytics.profitability') }}" class="btn btn-sm btn-outline-warning">
                        <i class="fas fa-dollar-sign {{ marginEnd('2') }}"></i>{{ __('View Profitability Analysis') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Quick Actions -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0">{{ __('Quick Analytics Actions') }}</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <a href="{{ route('analytics.sales') }}" class="btn btn-outline-primary w-100">
                            <i class="fas fa-chart-bar {{ marginEnd('2') }}"></i>{{ __('Sales Trends') }}
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="{{ route('analytics.customers') }}" class="btn btn-outline-success w-100">
                            <i class="fas fa-user-chart {{ marginEnd('2') }}"></i>{{ __('Customer Segmentation') }}
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="{{ route('analytics.products') }}" class="btn btn-outline-info w-100">
                            <i class="fas fa-boxes {{ marginEnd('2') }}"></i>{{ __('Product Performance') }}
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="{{ route('analytics.profitability') }}" class="btn btn-outline-warning w-100">
                            <i class="fas fa-chart-pie {{ marginEnd('2') }}"></i>{{ __('Profit Analysis') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize any charts or interactive elements here
    console.log('Analytics Dashboard loaded');
});
</script>
@endpush
