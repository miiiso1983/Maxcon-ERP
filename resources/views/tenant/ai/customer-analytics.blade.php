@extends('tenant.layouts.app')

@section('title', __('Customer Analytics'))

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">🧠 {{ __('AI Customer Analytics') }}</h1>
            <p class="text-muted">{{ __('Advanced customer behavior analysis and predictions') }}</p>
        </div>
        <div>
            <a href="{{ route('ai.dashboard') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> {{ __('Back to AI Dashboard') }}
            </a>
        </div>
    </div>

    <!-- Analytics Overview Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ number_format(1250) }}</h4>
                            <p class="mb-0">{{ __('Total Customers') }}</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-users fa-2x"></i>
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
                            <h4 class="mb-0">{{ number_format(85, 1) }}%</h4>
                            <p class="mb-0">{{ __('Retention Rate') }}</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-heart fa-2x"></i>
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
                            <h4 class="mb-0">{{ number_format(45) }}</h4>
                            <p class="mb-0">{{ __('High Churn Risk') }}</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
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
                            <h4 class="mb-0">{{ number_format(2.3, 1) }}</h4>
                            <p class="mb-0">{{ __('Avg CLV Score') }}</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-chart-line fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Segmentation and Analysis -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Customer Segmentation Analysis') }}</h5>
                </div>
                <div class="card-body">
                    <canvas id="segmentationChart" height="100"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Churn Risk Distribution') }}</h5>
                </div>
                <div class="card-body">
                    <canvas id="churnRiskChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Behavior Insights -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Purchase Behavior Trends') }}</h5>
                </div>
                <div class="card-body">
                    <canvas id="behaviorChart" height="150"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Customer Lifetime Value') }}</h5>
                </div>
                <div class="card-body">
                    <canvas id="clvChart" height="150"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- AI Predictions and Recommendations -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('AI-Powered Customer Insights') }}</h5>
                    <div>
                        <button class="btn btn-sm btn-primary" onclick="runAnalysis()">
                            <i class="fas fa-brain"></i> {{ __('Run Analysis') }}
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" onclick="exportInsights()">
                            <i class="fas fa-download"></i> {{ __('Export') }}
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="insight-card p-3 border rounded mb-3">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="insight-icon bg-primary text-white rounded-circle me-2" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-user-friends"></i>
                                    </div>
                                    <h6 class="mb-0">{{ __('Segment Insights') }}</h6>
                                </div>
                                <p class="text-muted small mb-2">{{ __('VIP customers show 35% higher engagement') }}</p>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-primary" style="width: 85%"></div>
                                </div>
                                <small class="text-muted">{{ __('Confidence: 85%') }}</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="insight-card p-3 border rounded mb-3">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="insight-icon bg-warning text-white rounded-circle me-2" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </div>
                                    <h6 class="mb-0">{{ __('Churn Alert') }}</h6>
                                </div>
                                <p class="text-muted small mb-2">{{ __('45 customers at high risk of churning') }}</p>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-warning" style="width: 92%"></div>
                                </div>
                                <small class="text-muted">{{ __('Confidence: 92%') }}</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="insight-card p-3 border rounded mb-3">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="insight-icon bg-success text-white rounded-circle me-2" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-arrow-up"></i>
                                    </div>
                                    <h6 class="mb-0">{{ __('Upsell Opportunity') }}</h6>
                                </div>
                                <p class="text-muted small mb-2">{{ __('120 customers ready for premium upgrade') }}</p>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-success" style="width: 78%"></div>
                                </div>
                                <small class="text-muted">{{ __('Confidence: 78%') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Analysis Tools -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Individual Customer Analysis') }}</h5>
                </div>
                <div class="card-body">
                    <form id="customerAnalysisForm">
                        <div class="mb-3">
                            <label class="form-label">{{ __('Select Customer') }}</label>
                            <select class="form-select" name="customer_id" required>
                                <option value="">{{ __('Choose a customer...') }}</option>
                                @for($i = 1; $i <= 10; $i++)
                                <option value="{{ $i }}">{{ __('Customer') }} {{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Analysis Type') }}</label>
                            <select class="form-select" name="analysis_type" required>
                                <option value="behavior">{{ __('Behavior Analysis') }}</option>
                                <option value="churn_risk">{{ __('Churn Risk Prediction') }}</option>
                                <option value="clv">{{ __('Customer Lifetime Value') }}</option>
                                <option value="segmentation">{{ __('Segment Classification') }}</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> {{ __('Analyze Customer') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Batch Analysis') }}</h5>
                </div>
                <div class="card-body">
                    <form id="batchAnalysisForm">
                        <div class="mb-3">
                            <label class="form-label">{{ __('Customer Segment') }}</label>
                            <select class="form-select" name="segment" required>
                                <option value="">{{ __('Select segment...') }}</option>
                                <option value="vip">{{ __('VIP Customers') }}</option>
                                <option value="regular">{{ __('Regular Customers') }}</option>
                                <option value="new">{{ __('New Customers') }}</option>
                                <option value="inactive">{{ __('Inactive Customers') }}</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Analysis Type') }}</label>
                            <select class="form-select" name="batch_analysis_type" required>
                                <option value="churn_prediction">{{ __('Churn Risk Assessment') }}</option>
                                <option value="behavior_analysis">{{ __('Behavior Pattern Analysis') }}</option>
                                <option value="clv_calculation">{{ __('CLV Calculation') }}</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-cogs"></i> {{ __('Run Batch Analysis') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Predictions Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ __('Recent Customer Predictions') }}</h5>
            <div>
                <button class="btn btn-sm btn-outline-primary" onclick="refreshPredictions()">
                    <i class="fas fa-refresh"></i> {{ __('Refresh') }}
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>{{ __('Customer') }}</th>
                            <th>{{ __('Prediction Type') }}</th>
                            <th>{{ __('Result') }}</th>
                            <th>{{ __('Confidence') }}</th>
                            <th>{{ __('Risk Level') }}</th>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for($i = 1; $i <= 10; $i++)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm me-2">
                                        <div class="avatar-title bg-primary rounded-circle">
                                            {{ substr('Customer ' . $i, 0, 2) }}
                                        </div>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ __('Customer') }} {{ $i }}</h6>
                                        <small class="text-muted">customer{{ $i }}@example.com</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @php $types = ['Churn Risk', 'CLV Analysis', 'Behavior Pattern', 'Segmentation'] @endphp
                                <span class="badge bg-info">{{ $types[array_rand($types)] }}</span>
                            </td>
                            <td>
                                @php $result = rand(10, 95) @endphp
                                {{ $result }}%
                            </td>
                            <td>
                                @php $confidence = rand(75, 98) @endphp
                                <div class="progress" style="height: 8px; width: 60px;">
                                    <div class="progress-bar bg-{{ $confidence >= 85 ? 'success' : 'warning' }}" style="width: '{{ (int) $confidence }}%'; /* 80% */"></div>
                                </div>
                                <small>{{ $confidence }}%</small>
                            </td>
                            <td>
                                @php $risk = ['Low', 'Medium', 'High'][array_rand(['Low', 'Medium', 'High'])] @endphp
                                <span class="badge bg-{{ $risk == 'High' ? 'danger' : ($risk == 'Medium' ? 'warning' : 'success') }}">
                                    {{ $risk }}
                                </span>
                            </td>
                            <td>{{ now()->subDays(rand(0, 7))->format('M d, Y') }}</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="viewDetails('{{ $i }}')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-success" onclick="takeAction('{{ $i }}')">
                                        <i class="fas fa-play"></i>
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
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Customer Segmentation Chart
const segmentationCtx = document.getElementById('segmentationChart').getContext('2d');
new Chart(segmentationCtx, {
    type: 'bar',
    data: {
        labels: ['VIP', 'Regular', 'New', 'Inactive', 'At Risk'],
        datasets: [{
            label: 'Customer Count',
            data: [120, 450, 180, 85, 45],
            backgroundColor: [
                'rgba(255, 193, 7, 0.8)',
                'rgba(40, 167, 69, 0.8)',
                'rgba(0, 123, 255, 0.8)',
                'rgba(108, 117, 125, 0.8)',
                'rgba(220, 53, 69, 0.8)'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

// Churn Risk Chart
const churnRiskCtx = document.getElementById('churnRiskChart').getContext('2d');
new Chart(churnRiskCtx, {
    type: 'doughnut',
    data: {
        labels: ['Low Risk', 'Medium Risk', 'High Risk'],
        datasets: [{
            data: [65, 25, 10],
            backgroundColor: [
                'rgba(40, 167, 69, 0.8)',
                'rgba(255, 193, 7, 0.8)',
                'rgba(220, 53, 69, 0.8)'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

// Behavior Chart
const behaviorCtx = document.getElementById('behaviorChart').getContext('2d');
new Chart(behaviorCtx, {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [{
            label: 'Purchase Frequency',
            data: [2.1, 2.3, 2.8, 2.5, 3.1, 2.9],
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

// CLV Chart
const clvCtx = document.getElementById('clvChart').getContext('2d');
new Chart(clvCtx, {
    type: 'scatter',
    data: {
        datasets: [{
            label: 'Customer Lifetime Value',
            data: [
                {x: 1, y: 1200}, {x: 2, y: 1800}, {x: 3, y: 2400},
                {x: 4, y: 1600}, {x: 5, y: 3200}, {x: 6, y: 2800}
            ],
            backgroundColor: 'rgba(255, 99, 132, 0.8)'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            x: { title: { display: true, text: 'Customer Tenure (Years)' } },
            y: { title: { display: true, text: 'CLV ($)' } }
        }
    }
});

// Form handlers
document.getElementById('customerAnalysisForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Analyzing...';
    submitBtn.disabled = true;
    
    // Simulate API call
    setTimeout(() => {
        alert('Customer analysis completed! Check the results in the predictions table.');
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }, 2000);
});

document.getElementById('batchAnalysisForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    submitBtn.disabled = true;
    
    // Simulate API call
    setTimeout(() => {
        alert('Batch analysis completed! Results have been updated.');
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }, 3000);
});

function runAnalysis() {
    alert('Running comprehensive AI analysis...');
}

function exportInsights() {
    alert('Exporting customer insights...');
}

function viewDetails(customerId) {
    alert('Viewing details for customer ' + customerId);
}

function takeAction(customerId) {
    alert('Taking action for customer ' + customerId);
}

function refreshPredictions() {
    alert('Refreshing predictions...');
}
</script>
@endpush
