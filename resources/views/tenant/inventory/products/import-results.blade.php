@extends('tenant.layouts.app')

@section('title', __('Import Results'))
@section('page-title', __('Import Results'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">{{ __('app.dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('inventory.index') }}">{{ __('app.inventory') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('inventory.products.index') }}">{{ __('Products') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Import Results') }}</li>
@endsection

@push('styles')
<style>
    .chart-container {
        position: relative;
        height: 300px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h3 class="mb-0">{{ $results['imported'] ?? 0 }}</h3>
                                    <p class="mb-0">{{ __('Imported') }}</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-check-circle fa-2x opacity-75"></i>
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
                                    <h3 class="mb-0">{{ $results['skipped'] ?? 0 }}</h3>
                                    <p class="mb-0">{{ __('Skipped') }}</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h3 class="mb-0">{{ $results['errors'] ?? 0 }}</h3>
                                    <p class="mb-0">{{ __('Errors') }}</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-times-circle fa-2x opacity-75"></i>
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
                                    <h3 class="mb-0">{{ ($results['imported'] ?? 0) + ($results['skipped'] ?? 0) + ($results['errors'] ?? 0) }}</h3>
                                    <p class="mb-0">{{ __('Total Rows') }}</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-list fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Import Summary -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-pie {{ marginEnd('2') }}"></i>
                        {{ __('Import Summary') }}
                    </h5>
                </div>
                <div class="card-body">
                    @if(($results['imported'] ?? 0) > 0)
                        <div class="alert alert-success">
                            <h6 class="alert-heading">
                                <i class="fas fa-check-circle {{ marginEnd('2') }}"></i>
                                {{ __('Import Completed Successfully!') }}
                            </h6>
                            <p class="mb-0">
                                {{ __('Successfully imported :count products.', ['count' => $results['imported']]) }}
                                @if(($results['skipped'] ?? 0) > 0)
                                    {{ __(':count products were skipped due to duplicates.', ['count' => $results['skipped']]) }}
                                @endif
                            </p>
                        </div>
                    @endif

                    @if(($results['errors'] ?? 0) > 0)
                        <div class="alert alert-warning">
                            <h6 class="alert-heading">
                                <i class="fas fa-exclamation-triangle {{ marginEnd('2') }}"></i>
                                {{ __('Some Issues Found') }}
                            </h6>
                            <p class="mb-0">
                                {{ __(':count rows had errors and were not imported. Please check the error details below.', ['count' => $results['errors']]) }}
                            </p>
                        </div>
                    @endif

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="chart-container">
                                <canvas id="importChart" aria-label="{{ __('Import Results Chart') }}"></canvas>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6>{{ __('Import Statistics') }}</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success {{ marginEnd('2') }}" aria-hidden="true"></i>{{ __('Successful imports: :count', ['count' => $results['imported'] ?? 0]) }}</li>
                                <li><i class="fas fa-skip-forward text-warning {{ marginEnd('2') }}" aria-hidden="true"></i>{{ __('Skipped duplicates: :count', ['count' => $results['skipped'] ?? 0]) }}</li>
                                <li><i class="fas fa-times text-danger {{ marginEnd('2') }}" aria-hidden="true"></i>{{ __('Failed imports: :count', ['count' => $results['errors'] ?? 0]) }}</li>
                            </ul>
                            
                            @php
                                $total = ($results['imported'] ?? 0) + ($results['skipped'] ?? 0) + ($results['errors'] ?? 0);
                                $successRate = $total > 0 ? round((($results['imported'] ?? 0) / $total) * 100, 1) : 0;
                            @endphp
                            
                            <div class="mt-3">
                                <h6>{{ __('Success Rate') }}</h6>
                                <div class="progress">
                                    <div class="progress-bar bg-success" role="progressbar" data-success-rate="{{ $successRate }}" aria-valuenow="{{ $successRate }}" aria-valuemin="0" aria-valuemax="100">
                                        {{ $successRate }}%
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Error Details -->
            @if(!empty($results['error_details']))
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-exclamation-circle {{ marginEnd('2') }}"></i>
                        {{ __('Error Details') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{ __('Row') }}</th>
                                    <th>{{ __('Error') }}</th>
                                    <th>{{ __('Data') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($results['error_details'] as $error)
                                <tr>
                                    <td>
                                        <span class="badge bg-danger">{{ e($error['row'] ?? '') }}</span>
                                    </td>
                                    <td>
                                        <span class="text-danger">{{ e($error['error'] ?? '') }}</span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            @if(is_array($error['data'] ?? []))
                                                @foreach($error['data'] as $key => $value)
                                                    @if(!empty($value))
                                                        <strong>{{ e($key) }}:</strong> {{ e($value) }}<br>
                                                    @endif
                                                @endforeach
                                            @else
                                                {{ e($error['data'] ?? '') }}
                                            @endif
                                        </small>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <!-- Actions -->
            <div class="card mt-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <a href="{{ route('inventory.products.import') }}" class="btn btn-outline-primary">
                                <i class="fas fa-upload {{ marginEnd('2') }}"></i>{{ __('Import More Products') }}
                            </a>
                        </div>
                        <div>
                            <a href="{{ route('inventory.products.index') }}" class="btn btn-primary">
                                <i class="fas fa-list {{ marginEnd('2') }}"></i>{{ __('View Products') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Chart Data -->
<script id="chart-data" type="application/json">
{!! json_encode([
    'imported' => $results['imported'] ?? 0,
    'skipped' => $results['skipped'] ?? 0,
    'errors' => $results['errors'] ?? 0,
    'labels' => [
        'imported' => __('Imported'),
        'skipped' => __('Skipped'),
        'errors' => __('Errors')
    ],
    'title' => __('Import Results')
]) !!}
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get chart data from JSON script
    const chartDataElement = document.getElementById('chart-data');
    const chartData = JSON.parse(chartDataElement.textContent);

    // Set progress bar width
    const progressBar = document.querySelector('.progress-bar[data-success-rate]');
    if (progressBar) {
        const successRate = progressBar.getAttribute('data-success-rate');
        progressBar.style.width = successRate + '%';
    }

    // Create pie chart for import results
    const ctx = document.getElementById('importChart');
    if (ctx) {
        const importChart = new Chart(ctx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: [
                    chartData.labels.imported,
                    chartData.labels.skipped,
                    chartData.labels.errors
                ],
                datasets: [{
                    data: [
                        chartData.imported,
                        chartData.skipped,
                        chartData.errors
                    ],
                    backgroundColor: [
                        '#28a745',
                        '#ffc107',
                        '#dc3545'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    title: {
                        display: true,
                        text: chartData.title
                    }
                }
            }
        });
    }
});
</script>
@endpush
