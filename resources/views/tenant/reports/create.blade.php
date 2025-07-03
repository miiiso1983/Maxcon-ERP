@extends('tenant.layouts.app')

@section('title', __('Create Report'))

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">📊 {{ __('Create New Report') }}</h1>
            <p class="text-muted">{{ __('Build custom reports for your business needs') }}</p>
        </div>
        <div>
            <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> {{ __('Back to Reports') }}
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Report Configuration') }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('reports.store') }}" method="POST" id="reportForm">
                        @csrf
                        
                        <!-- Basic Information -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h6 class="text-primary mb-3">{{ __('Basic Information') }}</h6>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Report Name') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Report Type') }} <span class="text-danger">*</span></label>
                                <select class="form-select @error('report_type') is-invalid @enderror" 
                                        name="report_type" required>
                                    <option value="">{{ __('Select type...') }}</option>
                                    @foreach($types as $key => $value)
                                        <option value="{{ $key }}" {{ old('report_type') == $key ? 'selected' : '' }}>
                                            {{ $value }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('report_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Category') }} <span class="text-danger">*</span></label>
                                <select class="form-select @error('category') is-invalid @enderror" 
                                        name="category" required>
                                    <option value="">{{ __('Select category...') }}</option>
                                    @foreach($categories as $key => $value)
                                        <option value="{{ $key }}" {{ old('category') == $key ? 'selected' : '' }}>
                                            {{ $value }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Visibility') }}</label>
                                <select class="form-select" name="is_public">
                                    <option value="0" {{ old('is_public') == '0' ? 'selected' : '' }}>
                                        {{ __('Private (Only Me)') }}
                                    </option>
                                    <option value="1" {{ old('is_public') == '1' ? 'selected' : '' }}>
                                        {{ __('Public (All Users)') }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">{{ __('Description') }}</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          name="description" rows="3">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Data Configuration -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h6 class="text-primary mb-3">{{ __('Data Configuration') }}</h6>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Data Source') }}</label>
                                <select class="form-select" name="data_source" id="dataSource">
                                    <option value="">{{ __('Select data source...') }}</option>
                                    <option value="sales">{{ __('Sales Data') }}</option>
                                    <option value="products">{{ __('Products Data') }}</option>
                                    <option value="customers">{{ __('Customers Data') }}</option>
                                    <option value="inventory">{{ __('Inventory Data') }}</option>
                                    <option value="financial">{{ __('Financial Data') }}</option>
                                </select>
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
                        </div>

                        <!-- Chart Configuration -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h6 class="text-primary mb-3">{{ __('Chart Configuration') }}</h6>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Chart Type') }}</label>
                                <select class="form-select" name="chart_type">
                                    <option value="">{{ __('No Chart') }}</option>
                                    <option value="bar">{{ __('Bar Chart') }}</option>
                                    <option value="line">{{ __('Line Chart') }}</option>
                                    <option value="pie">{{ __('Pie Chart') }}</option>
                                    <option value="doughnut">{{ __('Doughnut Chart') }}</option>
                                    <option value="area">{{ __('Area Chart') }}</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Group By') }}</label>
                                <select class="form-select" name="group_by" id="groupBy">
                                    <option value="">{{ __('No Grouping') }}</option>
                                    <option value="date">{{ __('Date') }}</option>
                                    <option value="category">{{ __('Category') }}</option>
                                    <option value="status">{{ __('Status') }}</option>
                                </select>
                            </div>
                        </div>

                        <!-- Filters -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h6 class="text-primary mb-3">{{ __('Filters') }}</h6>
                                <div id="filtersContainer">
                                    <div class="filter-row row mb-2">
                                        <div class="col-md-4">
                                            <select class="form-select" name="filters[0][field]">
                                                <option value="">{{ __('Select field...') }}</option>
                                                <option value="status">{{ __('Status') }}</option>
                                                <option value="category">{{ __('Category') }}</option>
                                                <option value="date">{{ __('Date') }}</option>
                                                <option value="amount">{{ __('Amount') }}</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <select class="form-select" name="filters[0][operator]">
                                                <option value="=">=</option>
                                                <option value="!=">!=</option>
                                                <option value=">">></option>
                                                <option value="<"><</option>
                                                <option value=">=">>=</option>
                                                <option value="<="><=</option>
                                                <option value="like">{{ __('Contains') }}</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <input type="text" class="form-control" name="filters[0][value]" 
                                                   placeholder="{{ __('Filter value...') }}">
                                        </div>
                                        <div class="col-md-1">
                                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeFilter(this)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="addFilter()">
                                    <i class="fas fa-plus"></i> {{ __('Add Filter') }}
                                </button>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="row">
                            <div class="col-md-12">
                                <hr>
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('reports.index') }}" class="btn btn-secondary">
                                        {{ __('Cancel') }}
                                    </a>
                                    <button type="button" class="btn btn-outline-primary" onclick="previewReport()">
                                        <i class="fas fa-eye"></i> {{ __('Preview') }}
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> {{ __('Create Report') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Report Templates -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">{{ __('Quick Templates') }}</h6>
                </div>
                <div class="card-body">
                    <div class="template-item p-2 border rounded mb-2 cursor-pointer" onclick="loadTemplate('sales_summary')">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-chart-bar text-primary me-2"></i>
                            <div>
                                <h6 class="mb-0">{{ __('Sales Summary') }}</h6>
                                <small class="text-muted">{{ __('Monthly sales overview') }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="template-item p-2 border rounded mb-2 cursor-pointer" onclick="loadTemplate('inventory_status')">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-boxes text-success me-2"></i>
                            <div>
                                <h6 class="mb-0">{{ __('Inventory Status') }}</h6>
                                <small class="text-muted">{{ __('Stock levels and alerts') }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="template-item p-2 border rounded mb-2 cursor-pointer" onclick="loadTemplate('customer_analysis')">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-users text-info me-2"></i>
                            <div>
                                <h6 class="mb-0">{{ __('Customer Analysis') }}</h6>
                                <small class="text-muted">{{ __('Customer behavior insights') }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="template-item p-2 border rounded mb-2 cursor-pointer" onclick="loadTemplate('financial_overview')">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-calculator text-warning me-2"></i>
                            <div>
                                <h6 class="mb-0">{{ __('Financial Overview') }}</h6>
                                <small class="text-muted">{{ __('Revenue and expenses') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Help & Tips -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">{{ __('Tips & Help') }}</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6>{{ __('Report Building Tips:') }}</h6>
                        <ul class="mb-0 small">
                            <li>{{ __('Choose a descriptive name for your report') }}</li>
                            <li>{{ __('Select the appropriate data source') }}</li>
                            <li>{{ __('Use filters to narrow down your data') }}</li>
                            <li>{{ __('Preview before saving') }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let filterIndex = 1;

function addFilter() {
    const container = document.getElementById('filtersContainer');
    const filterRow = document.createElement('div');
    filterRow.className = 'filter-row row mb-2';
    filterRow.innerHTML = `
        <div class="col-md-4">
            <select class="form-select" name="filters[${filterIndex}][field]">
                <option value="">{{ __('Select field...') }}</option>
                <option value="status">{{ __('Status') }}</option>
                <option value="category">{{ __('Category') }}</option>
                <option value="date">{{ __('Date') }}</option>
                <option value="amount">{{ __('Amount') }}</option>
            </select>
        </div>
        <div class="col-md-3">
            <select class="form-select" name="filters[${filterIndex}][operator]">
                <option value="=">=</option>
                <option value="!=">!=</option>
                <option value=">">></option>
                <option value="<"><</option>
                <option value=">=">>=</option>
                <option value="<="><=</option>
                <option value="like">{{ __('Contains') }}</option>
            </select>
        </div>
        <div class="col-md-4">
            <input type="text" class="form-control" name="filters[${filterIndex}][value]" 
                   placeholder="{{ __('Filter value...') }}">
        </div>
        <div class="col-md-1">
            <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeFilter(this)">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    `;
    container.appendChild(filterRow);
    filterIndex++;
}

function removeFilter(button) {
    const filterRow = button.closest('.filter-row');
    if (document.querySelectorAll('.filter-row').length > 1) {
        filterRow.remove();
    }
}

function loadTemplate(templateType) {
    const templates = {
        sales_summary: {
            name: 'Monthly Sales Summary',
            report_type: 'sales',
            category: 'operational',
            data_source: 'sales',
            chart_type: 'bar',
            group_by: 'date'
        },
        inventory_status: {
            name: 'Inventory Status Report',
            report_type: 'inventory',
            category: 'operational',
            data_source: 'inventory',
            chart_type: 'pie',
            group_by: 'category'
        },
        customer_analysis: {
            name: 'Customer Analysis Report',
            report_type: 'customer',
            category: 'analytical',
            data_source: 'customers',
            chart_type: 'line',
            group_by: 'date'
        },
        financial_overview: {
            name: 'Financial Overview',
            report_type: 'financial',
            category: 'financial',
            data_source: 'financial',
            chart_type: 'area',
            group_by: 'date'
        }
    };

    const template = templates[templateType];
    if (template) {
        document.querySelector('input[name="name"]').value = template.name;
        document.querySelector('select[name="report_type"]').value = template.report_type;
        document.querySelector('select[name="category"]').value = template.category;
        document.querySelector('select[name="data_source"]').value = template.data_source;
        document.querySelector('select[name="chart_type"]').value = template.chart_type;
        document.querySelector('select[name="group_by"]').value = template.group_by;
    }
}

function previewReport() {
    alert('Preview functionality will be implemented soon!');
}

// Add hover effects to template items
document.addEventListener('DOMContentLoaded', function() {
    const templateItems = document.querySelectorAll('.template-item');
    templateItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.backgroundColor = '#f8f9fa';
            this.style.transform = 'translateY(-1px)';
            this.style.transition = 'all 0.2s ease';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.backgroundColor = '';
            this.style.transform = 'translateY(0)';
        });
    });
});
</script>

<style>
.cursor-pointer {
    cursor: pointer;
}

.template-item:hover {
    background-color: #f8f9fa !important;
}
</style>
@endpush
