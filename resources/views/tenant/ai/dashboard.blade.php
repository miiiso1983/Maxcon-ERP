@extends('tenant.layouts.app')

@section('title', __('AI & Prediction Tools'))
@section('page-title', __('AI & Prediction Tools'))

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">{{ __('app.dashboard') }}</a></li>
<li class="breadcrumb-item active">{{ __('AI Tools') }}</li>
@endsection

@section('content')
<!-- AI Insights Overview -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stats-card-primary">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col {{ marginEnd('2') }}">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            {{ __('Demand Forecasts') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ $insights['demand_forecasts'] }}</div>
                        <small class="text-muted">{{ __('This week') }}</small>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-chart-line fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stats-card-success">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col {{ marginEnd('2') }}">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            {{ __('Price Optimizations') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ $insights['price_optimizations'] }}</div>
                        <small class="text-muted">{{ __('This week') }}</small>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-dollar-sign fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stats-card-danger">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col {{ marginEnd('2') }}">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            {{ __('Churn Risks') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ $insights['churn_risks'] }}</div>
                        <small class="text-muted">{{ __('High risk customers') }}</small>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
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
                            {{ __('Stock Alerts') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ $insights['low_stock_alerts'] }}</div>
                        <small class="text-muted">{{ __('Low stock items') }}</small>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-boxes fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- AI Tools Quick Access -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0">{{ __('AI-Powered Tools') }}</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <div class="card h-100 border-primary">
                            <div class="card-body text-center">
                                <i class="fas fa-chart-line fa-3x text-primary mb-3"></i>
                                <h6>{{ __('Demand Forecasting') }}</h6>
                                <p class="text-muted small">{{ __('Predict future product demand using AI') }}</p>
                                <a href="{{ route('ai.demand-forecasting') }}" class="btn btn-primary btn-sm">
                                    {{ __('Start Forecasting') }}
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card h-100 border-success">
                            <div class="card-body text-center">
                                <i class="fas fa-dollar-sign fa-3x text-success mb-3"></i>
                                <h6>{{ __('Price Optimization') }}</h6>
                                <p class="text-muted small">{{ __('Optimize pricing for maximum revenue') }}</p>
                                <a href="{{ route('ai.price-optimization') }}" class="btn btn-success btn-sm">
                                    {{ __('Optimize Prices') }}
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card h-100 border-info">
                            <div class="card-body text-center">
                                <i class="fas fa-users fa-3x text-info mb-3"></i>
                                <h6>{{ __('Customer Analytics') }}</h6>
                                <p class="text-muted small">{{ __('Analyze customer behavior and predict churn') }}</p>
                                <a href="{{ route('ai.customer-analytics') }}" class="btn btn-info btn-sm">
                                    {{ __('Analyze Customers') }}
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card h-100 border-warning">
                            <div class="card-body text-center">
                                <i class="fas fa-cogs fa-3x text-warning mb-3"></i>
                                <h6>{{ __('Batch Analysis') }}</h6>
                                <p class="text-muted small">{{ __('Run AI analysis on multiple items') }}</p>
                                <button class="btn btn-warning btn-sm" onclick="showBatchAnalysisModal()">
                                    {{ __('Batch Process') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Predictions & Statistics -->
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="m-0">{{ __('Recent AI Predictions') }}</h6>
                <span class="badge bg-info">{{ $insights['total_predictions'] }} {{ __('this week') }}</span>
            </div>
            <div class="card-body p-0">
                @if($recentPredictions->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Target') }}</th>
                                <th>{{ __('Confidence') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Date') }}</th>
                                <th width="80">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentPredictions as $prediction)
                            <tr>
                                <td>
                                    <span class="badge bg-{{ $prediction->type_color }}">
                                        {{ ucfirst(str_replace('_', ' ', $prediction->prediction_type)) }}
                                    </span>
                                </td>
                                <td>
                                    <div>
                                        @if($prediction->targetEntity)
                                        <strong>{{ $prediction->targetEntity->name ?? 'N/A' }}</strong>
                                        @else
                                        <em class="text-muted">{{ __('Entity not found') }}</em>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1 {{ marginEnd('2') }}" style="height: 6px;">
                                            @php
                                                $confidenceClass = $prediction->confidence_score >= 0.8 ? 'success' :
                                                                  ($prediction->confidence_score >= 0.6 ? 'warning' : 'danger');
                                                $confidenceWidth = $prediction->confidence_score * 100;
                                            @endphp
                                            <div class="progress-bar bg-{{ $confidenceClass }}" style="--width: {{ $confidenceWidth }}%; width: var(--width)"></div>
                                        </div>
                                        <small>{{ round($prediction->confidence_score * 100) }}%</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $prediction->status_color }}">
                                        {{ ucfirst($prediction->status) }}
                                    </span>
                                </td>
                                <td>
                                    <small>{{ $prediction->created_at->format('M d, Y') }}</small>
                                </td>
                                <td>
                                    <a href="{{ route('ai.prediction-details', $prediction) }}" 
                                       class="btn btn-outline-primary btn-sm" 
                                       title="{{ __('View Details') }}">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4">
                    <i class="fas fa-robot fa-2x text-muted mb-2"></i>
                    <p class="text-muted">{{ __('No AI predictions yet') }}</p>
                    <p class="text-muted small">{{ __('Start using AI tools to see predictions here') }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Accuracy Statistics -->
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="m-0">{{ __('Prediction Accuracy') }}</h6>
            </div>
            <div class="card-body">
                @if($stats['total_predictions'] > 0)
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small>{{ __('Excellent') }} (95%+)</small>
                        <small>{{ $stats['accuracy_stats']['excellent'] }}</small>
                    </div>
                    <div class="progress mb-2" style="height: 6px;">
                        @php
                            $excellentWidth = $stats['total_predictions'] > 0 ? ($stats['accuracy_stats']['excellent'] / $stats['total_predictions']) * 100 : 0;
                        @endphp
                        <div class="progress-bar bg-success" style="--width: {{ $excellentWidth }}%; width: var(--width)"></div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small>{{ __('Good') }} (85-94%)</small>
                        <small>{{ $stats['accuracy_stats']['good'] }}</small>
                    </div>
                    <div class="progress mb-2" style="height: 6px;">
                        @php
                            $goodWidth = $stats['total_predictions'] > 0 ? ($stats['accuracy_stats']['good'] / $stats['total_predictions']) * 100 : 0;
                        @endphp
                        <div class="progress-bar bg-info" style="--width: {{ $goodWidth }}%; width: var(--width)"></div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small>{{ __('Fair') }} (75-84%)</small>
                        <small>{{ $stats['accuracy_stats']['fair'] }}</small>
                    </div>
                    <div class="progress mb-2" style="height: 6px;">
                        @php
                            $fairWidth = $stats['total_predictions'] > 0 ? ($stats['accuracy_stats']['fair'] / $stats['total_predictions']) * 100 : 0;
                        @endphp
                        <div class="progress-bar bg-warning" style="--width: {{ $fairWidth }}%; width: var(--width)"></div>
                    </div>
                </div>

                <div>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small>{{ __('Poor') }} (<75%)</small>
                        <small>{{ $stats['accuracy_stats']['poor'] }}</small>
                    </div>
                    <div class="progress" style="height: 6px;">
                        @php
                            $poorWidth = $stats['total_predictions'] > 0 ? ($stats['accuracy_stats']['poor'] / $stats['total_predictions']) * 100 : 0;
                        @endphp
                        <div class="progress-bar bg-danger" style="--width: {{ $poorWidth }}%; width: var(--width)"></div>
                    </div>
                </div>
                @else
                <div class="text-center py-3">
                    <i class="fas fa-chart-bar fa-2x text-muted mb-2"></i>
                    <p class="text-muted small">{{ __('No accuracy data available yet') }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Confidence Distribution -->
        <div class="card">
            <div class="card-header">
                <h6 class="m-0">{{ __('Confidence Levels') }}</h6>
            </div>
            <div class="card-body">
                @if($stats['total_predictions'] > 0)
                <div class="row text-center">
                    <div class="col-4">
                        <div class="border-end">
                            <div class="h4 mb-0 text-success">{{ $stats['confidence_stats']['high'] }}</div>
                            <small class="text-muted">{{ __('High') }}</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="border-end">
                            <div class="h4 mb-0 text-warning">{{ $stats['confidence_stats']['medium'] }}</div>
                            <small class="text-muted">{{ __('Medium') }}</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div>
                            <div class="h4 mb-0 text-danger">{{ $stats['confidence_stats']['low'] }}</div>
                            <small class="text-muted">{{ __('Low') }}</small>
                        </div>
                    </div>
                </div>
                @else
                <div class="text-center py-3">
                    <i class="fas fa-tachometer-alt fa-2x text-muted mb-2"></i>
                    <p class="text-muted small">{{ __('No confidence data available yet') }}</p>
                </div>
                @endif
            </div>
            <div class="card-footer">
                <a href="{{ route('ai.settings') }}" class="btn btn-sm btn-outline-secondary w-100">
                    <i class="fas fa-cog {{ marginEnd('2') }}"></i>{{ __('AI Settings') }}
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Batch Analysis Modal -->
<div class="modal fade" id="batchAnalysisModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Batch AI Analysis') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="batchAnalysisForm">
                    <div class="mb-3">
                        <label class="form-label">{{ __('Analysis Type') }}</label>
                        <select class="form-select" name="analysis_type" required>
                            <option value="">{{ __('Select analysis type') }}</option>
                            <option value="demand_forecast">{{ __('Demand Forecasting') }}</option>
                            <option value="price_optimization">{{ __('Price Optimization') }}</option>
                            <option value="customer_behavior">{{ __('Customer Behavior Analysis') }}</option>
                            <option value="churn_prediction">{{ __('Churn Risk Prediction') }}</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">{{ __('Entity IDs') }}</label>
                        <textarea class="form-control" name="entity_ids" rows="3" 
                                  placeholder="{{ __('Enter comma-separated IDs (e.g., 1,2,3,4,5)') }}" required></textarea>
                        <div class="form-text">{{ __('Maximum 50 entities per batch') }}</div>
                    </div>

                    <div id="additionalParameters" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label">{{ __('Forecast Days') }}</label>
                            <input type="number" class="form-control" name="forecast_days" value="30" min="1" max="365">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">{{ __('Optimization Objective') }}</label>
                            <select class="form-select" name="objective">
                                <option value="revenue">{{ __('Revenue') }}</option>
                                <option value="profit">{{ __('Profit') }}</option>
                                <option value="volume">{{ __('Volume') }}</option>
                                <option value="margin">{{ __('Margin') }}</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-primary" onclick="runBatchAnalysis()">
                    <i class="fas fa-play {{ marginEnd('2') }}"></i>{{ __('Run Analysis') }}
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function showBatchAnalysisModal() {
    $('#batchAnalysisModal').modal('show');
}

// Show/hide additional parameters based on analysis type
$('select[name="analysis_type"]').change(function() {
    const analysisType = $(this).val();
    const additionalParams = $('#additionalParameters');

    if (analysisType === 'demand_forecast' || analysisType === 'price_optimization') {
        additionalParams.show();
    } else {
        additionalParams.hide();
    }
});

function runBatchAnalysis() {
    const form = document.getElementById('batchAnalysisForm');
    const formData = new FormData(form);
    
    // Convert entity_ids to array
    const entityIds = formData.get('entity_ids').split(',').map(id => parseInt(id.trim())).filter(id => !isNaN(id));
    
    if (entityIds.length === 0) {
        alert('Please enter valid entity IDs');
        return;
    }
    
    if (entityIds.length > 50) {
        alert('Maximum 50 entities allowed per batch');
        return;
    }
    
    const data = {
        analysis_type: formData.get('analysis_type'),
        entity_ids: entityIds,
        parameters: {
            forecast_days: formData.get('forecast_days') || 30,
            objective: formData.get('objective') || 'revenue'
        }
    };
    
    // Show loading state
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
    button.disabled = true;
    
    fetch('{{ route("ai.batch-analysis") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`Batch analysis completed! ${data.summary.successful} successful, ${data.summary.failed} failed.`);
            $('#batchAnalysisModal').modal('hide');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}
</script>
@endpush
