@extends('tenant.layouts.app')

@section('title', __('Import Products'))
@section('page-title', __('Import Products from Excel'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">{{ __('app.dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('inventory.index') }}">{{ __('app.inventory') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('inventory.products.index') }}">{{ __('Products') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Import Products') }}</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Import Instructions -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-info-circle {{ marginEnd('2') }}"></i>
                        {{ __('Import Instructions') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6 class="alert-heading">{{ __('Before You Start') }}</h6>
                        <p class="mb-2">{{ __('Please follow these guidelines:') }}</p>
                        <ul class="mb-0">
                            <li>{{ __('Use Excel (.xlsx) or CSV format') }}</li>
                            <li>{{ __('First row should contain headers') }}</li>
                            <li>{{ __('Required fields: Name, SKU, Price') }}</li>
                            <li>{{ __('Maximum file size: 10MB') }}</li>
                            <li>{{ __('Maximum 1000 products per file') }}</li>
                        </ul>
                    </div>

                    <h6>{{ __('Required Columns') }}</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('Column') }}</th>
                                    <th>{{ __('Required') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><code>name</code></td>
                                    <td><span class="badge bg-danger">{{ __('Yes') }}</span></td>
                                </tr>
                                <tr>
                                    <td><code>sku</code></td>
                                    <td><span class="badge bg-danger">{{ __('Yes') }}</span></td>
                                </tr>
                                <tr>
                                    <td><code>selling_price</code></td>
                                    <td><span class="badge bg-danger">{{ __('Yes') }}</span></td>
                                </tr>
                                <tr>
                                    <td><code>cost_price</code></td>
                                    <td><span class="badge bg-secondary">{{ __('No') }}</span></td>
                                </tr>
                                <tr>
                                    <td><code>barcode</code></td>
                                    <td><span class="badge bg-secondary">{{ __('No') }}</span></td>
                                </tr>
                                <tr>
                                    <td><code>category</code></td>
                                    <td><span class="badge bg-secondary">{{ __('No') }}</span></td>
                                </tr>
                                <tr>
                                    <td><code>description</code></td>
                                    <td><span class="badge bg-secondary">{{ __('No') }}</span></td>
                                </tr>
                                <tr>
                                    <td><code>quantity</code></td>
                                    <td><span class="badge bg-secondary">{{ __('No') }}</span></td>
                                </tr>
                                <tr>
                                    <td><code>min_quantity</code></td>
                                    <td><span class="badge bg-secondary">{{ __('No') }}</span></td>
                                </tr>
                                <tr>
                                    <td><code>unit</code></td>
                                    <td><span class="badge bg-secondary">{{ __('No') }}</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        <a href="{{ route('inventory.products.download-template') }}" class="btn btn-outline-success btn-sm w-100">
                            <i class="fas fa-download {{ marginEnd('2') }}"></i>{{ __('Download Template') }}
                        </a>
                    </div>
                </div>
            </div>

            <!-- Sample Data -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-eye {{ marginEnd('2') }}"></i>
                        {{ __('Sample Data') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>name</th>
                                    <th>sku</th>
                                    <th>selling_price</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Paracetamol 500mg</td>
                                    <td>PAR001</td>
                                    <td>5000</td>
                                </tr>
                                <tr>
                                    <td>Amoxicillin 250mg</td>
                                    <td>AMX001</td>
                                    <td>15000</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Import Form -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-upload {{ marginEnd('2') }}"></i>
                        {{ __('Upload Excel File') }}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('inventory.products.import') }}" method="POST" enctype="multipart/form-data" id="importForm">
                        @csrf
                        
                        <!-- File Upload -->
                        <div class="mb-4">
                            <label for="excel_file" class="form-label">{{ __('Select Excel File') }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="file" class="form-control @error('excel_file') is-invalid @enderror" 
                                       id="excel_file" name="excel_file" accept=".xlsx,.xls,.csv" required>
                                <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('excel_file').click()">
                                    <i class="fas fa-folder-open"></i>
                                </button>
                            </div>
                            @error('excel_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">{{ __('Supported formats: .xlsx, .xls, .csv (Max: 10MB)') }}</div>
                        </div>

                        <!-- Import Options -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">{{ __('Import Options') }}</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="skip_duplicates" 
                                                       name="skip_duplicates" value="1" checked>
                                                <label class="form-check-label" for="skip_duplicates">
                                                    {{ __('Skip Duplicate SKUs') }}
                                                </label>
                                            </div>
                                            <small class="text-muted">{{ __('Skip products with existing SKUs') }}</small>
                                        </div>
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="update_existing" 
                                                       name="update_existing" value="1">
                                                <label class="form-check-label" for="update_existing">
                                                    {{ __('Update Existing Products') }}
                                                </label>
                                            </div>
                                            <small class="text-muted">{{ __('Update products if SKU already exists') }}</small>
                                        </div>
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="validate_only" 
                                                       name="validate_only" value="1">
                                                <label class="form-check-label" for="validate_only">
                                                    {{ __('Validate Only (Dry Run)') }}
                                                </label>
                                            </div>
                                            <small class="text-muted">{{ __('Check for errors without importing') }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">{{ __('Default Values') }}</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="default_category" class="form-label">{{ __('Default Category') }}</label>
                                            <select class="form-select" id="default_category" name="default_category"
                                                    data-placeholder="{{ __('No Default') }}">
                                                <option value="">{{ __('No Default') }}</option>
                                                <option value="medicines">{{ __('Medicines') }}</option>
                                                <option value="medical-devices">{{ __('Medical Devices') }}</option>
                                                <option value="supplements">{{ __('Supplements') }}</option>
                                                <option value="equipment">{{ __('Medical Equipment') }}</option>
                                                <option value="consumables">{{ __('Medical Consumables') }}</option>
                                                <option value="laboratory">{{ __('Laboratory Supplies') }}</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="default_unit" class="form-label">{{ __('Default Unit') }}</label>
                                            <select class="form-select" id="default_unit" name="default_unit"
                                                    data-placeholder="{{ __('No Default') }}">
                                                <option value="">{{ __('No Default') }}</option>
                                                <option value="piece">{{ __('Piece') }}</option>
                                                <option value="box">{{ __('Box') }}</option>
                                                <option value="bottle">{{ __('Bottle') }}</option>
                                                <option value="vial">{{ __('Vial') }}</option>
                                                <option value="pack">{{ __('Pack') }}</option>
                                                <option value="tablet">{{ __('Tablet') }}</option>
                                                <option value="capsule">{{ __('Capsule') }}</option>
                                                <option value="ml">{{ __('Milliliter') }}</option>
                                                <option value="gram">{{ __('Gram') }}</option>
                                                <option value="kg">{{ __('Kilogram') }}</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="default_active" 
                                                       name="default_active" value="1" checked>
                                                <label class="form-check-label" for="default_active">
                                                    {{ __('Set as Active by Default') }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- File Preview -->
                        <div id="file-preview" class="mb-4" style="display: none;">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-file-excel {{ marginEnd('2') }}"></i>
                                        {{ __('File Preview') }}
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div id="preview-content">
                                        <!-- Preview will be loaded here -->
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-end">
                            <a href="{{ route('inventory.products.index') }}" class="btn btn-outline-secondary {{ marginEnd('2') }}">
                                <i class="fas fa-times {{ marginEnd('2') }}"></i>{{ __('Cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary" id="importBtn">
                                <i class="fas fa-upload {{ marginEnd('2') }}"></i>{{ __('Import Products') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Import Progress -->
            <div id="import-progress" class="card mt-3" style="display: none;">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-spinner fa-spin {{ marginEnd('2') }}"></i>
                        {{ __('Import Progress') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="progress mb-3">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                             role="progressbar" style="width: 0%" id="progress-bar"></div>
                    </div>
                    <div id="progress-text">{{ __('Preparing import...') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('excel_file').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        // Show file info
        const fileInfo = `
            <div class="alert alert-info">
                <strong>${file.name}</strong><br>
                Size: ${(file.size / 1024 / 1024).toFixed(2)} MB<br>
                Type: ${file.type || 'Unknown'}
            </div>
        `;
        document.getElementById('preview-content').innerHTML = fileInfo;
        document.getElementById('file-preview').style.display = 'block';
    }
});

document.getElementById('importForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const progressDiv = document.getElementById('import-progress');
    const progressBar = document.getElementById('progress-bar');
    const progressText = document.getElementById('progress-text');
    const importBtn = document.getElementById('importBtn');
    
    // Show progress
    progressDiv.style.display = 'block';
    importBtn.disabled = true;
    importBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>{{ __("Importing...") }}';
    
    // Simulate progress (in real implementation, use WebSocket or polling)
    let progress = 0;
    const progressInterval = setInterval(() => {
        progress += Math.random() * 20;
        if (progress > 90) progress = 90;
        
        progressBar.style.width = progress + '%';
        progressText.textContent = `{{ __("Processing...") }} ${Math.round(progress)}%`;
    }, 500);
    
    // Submit form
    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        clearInterval(progressInterval);
        progressBar.style.width = '100%';
        
        if (data.success) {
            progressText.innerHTML = `
                <div class="alert alert-success">
                    <strong>{{ __("Import Completed!") }}</strong><br>
                    {{ __("Imported") }}: ${data.imported || 0} {{ __("products") }}<br>
                    {{ __("Skipped") }}: ${data.skipped || 0} {{ __("products") }}<br>
                    {{ __("Errors") }}: ${data.errors || 0} {{ __("products") }}
                </div>
            `;
            
            setTimeout(() => {
                window.location.href = '{{ route("inventory.products.index") }}';
            }, 3000);
        } else {
            progressText.innerHTML = `
                <div class="alert alert-danger">
                    <strong>{{ __("Import Failed!") }}</strong><br>
                    ${data.message || '{{ __("Unknown error occurred") }}'}
                </div>
            `;
        }
    })
    .catch(error => {
        clearInterval(progressInterval);
        progressText.innerHTML = `
            <div class="alert alert-danger">
                <strong>{{ __("Error!") }}</strong><br>
                ${error.message || '{{ __("Network error occurred") }}'}
            </div>
        `;
    })
    .finally(() => {
        importBtn.disabled = false;
        importBtn.innerHTML = '<i class="fas fa-upload me-2"></i>{{ __("Import Products") }}';
    });
});

// Handle conflicting options
document.getElementById('skip_duplicates').addEventListener('change', function() {
    if (this.checked) {
        document.getElementById('update_existing').checked = false;
    }
});

document.getElementById('update_existing').addEventListener('change', function() {
    if (this.checked) {
        document.getElementById('skip_duplicates').checked = false;
    }
});
</script>
@endpush
