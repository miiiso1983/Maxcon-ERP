@extends('tenant.layouts.app')

@section('title', __('Import Suppliers'))
@section('page-title', __('Import Suppliers from Excel'))

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">{{ __('app.dashboard') }}</a></li>
<li class="breadcrumb-item"><a href="{{ route('suppliers.index') }}">{{ __('Suppliers') }}</a></li>
<li class="breadcrumb-item active">{{ __('Import') }}</li>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Import Results -->
    @if(session('import_results'))
        @php $results = session('import_results'); @endphp
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-success">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-check-circle {{ marginEnd('2') }}"></i>
                            {{ __('Import Results') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h3 class="text-success">{{ $results['imported'] }}</h3>
                                    <p class="mb-0">{{ __('Imported') }}</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h3 class="text-info">{{ $results['updated'] }}</h3>
                                    <p class="mb-0">{{ __('Updated') }}</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h3 class="text-warning">{{ $results['skipped'] }}</h3>
                                    <p class="mb-0">{{ __('Skipped') }}</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h3 class="text-primary">{{ $results['total'] }}</h3>
                                    <p class="mb-0">{{ __('Total Rows') }}</p>
                                </div>
                            </div>
                        </div>
                        
                        @if(!empty($results['errors']))
                            <div class="mt-3">
                                <h6 class="text-danger">{{ __('Errors:') }}</h6>
                                <ul class="list-unstyled">
                                    @foreach(array_slice($results['errors'], 0, 10) as $error)
                                        <li class="text-danger"><i class="fas fa-exclamation-triangle {{ marginEnd('2') }}"></i>{{ $error }}</li>
                                    @endforeach
                                    @if(count($results['errors']) > 10)
                                        <li class="text-muted">{{ __('And') }} {{ count($results['errors']) - 10 }} {{ __('more errors...') }}</li>
                                    @endif
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Import Instructions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle {{ marginEnd('2') }}"></i>
                        {{ __('Import Instructions') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-info">{{ __('Supported File Formats') }}</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-file-excel text-success {{ marginEnd('2') }}"></i>{{ __('Excel (.xlsx, .xls)') }}</li>
                                <li><i class="fas fa-file-csv text-primary {{ marginEnd('2') }}"></i>{{ __('CSV (.csv)') }}</li>
                            </ul>
                            
                            <h6 class="text-info mt-3">{{ __('File Requirements') }}</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success {{ marginEnd('2') }}"></i>{{ __('Maximum file size: 10MB') }}</li>
                                <li><i class="fas fa-check text-success {{ marginEnd('2') }}"></i>{{ __('Maximum 1000 suppliers per file') }}</li>
                                <li><i class="fas fa-check text-success {{ marginEnd('2') }}"></i>{{ __('First row must contain column headers') }}</li>
                                <li><i class="fas fa-check text-success {{ marginEnd('2') }}"></i>{{ __('Use exact column names as shown in template') }}</li>
                                <li><i class="fas fa-check text-success {{ marginEnd('2') }}"></i>{{ __('UTF-8 encoding recommended for Arabic text') }}</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-info">{{ __('Required Columns') }}</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>{{ __('Column Name') }}</th>
                                            <th>{{ __('Required') }}</th>
                                            <th>{{ __('Example') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><code>name (اسم المورد)</code></td>
                                            <td><span class="badge bg-danger">{{ __('Required') }}</span></td>
                                            <td>Medical Supplies Co.</td>
                                        </tr>
                                        <tr>
                                            <td><code>phone (رقم الهاتف)</code></td>
                                            <td><span class="badge bg-danger">{{ __('Required') }}</span></td>
                                            <td>+964 1 234 5678</td>
                                        </tr>
                                        <tr>
                                            <td><code>email (البريد الإلكتروني)</code></td>
                                            <td><span class="badge bg-secondary">{{ __('Optional') }}</span></td>
                                            <td>info@supplier.com</td>
                                        </tr>
                                        <tr>
                                            <td><code>address (العنوان)</code></td>
                                            <td><span class="badge bg-secondary">{{ __('Optional') }}</span></td>
                                            <td>Baghdad, Al-Karrada</td>
                                        </tr>
                                        <tr>
                                            <td><code>city (المدينة)</code></td>
                                            <td><span class="badge bg-secondary">{{ __('Optional') }}</span></td>
                                            <td>baghdad, basra, erbil</td>
                                        </tr>
                                        <tr>
                                            <td><code>supplier_type (نوع المورد)</code></td>
                                            <td><span class="badge bg-secondary">{{ __('Optional') }}</span></td>
                                            <td>distributor, manufacturer</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <h6 class="text-info">{{ __('Supplier Type Values') }}</h6>
                            <ul class="list-unstyled">
                                <li><code>manufacturer</code> - {{ __('Manufacturer') }} (مصنع)</li>
                                <li><code>distributor</code> - {{ __('Distributor') }} (موزع)</li>
                                <li><code>wholesaler</code> - {{ __('Wholesaler') }} (تاجر جملة)</li>
                                <li><code>retailer</code> - {{ __('Retailer') }} (تاجر تجزئة)</li>
                                <li><code>service_provider</code> - {{ __('Service Provider') }} (مقدم خدمات)</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-info">{{ __('Payment Terms Values') }}</h6>
                            <ul class="list-unstyled">
                                <li><code>cash</code> - {{ __('Cash') }} (نقدي)</li>
                                <li><code>net_7</code> - {{ __('Net 7 days') }} (7 أيام)</li>
                                <li><code>net_15</code> - {{ __('Net 15 days') }} (15 يوم)</li>
                                <li><code>net_30</code> - {{ __('Net 30 days') }} (30 يوم)</li>
                                <li><code>net_60</code> - {{ __('Net 60 days') }} (60 يوم)</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="alert alert-warning mt-3" role="alert">
                        <i class="fas fa-exclamation-triangle {{ marginEnd('2') }}"></i>
                        <strong>{{ __('Important') }}:</strong> {{ __('Column names must match exactly as shown in the template. Download the template below to ensure correct format.') }}
                    </div>
                    
                    <div class="mt-3">
                        <button class="btn btn-info btn-sm" onclick="downloadTemplate()">
                            <i class="fas fa-download {{ marginEnd('2') }}"></i>{{ __('Download Template with Column Names') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Import Form -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-upload {{ marginEnd('2') }}"></i>
                        {{ __('Upload Suppliers File') }}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('suppliers.import.process') }}" method="POST" enctype="multipart/form-data" id="import-form">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="file" class="form-label">{{ __('Select File') }} <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control @error('file') is-invalid @enderror" 
                                           id="file" name="file" accept=".xlsx,.xls,.csv" required>
                                    @error('file')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">{{ __('Supported formats: Excel (.xlsx, .xls) or CSV (.csv). Maximum size: 10MB') }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Import Options') }}</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="update_existing" name="update_existing" value="1">
                                        <label class="form-check-label" for="update_existing">
                                            {{ __('Update existing suppliers') }}
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="skip_duplicates" name="skip_duplicates" value="1" checked>
                                        <label class="form-check-label" for="skip_duplicates">
                                            {{ __('Skip duplicate entries') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left {{ marginEnd('2') }}"></i>{{ __('Back to Suppliers') }}
                                    </a>
                                    <button type="submit" class="btn btn-primary" id="import-btn">
                                        <i class="fas fa-upload {{ marginEnd('2') }}"></i>{{ __('Import Suppliers') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Download template function
function downloadTemplate() {
    window.location.href = '{{ route("templates.suppliers") }}';
}

// Form validation and progress
document.getElementById('import-form').addEventListener('submit', function(e) {
    const fileInput = document.getElementById('file');
    const importBtn = document.getElementById('import-btn');
    
    if (!fileInput.files.length) {
        e.preventDefault();
        alert('{{ __("Please select a file to import.") }}');
        return;
    }
    
    // Check file size (10MB = 10485760 bytes)
    if (fileInput.files[0].size > 10485760) {
        e.preventDefault();
        alert('{{ __("File size must be less than 10MB.") }}');
        return;
    }
    
    // Show loading state
    importBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>{{ __("Importing...") }}';
    importBtn.disabled = true;
});

// File input change handler
document.getElementById('file').addEventListener('change', function() {
    const file = this.files[0];
    if (file) {
        const fileName = file.name;
        const fileSize = (file.size / 1024 / 1024).toFixed(2); // Convert to MB
        
        // Show file info
        const fileInfo = document.createElement('div');
        fileInfo.className = 'alert alert-info mt-2';
        fileInfo.innerHTML = `
            <i class="fas fa-file {{ marginEnd('2') }}"></i>
            <strong>{{ __('Selected file') }}:</strong> ${fileName} (${fileSize} MB)
        `;
        
        // Remove existing file info
        const existingInfo = this.parentNode.querySelector('.alert-info');
        if (existingInfo) {
            existingInfo.remove();
        }
        
        this.parentNode.appendChild(fileInfo);
    }
});
</script>
@endpush

@push('styles')
<style>
.table th {
    border-top: none;
    font-weight: 600;
    font-size: 0.85rem;
}

.table td {
    font-size: 0.85rem;
}

code {
    background-color: #f8f9fa;
    padding: 2px 4px;
    border-radius: 3px;
    font-size: 0.8rem;
}

.card {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    border: 1px solid #e3e6f0;
}

.form-check-input:checked {
    background-color: #4e73df;
    border-color: #4e73df;
}

.alert {
    border-radius: 0.35rem;
}

.btn {
    border-radius: 0.35rem;
}
</style>
@endpush
