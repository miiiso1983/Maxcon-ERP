@extends('tenant.layouts.app')

@section('title', __('Testing & Quality Assurance'))
@section('page-title', __('Testing Dashboard'))

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">{{ __('app.dashboard') }}</a></li>
<li class="breadcrumb-item active">{{ __('Testing') }}</li>
@endsection

@section('content')
<!-- Test Results Overview -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stats-card-{{ $latestResults['summary']['overall_status'] === 'passed' ? 'success' : 'danger' }}">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col {{ marginEnd('2') }}">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            {{ __('Test Status') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">
                            {{ $latestResults['summary']['success_rate'] ?? 0 }}%
                        </div>
                        <small class="text-muted">
                            {{ $latestResults['summary']['passed_test_suites'] ?? 0 }}/{{ $latestResults['summary']['total_test_suites'] ?? 0 }} {{ __('passed') }}
                        </small>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-{{ $latestResults['summary']['overall_status'] === 'passed' ? 'check-circle' : 'times-circle' }} fa-2x opacity-75"></i>
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
                            {{ __('Code Coverage') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ $latestResults['summary']['code_coverage'] ?? 0 }}%</div>
                        <div class="progress mt-2" style="height: 6px;">
                            <div class="progress-bar bg-info" style="width: {{ $latestResults['summary']['code_coverage'] ?? 0 }}%; /* 80% */"></div>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-code fa-2x opacity-75"></i>
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
                            {{ __('Quality Score') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ $qualityMetrics['quality_score'] ?? 0 }}/100</div>
                        <small class="text-muted">{{ __('Code quality rating') }}</small>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-star fa-2x opacity-75"></i>
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
                            {{ __('Security Score') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ $qualityMetrics['security_score'] ?? 0 }}/100</div>
                        <small class="text-muted">{{ __('Security assessment') }}</small>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-shield-alt fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Test Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0">{{ __('Test Suite Actions') }}</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2 mb-3">
                        <div class="card h-100 border-success">
                            <div class="card-body text-center p-3">
                                <i class="fas fa-play fa-2x text-success mb-2"></i>
                                <h6 class="small">{{ __('Run All Tests') }}</h6>
                                <button class="btn btn-success btn-sm" onclick="runTests('all')">
                                    {{ __('Run All') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-2 mb-3">
                        <div class="card h-100 border-primary">
                            <div class="card-body text-center p-3">
                                <i class="fas fa-cube fa-2x text-primary mb-2"></i>
                                <h6 class="small">{{ __('Unit Tests') }}</h6>
                                <button class="btn btn-primary btn-sm" onclick="runTests('unit')">
                                    {{ __('Run Unit') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-2 mb-3">
                        <div class="card h-100 border-info">
                            <div class="card-body text-center p-3">
                                <i class="fas fa-cogs fa-2x text-info mb-2"></i>
                                <h6 class="small">{{ __('Feature Tests') }}</h6>
                                <button class="btn btn-info btn-sm" onclick="runTests('feature')">
                                    {{ __('Run Feature') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-2 mb-3">
                        <div class="card h-100 border-warning">
                            <div class="card-body text-center p-3">
                                <i class="fas fa-link fa-2x text-warning mb-2"></i>
                                <h6 class="small">{{ __('Integration') }}</h6>
                                <button class="btn btn-warning btn-sm" onclick="runTests('integration')">
                                    {{ __('Run Integration') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-2 mb-3">
                        <div class="card h-100 border-secondary">
                            <div class="card-body text-center p-3">
                                <i class="fas fa-desktop fa-2x text-secondary mb-2"></i>
                                <h6 class="small">{{ __('Browser Tests') }}</h6>
                                <button class="btn btn-secondary btn-sm" onclick="runTests('browser')">
                                    {{ __('Run Browser') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-2 mb-3">
                        <div class="card h-100 border-dark">
                            <div class="card-body text-center p-3">
                                <i class="fas fa-chart-line fa-2x text-dark mb-2"></i>
                                <h6 class="small">{{ __('Coverage') }}</h6>
                                <button class="btn btn-dark btn-sm" onclick="generateCoverage()">
                                    {{ __('Generate') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Test Results & Quality Metrics -->
<div class="row mb-4">
    <!-- Latest Test Results -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="m-0">{{ __('Latest Test Results') }}</h6>
                <button class="btn btn-outline-primary btn-sm" onclick="refreshResults()">
                    <i class="fas fa-sync-alt"></i> {{ __('Refresh') }}
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('Test Suite') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Duration') }}</th>
                                <th>{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(['unit_tests' => 'Unit Tests', 'feature_tests' => 'Feature Tests', 'integration_tests' => 'Integration Tests', 'browser_tests' => 'Browser Tests'] as $key => $name)
                            <tr>
                                <td>
                                    <strong>{{ $name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ ucfirst(str_replace('_', ' ', $key)) }}</small>
                                </td>
                                <td>
                                    @php
                                        $status = $latestResults[$key]['status'] ?? 'not_run';
                                        $statusColor = match($status) {
                                            'passed' => 'success',
                                            'failed' => 'danger',
                                            'error' => 'warning',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $statusColor }}">
                                        {{ ucfirst(str_replace('_', ' ', $status)) }}
                                    </span>
                                </td>
                                <td>
                                    @if(isset($latestResults[$key]['duration']))
                                        {{ number_format($latestResults[$key]['duration'], 2) }}s
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <button class="btn btn-outline-primary btn-sm" onclick="runTests('{{ str_replace('_tests', '', $key) }}')">
                                        <i class="fas fa-play"></i> {{ __('Run') }}
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Quality Metrics -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0">{{ __('Quality Metrics') }}</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small">{{ __('Code Coverage') }}</span>
                        <span class="small font-weight-bold">{{ $qualityMetrics['code_coverage'] ?? 0 }}%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-info" style="width: {{ $qualityMetrics['code_coverage'] ?? 0 }}%; /* 80% */"></div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small">{{ __('Quality Score') }}</span>
                        <span class="small font-weight-bold">{{ $qualityMetrics['quality_score'] ?? 0 }}/100</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-warning" style="width: {{ $qualityMetrics['quality_score'] ?? 0 }}%; /* 80% */"></div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small">{{ __('Security Score') }}</span>
                        <span class="small font-weight-bold">{{ $qualityMetrics['security_score'] ?? 0 }}/100</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-success" style="width: {{ $qualityMetrics['security_score'] ?? 0 }}%; /* 80% */"></div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small">{{ __('Performance Score') }}</span>
                        <span class="small font-weight-bold">{{ $qualityMetrics['performance_score'] ?? 0 }}/100</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-primary" style="width: {{ $qualityMetrics['performance_score'] ?? 0 }}%; /* 80% */"></div>
                    </div>
                </div>

                <hr>

                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-end">
                            <strong>{{ $qualityMetrics['maintainability_index'] ?? 0 }}</strong>
                            <br>
                            <small class="text-muted">{{ __('Maintainability') }}</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <strong>{{ $qualityMetrics['technical_debt_ratio'] ?? 0 }}%</strong>
                        <br>
                        <small class="text-muted">{{ __('Tech Debt') }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Coverage Trends & Test History -->
<div class="row">
    <!-- Coverage Trends Chart -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0">{{ __('Coverage Trends (Last 7 Days)') }}</h6>
            </div>
            <div class="card-body">
                @if(!empty($coverageTrends) && count($coverageTrends) > 0)
                <canvas id="coverageTrendsChart" height="200"></canvas>
                @else
                <div class="text-center py-4">
                    <i class="fas fa-chart-line fa-2x text-muted mb-2"></i>
                    <p class="text-muted">{{ __('No coverage trend data available') }}</p>
                    <button class="btn btn-primary" onclick="generateCoverage()">
                        {{ __('Generate Coverage Report') }}
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Test History -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0">{{ __('Recent Test Runs') }}</h6>
            </div>
            <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                @if(!empty($testHistory) && count($testHistory) > 0)
                <div class="list-group list-group-flush">
                    @foreach(array_reverse($testHistory) as $test)
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="small">
                                    <strong>{{ $test['results']['summary']['total_test_suites'] ?? 0 }} {{ __('test suites') }}</strong>
                                </div>
                                <small class="text-muted">
                                    {{ \Carbon\Carbon::parse($test['timestamp'])->diffForHumans() }}
                                </small>
                            </div>
                            <div class="text-end">
                                @php
                                    $status = $test['results']['summary']['overall_status'] ?? 'unknown';
                                    $statusColor = $status === 'passed' ? 'success' : 'danger';
                                @endphp
                                <span class="badge bg-{{ $statusColor }}">
                                    {{ ucfirst($status) }}
                                </span>
                                <br>
                                <small class="text-muted">
                                    {{ $test['results']['summary']['success_rate'] ?? 0 }}%
                                </small>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-4">
                    <i class="fas fa-history fa-2x text-muted mb-2"></i>
                    <p class="text-muted">{{ __('No test history available') }}</p>
                    <button class="btn btn-primary btn-sm" onclick="runTests('all')">
                        {{ __('Run First Test') }}
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Test Progress Modal -->
<div class="modal fade" id="testProgressModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Running Tests') }}</h5>
            </div>
            <div class="modal-body text-center">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">{{ __('Loading...') }}</span>
                </div>
                <p id="testProgressText">{{ __('Initializing test suite...') }}</p>
                <div class="progress">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" id="testProgressBar" style="width: 0%"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@if(!empty($coverageTrends) && count($coverageTrends) > 0)
// Coverage Trends Chart
const trendsCtx = document.getElementById('coverageTrendsChart').getContext('2d');
const trendsChart = new Chart(trendsCtx, {
    type: 'line',
    data: {
        labels: @json(array_column($coverageTrends ?? [], 'date')),
        datasets: [
            {
                label: '{{ __("Coverage %") }}',
                data: @json(array_column($coverageTrends ?? [], 'coverage')),
                borderColor: 'rgb(54, 162, 235)',
                backgroundColor: 'rgba(54, 162, 235, 0.1)',
                tension: 0.1
            },
            {
                label: '{{ __("Quality Score") }}',
                data: @json(array_column($coverageTrends ?? [], 'quality_score')),
                borderColor: 'rgb(255, 193, 7)',
                backgroundColor: 'rgba(255, 193, 7, 0.1)',
                tension: 0.1
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                max: 100
            }
        },
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
@endif

// Test execution functions
function runTests(testType) {
    console.log('runTests called with type:', testType);

    try {
        const modalElement = document.getElementById('testProgressModal');
        if (!modalElement) {
            console.error('Modal element not found');
            alert('Modal element not found');
            return;
        }

        const modal = new bootstrap.Modal(modalElement);
        modal.show();

        updateProgress(0, `Starting ${testType} tests...`);

        fetch('{{ route("testing.run") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ test_type: testType })
        })
        .then(response => response.json())
        .then(data => {
            modal.hide();

            if (data.success) {
                alert(`Tests completed successfully!\nResults: ${JSON.stringify(data.results, null, 2)}`);
                location.reload();
            } else {
                alert(`Test execution failed: ${data.error}`);
            }
        })
        .catch(error => {
            modal.hide();
            console.error('Error:', error);
            alert('An error occurred while running tests');
        });
    } catch (error) {
        console.error('Error in runTests:', error);
        alert('Error: ' + error.message);
    }

    updateProgress(0, `Starting ${testType} tests...`);

    fetch('{{ route("testing.run") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ test_type: testType })
    })
    .then(response => response.json())
    .then(data => {
        modal.hide();
        
        if (data.success) {
            alert(`{{ __('Tests completed successfully!') }}\n{{ __('Results:') }} ${JSON.stringify(data.results, null, 2)}`);
            location.reload();
        } else {
            alert(`{{ __('Test execution failed:') }} ${data.error}`);
        }
    })
    .catch(error => {
        modal.hide();
        console.error('Error:', error);
        alert('{{ __("An error occurred while running tests") }}');
    });
}

function generateCoverage() {
    console.log('generateCoverage called');

    try {
        const modalElement = document.getElementById('testProgressModal');
        if (!modalElement) {
            console.error('Modal element not found');
            alert('Modal element not found');
            return;
        }

        const modal = new bootstrap.Modal(modalElement);
        modal.show();

        updateProgress(0, 'Generating code coverage report...');

        fetch('{{ route("testing.coverage") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            modal.hide();

            if (data.success) {
                alert(`Coverage report generated successfully!\nCoverage: ${data.results.coverage_percentage}%`);
                location.reload();
            } else {
                alert(`Coverage generation failed: ${data.error}`);
            }
        })
        .catch(error => {
            modal.hide();
            console.error('Error:', error);
            alert('An error occurred while generating coverage');
        });
    } catch (error) {
        console.error('Error in generateCoverage:', error);
        alert('Error: ' + error.message);
    }
}

function refreshResults() {
    console.log('refreshResults called');
    location.reload();
}

function updateProgress(percentage, text) {
    document.getElementById('testProgressBar').style.width = percentage + '%';
    document.getElementById('testProgressText').textContent = text;
}

// Make functions globally accessible
window.runTests = runTests;
window.generateCoverage = generateCoverage;
window.refreshResults = refreshResults;

// Auto-refresh test results every 5 minutes
setInterval(function() {
    fetch('{{ route("testing.results") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.results.length > 0) {
                // Update UI with latest results if needed
                console.log('Test results updated');
            }
        })
        .catch(error => console.error('Error updating test results:', error));
}, 300000);
</script>
@endpush
