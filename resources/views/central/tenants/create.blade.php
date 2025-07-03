@extends('central.layouts.master')

@section('title', __('Create New Tenant'))
@section('page-title', __('Create New Tenant'))

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-plus text-primary me-2"></i>
                        {{ __('Create New Tenant') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Set up a new system administrator with custom limits and permissions') }}</p>
                </div>
                <div>
                    <a href="{{ route('central.tenants.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> {{ __('Back to Tenants') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('central.tenants.store') }}" method="POST" id="createTenantForm">
        @csrf
        
        <div class="row">
            <!-- Main Form -->
            <div class="col-lg-8">
                <!-- Company Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-building me-2"></i>{{ __('Company Information') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Company Name') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       name="name" value="{{ old('name') }}" required
                                       placeholder="{{ __('Enter company name') }}">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Company Email') }} <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       name="email" value="{{ old('email') }}" required
                                       placeholder="{{ __('company@example.com') }}">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Phone Number') }}</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                       name="phone" value="{{ old('phone') }}"
                                       placeholder="{{ __('+964 XXX XXX XXXX') }}">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('License Expiry Date') }} <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('license_expires_at') is-invalid @enderror" 
                                       name="license_expires_at" value="{{ old('license_expires_at', now()->addYear()->format('Y-m-d')) }}" 
                                       min="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
                                @error('license_expires_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">{{ __('Company Address') }}</label>
                                <textarea class="form-control @error('address') is-invalid @enderror" 
                                          name="address" rows="3" placeholder="{{ __('Enter company address') }}">{{ old('address') }}</textarea>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Administrator -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-user-shield me-2"></i>{{ __('System Administrator') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Admin Name') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('admin_name') is-invalid @enderror" 
                                       name="admin_name" value="{{ old('admin_name') }}" required
                                       placeholder="{{ __('Enter admin full name') }}">
                                @error('admin_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Admin Email') }} <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('admin_email') is-invalid @enderror" 
                                       name="admin_email" value="{{ old('admin_email') }}" required
                                       placeholder="{{ __('admin@company.com') }}">
                                @error('admin_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Admin Password') }} <span class="text-danger">*</span></label>
                                <input type="password" class="form-control @error('admin_password') is-invalid @enderror" 
                                       name="admin_password" required minlength="8"
                                       placeholder="{{ __('Enter secure password') }}">
                                @error('admin_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Confirm Password') }} <span class="text-danger">*</span></label>
                                <input type="password" class="form-control @error('admin_password_confirmation') is-invalid @enderror" 
                                       name="admin_password_confirmation" required minlength="8"
                                       placeholder="{{ __('Confirm password') }}">
                                @error('admin_password_confirmation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- License & Limits -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-key me-2"></i>{{ __('License & Limits') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('License Type') }} <span class="text-danger">*</span></label>
                                <select class="form-select @error('license_type') is-invalid @enderror" 
                                        name="license_type" id="licenseType" required>
                                    <option value="">{{ __('Select license type...') }}</option>
                                    @foreach(\App\Models\Tenant::getLicenseTypes() as $key => $type)
                                        <option value="{{ $key }}" {{ old('license_type') == $key ? 'selected' : '' }}
                                                data-limits="{{ json_encode($type) }}">
                                            {{ $type['name'] }} - ${{ number_format($type['monthly_fee']) }}/{{ __('month') }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('license_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Monthly Fee') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control @error('monthly_fee') is-invalid @enderror" 
                                           name="monthly_fee" value="{{ old('monthly_fee') }}" 
                                           min="0" step="0.01" id="monthlyFee">
                                </div>
                                @error('monthly_fee')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Custom Limits -->
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">{{ __('Max Users') }}</label>
                                <input type="number" class="form-control @error('max_users') is-invalid @enderror" 
                                       name="max_users" value="{{ old('max_users') }}" 
                                       min="1" id="maxUsers" placeholder="Auto">
                                @error('max_users')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">{{ __('Max Warehouses') }}</label>
                                <input type="number" class="form-control @error('max_warehouses') is-invalid @enderror" 
                                       name="max_warehouses" value="{{ old('max_warehouses') }}" 
                                       min="1" id="maxWarehouses" placeholder="Auto">
                                @error('max_warehouses')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">{{ __('Max Products') }}</label>
                                <input type="number" class="form-control @error('max_products') is-invalid @enderror" 
                                       name="max_products" value="{{ old('max_products') }}" 
                                       min="1" id="maxProducts" placeholder="Auto">
                                @error('max_products')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">{{ __('Storage (MB)') }}</label>
                                <input type="number" class="form-control @error('max_storage') is-invalid @enderror" 
                                       name="max_storage" value="{{ old('max_storage') }}" 
                                       min="100" id="maxStorage" placeholder="Auto">
                                @error('max_storage')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Enabled Modules -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-puzzle-piece me-2"></i>{{ __('Enabled Modules') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row" id="modulesContainer">
                            @foreach(\App\Models\Tenant::getAvailableModules() as $key => $name)
                            <div class="col-md-4 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input module-checkbox" type="checkbox" 
                                           name="enabled_modules[]" value="{{ $key }}" 
                                           id="module_{{ $key }}"
                                           {{ in_array($key, old('enabled_modules', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="module_{{ $key }}">
                                        {{ $name }}
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('central.tenants.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> {{ __('Cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> {{ __('Create Tenant') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- License Preview -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">{{ __('License Preview') }}</h6>
                    </div>
                    <div class="card-body" id="licensePreview">
                        <div class="text-center text-muted">
                            <i class="fas fa-key fa-3x mb-3"></i>
                            <p>{{ __('Select a license type to see details') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Quick Setup Guide -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">{{ __('Quick Setup Guide') }}</h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <h6>{{ __('Setup Steps:') }}</h6>
                            <ol class="mb-0 small">
                                <li>{{ __('Enter company information') }}</li>
                                <li>{{ __('Create system administrator account') }}</li>
                                <li>{{ __('Choose license type and limits') }}</li>
                                <li>{{ __('Select enabled modules') }}</li>
                                <li>{{ __('Review and create tenant') }}</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <!-- License Types Info -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">{{ __('License Types') }}</h6>
                    </div>
                    <div class="card-body">
                        @foreach(\App\Models\Tenant::getLicenseTypes() as $key => $type)
                        <div class="mb-3 p-2 border rounded">
                            <h6 class="text-{{ $key == 'enterprise' ? 'success' : ($key == 'premium' ? 'warning' : 'info') }}">
                                {{ $type['name'] }}
                            </h6>
                            <small class="text-muted">
                                ${{ number_format($type['monthly_fee']) }}/{{ __('month') }}<br>
                                {{ $type['max_users'] == -1 ? __('Unlimited') : $type['max_users'] }} {{ __('users') }}<br>
                                {{ $type['max_warehouses'] == -1 ? __('Unlimited') : $type['max_warehouses'] }} {{ __('warehouses') }}
                            </small>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const licenseTypeSelect = document.getElementById('licenseType');
    const licensePreview = document.getElementById('licensePreview');
    const monthlyFeeInput = document.getElementById('monthlyFee');
    const maxUsersInput = document.getElementById('maxUsers');
    const maxWarehousesInput = document.getElementById('maxWarehouses');
    const maxProductsInput = document.getElementById('maxProducts');
    const maxStorageInput = document.getElementById('maxStorage');
    const moduleCheckboxes = document.querySelectorAll('.module-checkbox');

    licenseTypeSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        
        if (selectedOption.value) {
            const limits = JSON.parse(selectedOption.dataset.limits);
            
            // Update preview
            licensePreview.innerHTML = `
                <div class="text-center">
                    <div class="badge bg-primary fs-6 mb-3">${limits.name}</div>
                    <h4 class="text-primary">$${limits.monthly_fee.toLocaleString()}</h4>
                    <p class="text-muted">{{ __('per month') }}</p>
                    
                    <hr>
                    
                    <div class="row text-center">
                        <div class="col-6 mb-2">
                            <div class="h6 mb-0">${limits.max_users == -1 ? '∞' : limits.max_users}</div>
                            <small class="text-muted">{{ __('Users') }}</small>
                        </div>
                        <div class="col-6 mb-2">
                            <div class="h6 mb-0">${limits.max_warehouses == -1 ? '∞' : limits.max_warehouses}</div>
                            <small class="text-muted">{{ __('Warehouses') }}</small>
                        </div>
                        <div class="col-6 mb-2">
                            <div class="h6 mb-0">${limits.max_products == -1 ? '∞' : limits.max_products}</div>
                            <small class="text-muted">{{ __('Products') }}</small>
                        </div>
                        <div class="col-6 mb-2">
                            <div class="h6 mb-0">${limits.max_storage == -1 ? '∞' : limits.max_storage + ' MB'}</div>
                            <small class="text-muted">{{ __('Storage') }}</small>
                        </div>
                    </div>
                </div>
            `;
            
            // Update form fields
            monthlyFeeInput.value = limits.monthly_fee;
            maxUsersInput.placeholder = limits.max_users == -1 ? 'Unlimited' : limits.max_users;
            maxWarehousesInput.placeholder = limits.max_warehouses == -1 ? 'Unlimited' : limits.max_warehouses;
            maxProductsInput.placeholder = limits.max_products == -1 ? 'Unlimited' : limits.max_products;
            maxStorageInput.placeholder = limits.max_storage == -1 ? 'Unlimited' : limits.max_storage;
            
            // Update modules
            moduleCheckboxes.forEach(checkbox => {
                if (limits.modules === 'all' || limits.modules.includes(checkbox.value)) {
                    checkbox.checked = true;
                } else {
                    checkbox.checked = false;
                }
            });
        } else {
            licensePreview.innerHTML = `
                <div class="text-center text-muted">
                    <i class="fas fa-key fa-3x mb-3"></i>
                    <p>{{ __('Select a license type to see details') }}</p>
                </div>
            `;
            monthlyFeeInput.value = '';
        }
    });

    // Form validation
    document.getElementById('createTenantForm').addEventListener('submit', function(e) {
        const password = document.querySelector('input[name="admin_password"]').value;
        const confirmPassword = document.querySelector('input[name="admin_password_confirmation"]').value;
        
        if (password !== confirmPassword) {
            e.preventDefault();
            alert('{{ __("Passwords do not match") }}');
            return;
        }
        
        if (password.length < 8) {
            e.preventDefault();
            alert('{{ __("Password must be at least 8 characters long") }}');
            return;
        }
    });
});
</script>
@endpush
