@extends('tenant.layouts.app')

@section('title', __('Reports Dashboard'))
@section('page-title', __('Reports & Analytics'))

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">{{ __('app.dashboard') }}</a></li>
<li class="breadcrumb-item active">{{ __('Reports') }}</li>
@endsection

@section('content')
<!-- Key Metrics Overview -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stats-card-success">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col {{ marginEnd('2') }}">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            {{ __('Sales Today') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ formatCurrency($metrics['total_sales_today']) }}</div>
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
                            {{ __('Sales This Month') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ formatCurrency($metrics['total_sales_month']) }}</div>
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
                            {{ __('Total Customers') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ number_format($metrics['total_customers']) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x opacity-75"></i>
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
                            {{ __('Low Stock Items') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ $metrics['low_stock_items'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2 mb-2">
                        <a href="{{ route('reports.sales') }}" class="btn btn-success w-100">
                            <i class="fas fa-chart-bar {{ marginEnd('2') }}"></i>{{ __('Sales Reports') }}
                        </a>
                    </div>
                    <div class="col-md-2 mb-2">
                        <a href="{{ route('reports.inventory') }}" class="btn btn-info w-100">
                            <i class="fas fa-boxes {{ marginEnd('2') }}"></i>{{ __('Inventory Reports') }}
                        </a>
                    </div>
                    <div class="col-md-2 mb-2">
                        <a href="{{ route('reports.financial') }}" class="btn btn-primary w-100">
                            <i class="fas fa-calculator {{ marginEnd('2') }}"></i>{{ __('Financial Reports') }}
                        </a>
                    </div>
                    <div class="col-md-2 mb-2">
                        <a href="{{ route('analytics.dashboard') }}" class="btn btn-warning w-100">
                            <i class="fas fa-analytics {{ marginEnd('2') }}"></i>{{ __('Analytics') }}
                        </a>
                    </div>
                    <div class="col-md-2 mb-2">
                        <a href="{{ route('reports.index') }}" class="btn btn-secondary w-100">
                            <i class="fas fa-file-alt {{ marginEnd('2') }}"></i>{{ __('All Reports') }}
                        </a>
                    </div>
                    <div class="col-md-2 mb-2">
                        <a href="{{ route('reports.create') }}" class="btn btn-dark w-100">
                            <i class="fas fa-plus {{ marginEnd('2') }}"></i>{{ __('Create Report') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Report Executions & Popular Reports -->
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="m-0">{{ __('Recent Report Executions') }}</h6>
                <span class="badge bg-info">{{ $metrics['recent_executions'] }} {{ __('today') }}</span>
            </div>
            <div class="card-body p-0">
                @if($recentExecutions->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('Report') }}</th>
                                <th>{{ __('Executed By') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Duration') }}</th>
                                <th>{{ __('Rows') }}</th>
                                <th>{{ __('Date') }}</th>
                                <th width="80">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentExecutions as $execution)
                            <tr>
                                <td>
                                    <div>
                                        <strong>{{ $execution->report->name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $execution->report->report_type }}</small>
                                    </div>
                                </td>
                                <td>{{ $execution->executedBy->name }}</td>
                                <td>
                                    <span class="badge bg-{{ $execution->status_color }}">
                                        {{ ucfirst($execution->status) }}
                                    </span>
                                </td>
                                <td>{{ $execution->duration_text }}</td>
                                <td>{{ number_format($execution->row_count) }}</td>
                                <td>
                                    <small>{{ $execution->started_at, 'short'->format('M d, Y') }}</small>
                                </td>
                                <td>
                                    @if($execution->status === 'completed')
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary btn-sm" 
                                                onclick="viewExecution('{{ $execution->id }}')" 
                                                title="{{ __('View') }}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-success btn-sm" 
                                                onclick="exportExecution('{{ $execution->id }}')" 
                                                title="{{ __('Export') }}">
                                            <i class="fas fa-download"></i>
                                        </button>
                                    </div>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4">
                    <i class="fas fa-chart-bar fa-2x text-muted mb-2"></i>
                    <p class="text-muted">{{ __('No recent report executions') }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0">{{ __('Popular Reports') }}</h6>
            </div>
            <div class="card-body p-0">
                @if($popularReports->count() > 0)
                <div class="list-group list-group-flush">
                    @foreach($popularReports as $report)
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-bold">{{ $report->name }}</div>
                            <small class="text-muted">
                                <span class="badge bg-{{ $report->type_color }} {{ marginEnd('1') }}">{{ $report->report_type }}</span>
                                {{ $report->category }}
                            </small>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold">{{ $report->run_count }}</div>
                            <small class="text-muted">{{ __('runs') }}</small>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-4">
                    <i class="fas fa-star fa-2x text-muted mb-2"></i>
                    <p class="text-muted">{{ __('No popular reports yet') }}</p>
                </div>
                @endif
            </div>
            <div class="card-footer">
                <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline-primary w-100">
                    {{ __('View All Reports') }}
                </a>
            </div>
        </div>

        <!-- Quick Analytics -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="m-0">{{ __('Quick Analytics') }}</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <div class="border-end">
                            <div class="h4 mb-0">{{ $metrics['active_reports'] }}</div>
                            <small class="text-muted">{{ __('Active Reports') }}</small>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div>
                            <div class="h4 mb-0">{{ $metrics['recent_executions'] }}</div>
                            <small class="text-muted">{{ __('Today\'s Runs') }}</small>
                        </div>
                    </div>
                </div>
                <div class="d-grid gap-2">
                    <a href="{{ route('analytics.sales') }}" class="btn btn-sm btn-outline-success">
                        <i class="fas fa-chart-line {{ marginEnd('2') }}"></i>{{ __('Sales Analytics') }}
                    </a>
                    <a href="{{ route('analytics.customers') }}" class="btn btn-sm btn-outline-info">
                        <i class="fas fa-users {{ marginEnd('2') }}"></i>{{ __('Customer Analytics') }}
                    </a>
                    <a href="{{ route('analytics.products') }}" class="btn btn-sm btn-outline-warning">
                        <i class="fas fa-box {{ marginEnd('2') }}"></i>{{ __('Product Analytics') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function viewExecution(executionId) {
    // Implementation for viewing execution results
    window.open(`/reports/executions/${executionId}`, '_blank');
}

function exportExecution(executionId) {
    // Implementation for exporting execution results
    const format = prompt('Export format (pdf, excel, csv):', 'pdf');
    if (format && ['pdf', 'excel', 'csv'].includes(format.toLowerCase())) {
        window.location.href = `/reports/executions/${executionId}/export?format=${format}`;
    }
}

// Auto-refresh metrics every 5 minutes
setInterval(function() {
    location.reload();
}, 300000);
</script>
@endpush
