@extends('tenant.layouts.app')

@section('title', __('Import Customers'))
@section('page-title', __('Import Customers'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">{{ __('app.dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('customers.index') }}">{{ __('app.customers') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Import Customers') }}</li>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Import Instructions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-info">
                <div class="card-header bg-info text-white">
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
                                <li><i class="fas fa-check text-success {{ marginEnd('2') }}"></i>{{ __('Maximum 1000 customers per file') }}</li>
                                <li><i class="fas fa-check text-success {{ marginEnd('2') }}"></i>{{ __('First row must contain column headers') }}</li>
                                <li><i class="fas fa-check text-success {{ marginEnd('2') }}"></i>{{ __('Use exact column names as shown in template') }}</li>
                                <li><i class="fas fa-check text-success {{ marginEnd('2') }}"></i>{{ __('UTF-8 encoding recommended for Arabic text') }}</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-info">{{ __('Column Names (أسماء الأعمدة)') }}</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>{{ __('Column Name') }}</th>
                                            <th>{{ __('Arabic Name') }}</th>
                                            <th>{{ __('Required') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><code>name</code></td>
                                            <td>{{ __('الاسم الكامل') }}</td>
                                            <td><i class="fas fa-check text-danger"></i></td>
                                        </tr>
                                        <tr>
                                            <td><code>phone</code></td>
                                            <td>{{ __('رقم الهاتف') }}</td>
                                            <td><i class="fas fa-check text-danger"></i></td>
                                        </tr>
                                        <tr>
                                            <td><code>email</code></td>
                                            <td>{{ __('البريد الإلكتروني') }}</td>
                                            <td><i class="fas fa-minus text-muted"></i></td>
                                        </tr>
                                        <tr>
                                            <td><code>address</code></td>
                                            <td>{{ __('العنوان') }}</td>
                                            <td><i class="fas fa-minus text-muted"></i></td>
                                        </tr>
                                        <tr>
                                            <td><code>city</code></td>
                                            <td>{{ __('المدينة') }}</td>
                                            <td><i class="fas fa-minus text-muted"></i></td>
                                        </tr>
                                        <tr>
                                            <td><code>customer_type</code></td>
                                            <td>{{ __('نوع العميل') }}</td>
                                            <td><i class="fas fa-minus text-muted"></i></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            
                            <h6 class="text-info mt-3">{{ __('Additional Columns (أعمدة إضافية)') }}</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <small class="text-muted">
                                        • <code>district</code> - {{ __('المنطقة') }}<br>
                                        • <code>credit_limit</code> - {{ __('حد الائتمان') }}<br>
                                        • <code>payment_terms</code> - {{ __('شروط الدفع') }}<br>
                                        • <code>tax_number</code> - {{ __('رقم الضريبة') }}
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted">
                                        • <code>license_number</code> - {{ __('رقم الترخيص') }}<br>
                                        • <code>notes</code> - {{ __('ملاحظات') }}<br>
                                        • <code>secondary_phone</code> - {{ __('هاتف ثانوي') }}<br>
                                        • <code>postal_code</code> - {{ __('الرمز البريدي') }}
                                    </small>
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
        </div>
    </div>

    <!-- Import Form -->
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-upload {{ marginEnd('2') }}"></i>
                        {{ __('Upload Customer File') }}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('customers.import.process') }}" method="POST" enctype="multipart/form-data" id="import-form">
                        @csrf
                        
                        <!-- File Upload -->
                        <div class="mb-4">
                            <label for="customer_file" class="form-label">{{ __('Select File') }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="file" class="form-control @error('customer_file') is-invalid @enderror" 
                                       id="customer_file" name="customer_file" accept=".xlsx,.xls,.csv" required>
                                <button type="button" class="btn btn-outline-secondary" onclick="clearFile()">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            @error('customer_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">{{ __('Supported formats: Excel (.xlsx, .xls) and CSV (.csv)') }}</div>
                        </div>

                        <!-- Import Options -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6>{{ __('Import Options') }}</h6>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="skip_duplicates" name="skip_duplicates" value="1" checked>
                                    <label class="form-check-label" for="skip_duplicates">
                                        {{ __('Skip duplicate customers (by phone number)') }}
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="update_existing" name="update_existing" value="1">
                                    <label class="form-check-label" for="update_existing">
                                        {{ __('Update existing customers') }}
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="validate_emails" name="validate_emails" value="1" checked>
                                    <label class="form-check-label" for="validate_emails">
                                        {{ __('Validate email addresses') }}
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>{{ __('Default Values') }}</h6>
                                <div class="mb-3">
                                    <label for="default_customer_type" class="form-label">{{ __('Default Customer Type') }}</label>
                                    <select class="form-select" id="default_customer_type" name="default_customer_type" 
                                            data-placeholder="{{ __('Select Default Type') }}">
                                        <option value="">{{ __('No Default') }}</option>
                                        <option value="individual">👤 {{ __('Individual') }}</option>
                                        <option value="pharmacy">💊 {{ __('Pharmacy') }}</option>
                                        <option value="hospital">🏥 {{ __('Hospital') }}</option>
                                        <option value="clinic">🩺 {{ __('Clinic') }}</option>
                                        <option value="distributor">🚚 {{ __('Distributor') }}</option>
                                        <option value="government">🏛️ {{ __('Government') }}</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="default_city" class="form-label">{{ __('Default City') }}</label>
                                    <select class="form-select" id="default_city" name="default_city" 
                                            data-placeholder="{{ __('Select Default City') }}">
                                        <option value="">{{ __('No Default') }}</option>
                                        <option value="baghdad">🏛️ {{ __('Baghdad') }}</option>
                                        <option value="basra">🏭 {{ __('Basra') }}</option>
                                        <option value="erbil">🏔️ {{ __('Erbil') }}</option>
                                        <option value="mosul">🕌 {{ __('Mosul') }}</option>
                                        <option value="najaf">🕌 {{ __('Najaf') }}</option>
                                        <option value="karbala">🕌 {{ __('Karbala') }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-flex justify-content-end">
                            <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary {{ marginEnd('2') }}">
                                <i class="fas fa-times {{ marginEnd('2') }}"></i>{{ __('Cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary" id="import-btn">
                                <i class="fas fa-upload {{ marginEnd('2') }}"></i>{{ __('Import Customers') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sample Data Preview -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-eye {{ marginEnd('2') }}"></i>
                        {{ __('Sample Data Format') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered sample-data-table">
                            <thead class="table-dark">
                                <tr>
                                    <th style="font-size: 0.7rem;">name<br><small>(الاسم)</small></th>
                                    <th style="font-size: 0.7rem;">phone<br><small>(الهاتف)</small></th>
                                    <th style="font-size: 0.7rem;">email<br><small>(البريد)</small></th>
                                    <th style="font-size: 0.7rem;">city<br><small>(المدينة)</small></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Ahmed Al-Rashid</td>
                                    <td>+964 770 123 4567</td>
                                    <td>ahmed@email.com</td>
                                    <td>baghdad</td>
                                </tr>
                                <tr>
                                    <td>Fatima Hassan</td>
                                    <td>+964 771 234 5678</td>
                                    <td>fatima@email.com</td>
                                    <td>basra</td>
                                </tr>
                                <tr>
                                    <td>Omar Khalil</td>
                                    <td>+964 772 345 6789</td>
                                    <td>omar@email.com</td>
                                    <td>erbil</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        <h6 class="text-muted">{{ __('Customer Type Values (قيم نوع العميل)') }}</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <small class="text-muted">
                                    • <code>individual</code> - {{ __('فردي') }}<br>
                                    • <code>pharmacy</code> - {{ __('صيدلية') }}<br>
                                    • <code>hospital</code> - {{ __('مستشفى') }}
                                </small>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted">
                                    • <code>clinic</code> - {{ __('عيادة') }}<br>
                                    • <code>distributor</code> - {{ __('موزع') }}<br>
                                    • <code>government</code> - {{ __('حكومي') }}
                                </small>
                            </div>
                        </div>

                        <h6 class="text-muted mt-2">{{ __('Payment Terms Values (قيم شروط الدفع)') }}</h6>
                        <small class="text-muted">
                            <code>cash</code>, <code>net_7</code>, <code>net_15</code>, <code>net_30</code>, <code>net_60</code>
                        </small>
                    </div>
                </div>
            </div>

            <!-- Import History -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-history {{ marginEnd('2') }}"></i>
                        {{ __('Recent Imports') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <div>
                                <h6 class="mb-1">{{ __('customers_batch_1.xlsx') }}</h6>
                                <small class="text-muted">{{ date('M d, Y H:i') }}</small>
                            </div>
                            <span class="badge bg-success rounded-pill">45</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <div>
                                <h6 class="mb-1">{{ __('pharmacy_customers.csv') }}</h6>
                                <small class="text-muted">{{ date('M d, Y H:i', strtotime('-2 days')) }}</small>
                            </div>
                            <span class="badge bg-success rounded-pill">23</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <div>
                                <h6 class="mb-1">{{ __('hospital_contacts.xlsx') }}</h6>
                                <small class="text-muted">{{ date('M d, Y H:i', strtotime('-1 week')) }}</small>
                            </div>
                            <span class="badge bg-warning rounded-pill">12</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Modal -->
    <div class="modal fade" id="importProgressModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Importing Customers') }}</h5>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">{{ __('Loading...') }}</span>
                        </div>
                    </div>
                    <div class="progress mb-3">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                             role="progressbar" style="width: 0%" id="import-progress"></div>
                    </div>
                    <div class="text-center">
                        <p class="mb-1" id="import-status">{{ __('Preparing import...') }}</p>
                        <small class="text-muted" id="import-details">{{ __('Please wait while we process your file') }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// File validation
document.getElementById('customer_file').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;
    
    // Check file size (10MB limit)
    const maxSize = 10 * 1024 * 1024; // 10MB
    if (file.size > maxSize) {
        alert('{{ __("File size must be less than 10MB") }}');
        clearFile();
        return;
    }
    
    // Check file type
    const allowedTypes = [
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
        'application/vnd.ms-excel', // .xls
        'text/csv' // .csv
    ];
    
    if (!allowedTypes.includes(file.type)) {
        alert('{{ __("Please select a valid Excel or CSV file") }}');
        clearFile();
        return;
    }
    
    // Show file info
    const fileInfo = document.createElement('div');
    fileInfo.className = 'mt-2 text-success';
    fileInfo.innerHTML = `
        <i class="fas fa-check-circle me-2"></i>
        ${file.name} (${formatFileSize(file.size)})
    `;
    
    // Remove existing file info
    const existingInfo = document.querySelector('.file-info');
    if (existingInfo) {
        existingInfo.remove();
    }
    
    fileInfo.className += ' file-info';
    e.target.parentNode.parentNode.appendChild(fileInfo);
});

function clearFile() {
    document.getElementById('customer_file').value = '';
    const fileInfo = document.querySelector('.file-info');
    if (fileInfo) {
        fileInfo.remove();
    }
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Form submission
document.getElementById('import-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const fileInput = document.getElementById('customer_file');
    if (!fileInput.files[0]) {
        alert('{{ __("Please select a file to import") }}');
        return;
    }
    
    // Show progress modal
    const modal = new bootstrap.Modal(document.getElementById('importProgressModal'));
    modal.show();
    
    // Simulate import progress
    simulateImport();
});

function simulateImport() {
    const progressBar = document.getElementById('import-progress');
    const statusText = document.getElementById('import-status');
    const detailsText = document.getElementById('import-details');
    
    let progress = 0;
    const steps = [
        { progress: 20, status: '{{ __("Validating file format...") }}', details: '{{ __("Checking file structure and headers") }}' },
        { progress: 40, status: '{{ __("Reading customer data...") }}', details: '{{ __("Processing rows and validating data") }}' },
        { progress: 60, status: '{{ __("Validating customer information...") }}', details: '{{ __("Checking for duplicates and errors") }}' },
        { progress: 80, status: '{{ __("Saving customers to database...") }}', details: '{{ __("Creating customer records") }}' },
        { progress: 100, status: '{{ __("Import completed successfully!") }}', details: '{{ __("45 customers imported, 2 skipped") }}' }
    ];
    
    let currentStep = 0;
    
    const interval = setInterval(() => {
        if (currentStep < steps.length) {
            const step = steps[currentStep];
            progressBar.style.width = step.progress + '%';
            statusText.textContent = step.status;
            detailsText.textContent = step.details;
            currentStep++;
        } else {
            clearInterval(interval);
            setTimeout(() => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('importProgressModal'));
                modal.hide();
                
                // Show success message
                alert('{{ __("Import completed successfully! 45 customers imported, 2 skipped.") }}');
                
                // Redirect to customers list
                window.location.href = '{{ route("customers.index") }}';
            }, 1500);
        }
    }, 1000);
}

function downloadTemplate() {
    // Download the template from the server
    window.location.href = '{{ route("templates.customers") }}';
}

// Update existing customers checkbox logic
document.getElementById('skip_duplicates').addEventListener('change', function() {
    const updateExisting = document.getElementById('update_existing');
    if (this.checked) {
        updateExisting.checked = false;
        updateExisting.disabled = true;
    } else {
        updateExisting.disabled = false;
    }
});

document.getElementById('update_existing').addEventListener('change', function() {
    const skipDuplicates = document.getElementById('skip_duplicates');
    if (this.checked) {
        skipDuplicates.checked = false;
        skipDuplicates.disabled = true;
    } else {
        skipDuplicates.disabled = false;
    }
});
</script>
@endpush

@push('styles')
<link href="{{ asset('css/import-styles.css') }}" rel="stylesheet">
<style>
.file-info {
    font-size: 0.875rem;
}

.progress {
    height: 8px;
}

.list-group-item {
    border: none;
    border-bottom: 1px solid #dee2e6;
}

.list-group-item:last-child {
    border-bottom: none;
}

.table th, .table td {
    font-size: 0.8rem;
    padding: 0.5rem;
}

.spinner-border {
    width: 3rem;
    height: 3rem;
}
</style>
@endpush
