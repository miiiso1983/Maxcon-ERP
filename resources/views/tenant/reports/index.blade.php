@extends('tenant.layouts.app')

@section('title', __('Reports Dashboard'))

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">📊 {{ __('Reports Dashboard') }}</h1>
            <p class="text-muted">{{ __('Comprehensive business intelligence and reporting') }}</p>
        </div>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createReportModal">
                <i class="fas fa-plus"></i> {{ __('Create Report') }}
            </button>
            <a href="{{ route('reports.analytics') }}" class="btn btn-outline-info">
                <i class="fas fa-chart-line"></i> {{ __('Analytics') }}
            </a>
        </div>
    </div>

    <!-- Key Metrics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ number_format(25) }}</h4>
                            <p class="mb-0">{{ __('Total Reports') }}</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-file-alt fa-2x"></i>
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
                            <h4 class="mb-0">{{ number_format(156) }}</h4>
                            <p class="mb-0">{{ __('Reports Generated') }}</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-download fa-2x"></i>
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
                            <h4 class="mb-0">{{ number_format(12) }}</h4>
                            <p class="mb-0">{{ __('Scheduled Reports') }}</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clock fa-2x"></i>
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
                            <h4 class="mb-0">{{ number_format(8) }}</h4>
                            <p class="mb-0">{{ __('Active Dashboards') }}</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-tachometer-alt fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Reports -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Quick Reports') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="quick-report-card p-3 border rounded text-center">
                                <i class="fas fa-chart-bar fa-2x text-primary mb-2"></i>
                                <h6>{{ __('Sales Report') }}</h6>
                                <p class="text-muted small">{{ __('Daily, weekly, monthly sales') }}</p>
                                <a href="{{ route('reports.sales') }}" class="btn btn-sm btn-primary">
                                    {{ __('Generate') }}
                                </a>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="quick-report-card p-3 border rounded text-center">
                                <i class="fas fa-boxes fa-2x text-success mb-2"></i>
                                <h6>{{ __('Inventory Report') }}</h6>
                                <p class="text-muted small">{{ __('Stock levels and movements') }}</p>
                                <a href="{{ route('reports.inventory') }}" class="btn btn-sm btn-success">
                                    {{ __('Generate') }}
                                </a>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="quick-report-card p-3 border rounded text-center">
                                <i class="fas fa-users fa-2x text-info mb-2"></i>
                                <h6>{{ __('Customer Report') }}</h6>
                                <p class="text-muted small">{{ __('Customer analytics and behavior') }}</p>
                                <a href="{{ route('reports.customers') }}" class="btn btn-sm btn-info">
                                    {{ __('Generate') }}
                                </a>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="quick-report-card p-3 border rounded text-center">
                                <i class="fas fa-calculator fa-2x text-warning mb-2"></i>
                                <h6>{{ __('Financial Report') }}</h6>
                                <p class="text-muted small">{{ __('P&L, balance sheet, cash flow') }}</p>
                                <a href="{{ route('reports.financial') }}" class="btn btn-sm btn-warning">
                                    {{ __('Generate') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Reports and Scheduled Reports -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('Recent Reports') }}</h5>
                    <a href="#" class="btn btn-sm btn-outline-primary">{{ __('View All') }}</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('Report Name') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Generated') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @for($i = 1; $i <= 8; $i++)
                                <tr>
                                    <td>
                                        <div>
                                            <h6 class="mb-0">{{ __('Monthly Sales Report') }} {{ $i }}</h6>
                                            <small class="text-muted">{{ __('Generated by Admin') }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        @php $types = ['Sales', 'Inventory', 'Financial', 'Customer'] @endphp
                                        <span class="badge bg-primary">{{ $types[array_rand($types)] }}</span>
                                    </td>
                                    <td>{{ now()->subDays(rand(0, 30))->format('M d, Y H:i') }}</td>
                                    <td>
                                        @php $status = ['Completed', 'Processing', 'Failed'][array_rand(['Completed', 'Processing', 'Failed'])] @endphp
                                        <span class="badge bg-{{ $status == 'Completed' ? 'success' : ($status == 'Processing' ? 'warning' : 'danger') }}">
                                            {{ $status }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" onclick="viewReport({{ $i }})">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-outline-success" onclick="downloadReport({{ $i }})">
                                                <i class="fas fa-download"></i>
                                            </button>
                                            <button class="btn btn-outline-secondary" onclick="shareReport({{ $i }})">
                                                <i class="fas fa-share"></i>
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
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Scheduled Reports') }}</h5>
                </div>
                <div class="card-body">
                    @for($i = 1; $i <= 5; $i++)
                    <div class="d-flex align-items-center mb-3 p-2 border rounded">
                        <div class="me-3">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fas fa-calendar"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0">{{ __('Weekly Sales Summary') }}</h6>
                            <small class="text-muted">{{ __('Every Monday at 9:00 AM') }}</small>
                        </div>
                        <div>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary" onclick="editSchedule({{ $i }})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-outline-danger" onclick="deleteSchedule({{ $i }})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    @endfor
                    <div class="text-center">
                        <button class="btn btn-sm btn-outline-primary" onclick="addSchedule()">
                            <i class="fas fa-plus"></i> {{ __('Add Schedule') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Categories -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Report Categories') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2 mb-3">
                            <div class="category-card p-3 border rounded text-center">
                                <i class="fas fa-chart-line fa-2x text-primary mb-2"></i>
                                <h6>{{ __('Sales') }}</h6>
                                <small class="text-muted">{{ __('12 reports') }}</small>
                            </div>
                        </div>
                        <div class="col-md-2 mb-3">
                            <div class="category-card p-3 border rounded text-center">
                                <i class="fas fa-warehouse fa-2x text-success mb-2"></i>
                                <h6>{{ __('Inventory') }}</h6>
                                <small class="text-muted">{{ __('8 reports') }}</small>
                            </div>
                        </div>
                        <div class="col-md-2 mb-3">
                            <div class="category-card p-3 border rounded text-center">
                                <i class="fas fa-users fa-2x text-info mb-2"></i>
                                <h6>{{ __('Customers') }}</h6>
                                <small class="text-muted">{{ __('6 reports') }}</small>
                            </div>
                        </div>
                        <div class="col-md-2 mb-3">
                            <div class="category-card p-3 border rounded text-center">
                                <i class="fas fa-money-bill-wave fa-2x text-warning mb-2"></i>
                                <h6>{{ __('Financial') }}</h6>
                                <small class="text-muted">{{ __('10 reports') }}</small>
                            </div>
                        </div>
                        <div class="col-md-2 mb-3">
                            <div class="category-card p-3 border rounded text-center">
                                <i class="fas fa-user-tie fa-2x text-secondary mb-2"></i>
                                <h6>{{ __('HR') }}</h6>
                                <small class="text-muted">{{ __('5 reports') }}</small>
                            </div>
                        </div>
                        <div class="col-md-2 mb-3">
                            <div class="category-card p-3 border rounded text-center">
                                <i class="fas fa-cogs fa-2x text-dark mb-2"></i>
                                <h6>{{ __('Operations') }}</h6>
                                <small class="text-muted">{{ __('7 reports') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Report Modal -->
<div class="modal fade" id="createReportModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Create New Report') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createReportForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Report Name') }}</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Report Type') }}</label>
                            <select class="form-select" name="type" required>
                                <option value="">{{ __('Select type...') }}</option>
                                <option value="sales">{{ __('Sales Report') }}</option>
                                <option value="inventory">{{ __('Inventory Report') }}</option>
                                <option value="customer">{{ __('Customer Report') }}</option>
                                <option value="financial">{{ __('Financial Report') }}</option>
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">{{ __('Description') }}</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Date Range') }}</label>
                            <select class="form-select" name="date_range">
                                <option value="today">{{ __('Today') }}</option>
                                <option value="week">{{ __('This Week') }}</option>
                                <option value="month" selected>{{ __('This Month') }}</option>
                                <option value="quarter">{{ __('This Quarter') }}</option>
                                <option value="year">{{ __('This Year') }}</option>
                                <option value="custom">{{ __('Custom Range') }}</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Format') }}</label>
                            <select class="form-select" name="format">
                                <option value="pdf">{{ __('PDF') }}</option>
                                <option value="excel">{{ __('Excel') }}</option>
                                <option value="csv">{{ __('CSV') }}</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-primary" onclick="createReport()">{{ __('Create Report') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function viewReport(id) {
    alert('Viewing report ' + id);
}

function downloadReport(id) {
    alert('Downloading report ' + id);
}

function shareReport(id) {
    alert('Sharing report ' + id);
}

function editSchedule(id) {
    alert('Editing schedule ' + id);
}

function deleteSchedule(id) {
    if (confirm('Are you sure you want to delete this schedule?')) {
        alert('Schedule ' + id + ' deleted');
    }
}

function addSchedule() {
    alert('Adding new schedule');
}

function createReport() {
    const form = document.getElementById('createReportForm');
    const formData = new FormData(form);
    
    // Simulate report creation
    alert('Report creation started. You will be notified when it\'s ready.');
    
    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('createReportModal'));
    modal.hide();
    
    // Reset form
    form.reset();
}

// Add hover effects to cards
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.quick-report-card, .category-card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
            this.style.boxShadow = '0 4px 8px rgba(0,0,0,0.1)';
            this.style.transition = 'all 0.3s ease';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = 'none';
        });
    });
});
</script>
@endpush
