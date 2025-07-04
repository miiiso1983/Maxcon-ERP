@extends('tenant.layouts.app')

@section('title', __('Customer Analytics'))
@section('page-title', __('Customer Behavior Analytics'))

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">{{ __('app.dashboard') }}</a></li>
<li class="breadcrumb-item"><a href="{{ route('reports.dashboard') }}">{{ __('Reports') }}</a></li>
<li class="breadcrumb-item"><a href="{{ route('analytics.dashboard') }}">{{ __('Analytics') }}</a></li>
<li class="breadcrumb-item active">{{ __('Customer Analytics') }}</li>
@endsection

@section('content')
<!-- Period Selection -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('analytics.customers') }}">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label for="period" class="form-label">{{ __('Analysis Period') }}</label>
                            <select name="period" id="period" class="form-select">
                                <option value="30" {{ request('period') == '30' ? 'selected' : '' }}>{{ __('Last 30 Days') }}</option>
                                <option value="90" {{ request('period', '90') == '90' ? 'selected' : '' }}>{{ __('Last 90 Days') }}</option>
                                <option value="180" {{ request('period') == '180' ? 'selected' : '' }}>{{ __('Last 6 Months') }}</option>
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

<!-- Customer Segmentation -->
@if(isset($analytics['customer_segmentation']))
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0">{{ __('Customer Segmentation (RFM Analysis)') }}</h6>
            </div>
            <div class="card-body">
                <canvas id="customerSegmentationChart" height="150"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0">{{ __('Segment Distribution') }}</h6>
            </div>
            <div class="card-body">
                @foreach($analytics['customer_segmentation'] as $segment => $count)
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-sm">{{ __(ucwords(str_replace('_', ' ', $segment))) }}</span>
                    <span class="badge bg-primary">{{ $count }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif

<!-- Customer Lifetime Value -->
@if(isset($analytics['customer_lifetime_value']))
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0">{{ __('Top Customer Lifetime Value') }}</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>{{ __('Customer') }}</th>
                                <th>{{ __('Lifetime Value') }}</th>
                                <th>{{ __('Lifetime Days') }}</th>
                                <th>{{ __('Daily Average') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(array_slice($analytics['customer_lifetime_value'], 0, 10) as $customer)
                            <tr>
                                <td>{{ $customer['customer_name'] }}</td>
                                <td>{{ formatCurrency($customer['lifetime_value']) }}</td>
                                <td>{{ $customer['lifetime_days'] }} {{ __('days') }}</td>
                                <td>{{ formatCurrency($customer['avg_daily_value']) }}</td>
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

<!-- Customer Acquisition & Retention -->
<div class="row mb-4">
    @if(isset($analytics['customer_acquisition']))
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0">{{ __('Customer Acquisition') }}</h6>
            </div>
            <div class="card-body">
                <canvas id="customerAcquisitionChart" height="200"></canvas>
            </div>
        </div>
    </div>
    @endif

    @if(isset($analytics['customer_retention']))
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0">{{ __('Customer Retention Metrics') }}</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    @foreach($analytics['customer_retention'] as $metric => $value)
                    <div class="col-6 mb-3">
                        <div class="h4 mb-0">{{ is_numeric($value) ? number_format($value, 1) : $value }}{{ is_numeric($value) ? '%' : '' }}</div>
                        <small class="text-muted">{{ __(ucwords(str_replace('_', ' ', $metric))) }}</small>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Purchase Frequency -->
@if(isset($analytics['purchase_frequency']))
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0">{{ __('Purchase Frequency Distribution') }}</h6>
            </div>
            <div class="card-body">
                <canvas id="purchaseFrequencyChart" height="150"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0">{{ __('Frequency Stats') }}</h6>
            </div>
            <div class="card-body">
                @foreach($analytics['purchase_frequency'] as $frequency => $count)
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-sm">{{ $frequency }} {{ __('purchases') }}</span>
                    <span class="badge bg-info">{{ $count }} {{ __('customers') }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif

<!-- Geographic Distribution -->
@if(isset($analytics['geographic_distribution']))
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0">{{ __('Geographic Distribution') }}</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>{{ __('City') }}</th>
                                <th>{{ __('Customer Count') }}</th>
                                <th>{{ __('Total Sales') }}</th>
                                <th>{{ __('Avg per Customer') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($analytics['geographic_distribution'] as $location)
                            <tr>
                                <td>{{ $location['city'] }}</td>
                                <td>{{ $location['customer_count'] }}</td>
                                <td>{{ formatCurrency($location['total_sales']) }}</td>
                                <td>{{ formatCurrency($location['total_sales'] / $location['customer_count']) }}</td>
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
    $customerSegmentationLabels = isset($analytics['customer_segmentation']) ? array_keys($analytics['customer_segmentation']) : [];
    $customerSegmentationData = isset($analytics['customer_segmentation']) ? array_values($analytics['customer_segmentation']) : [];
    $customerAcquisitionLabels = isset($analytics['customer_acquisition']) ? collect($analytics['customer_acquisition'])->pluck('date') : [];
    $customerAcquisitionData = isset($analytics['customer_acquisition']) ? collect($analytics['customer_acquisition'])->pluck('new_customers') : [];
    $purchaseFrequencyLabels = isset($analytics['purchase_frequency']) ? array_keys($analytics['purchase_frequency']) : [];
    $purchaseFrequencyData = isset($analytics['purchase_frequency']) ? array_values($analytics['purchase_frequency']) : [];
@endphp
<script id="customer-segmentation-labels" type="application/json">{!! json_encode($customerSegmentationLabels) !!}</script>
<script id="customer-segmentation-data" type="application/json">{!! json_encode($customerSegmentationData) !!}</script>
<script id="customer-acquisition-labels" type="application/json">{!! json_encode($customerAcquisitionLabels) !!}</script>
<script id="customer-acquisition-data" type="application/json">{!! json_encode($customerAcquisitionData) !!}</script>
<script id="purchase-frequency-labels" type="application/json">{!! json_encode($purchaseFrequencyLabels) !!}</script>
<script id="purchase-frequency-data" type="application/json">{!! json_encode($purchaseFrequencyData) !!}</script>
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    const customerSegmentationLabels = JSON.parse(document.getElementById('customer-segmentation-labels').textContent);
    const customerSegmentationData = JSON.parse(document.getElementById('customer-segmentation-data').textContent);
    const customerAcquisitionLabels = JSON.parse(document.getElementById('customer-acquisition-labels').textContent);
    const customerAcquisitionData = JSON.parse(document.getElementById('customer-acquisition-data').textContent);
    const purchaseFrequencyLabels = JSON.parse(document.getElementById('purchase-frequency-labels').textContent);
    const purchaseFrequencyData = JSON.parse(document.getElementById('purchase-frequency-data').textContent);

    // Customer Segmentation Chart
    if (customerSegmentationLabels.length > 0) {
        const segmentationCtx = document.getElementById('customerSegmentationChart').getContext('2d');
        new Chart(segmentationCtx, {
            type: 'doughnut',
            data: {
                labels: customerSegmentationLabels,
                datasets: [{
                    data: customerSegmentationData,
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'
                    ]
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    }
    // Customer Acquisition Chart
    if (customerAcquisitionLabels.length > 0) {
        const acquisitionCtx = document.getElementById('customerAcquisitionChart').getContext('2d');
        new Chart(acquisitionCtx, {
            type: 'line',
            data: {
                labels: customerAcquisitionLabels,
                datasets: [{
                    label: '{{ __("New Customers") }}',
                    data: customerAcquisitionData,
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    }
    // Purchase Frequency Chart
    if (purchaseFrequencyLabels.length > 0) {
        const frequencyCtx = document.getElementById('purchaseFrequencyChart').getContext('2d');
        new Chart(frequencyCtx, {
            type: 'bar',
            data: {
                labels: purchaseFrequencyLabels,
                datasets: [{
                    label: '{{ __("Number of Customers") }}',
                    data: purchaseFrequencyData,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    }
});
</script>
@endpush
