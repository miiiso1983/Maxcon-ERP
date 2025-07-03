@extends('tenant.layouts.app')

@section('title', __('Create New Customer'))
@section('page-title', __('Create New Customer'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">{{ __('app.dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('customers.index') }}">{{ __('app.customers') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Create New Customer') }}</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user-plus {{ marginEnd('2') }}"></i>
                        {{ __('Create New Customer') }}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('customers.store') }}" method="POST" id="customer-form">
                        @csrf
                        
                        <div class="row">
                            <!-- Basic Information -->
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">{{ __('Basic Information') }}</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">{{ __('Full Name') }} <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                                   id="name" name="name" value="{{ old('name') }}" required 
                                                   placeholder="{{ __('Enter customer full name') }}">
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="email" class="form-label">{{ __('Email Address') }}</label>
                                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                                   id="email" name="email" value="{{ old('email') }}" 
                                                   placeholder="{{ __('Enter email address') }}">
                                            @error('email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="phone" class="form-label">{{ __('Phone Number') }} <span class="text-danger">*</span></label>
                                            <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                                   id="phone" name="phone" value="{{ old('phone') }}" required 
                                                   placeholder="{{ __('Enter phone number') }}">
                                            @error('phone')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="secondary_phone" class="form-label">{{ __('Secondary Phone') }}</label>
                                            <input type="tel" class="form-control @error('secondary_phone') is-invalid @enderror" 
                                                   id="secondary_phone" name="secondary_phone" value="{{ old('secondary_phone') }}" 
                                                   placeholder="{{ __('Enter secondary phone number') }}">
                                            @error('secondary_phone')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="date_of_birth" class="form-label">{{ __('Date of Birth') }}</label>
                                            <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" 
                                                   id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}">
                                            @error('date_of_birth')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="gender" class="form-label">{{ __('Gender') }}</label>
                                            <select class="form-select @error('gender') is-invalid @enderror" 
                                                    id="gender" name="gender" data-placeholder="{{ __('Select Gender') }}">
                                                <option value="">{{ __('Select Gender') }}</option>
                                                <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>👨 {{ __('Male') }}</option>
                                                <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>👩 {{ __('Female') }}</option>
                                            </select>
                                            @error('gender')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Address Information -->
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">{{ __('Address Information') }}</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="address" class="form-label">{{ __('Street Address') }}</label>
                                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                                      id="address" name="address" rows="3" 
                                                      placeholder="{{ __('Enter street address') }}">{{ old('address') }}</textarea>
                                            @error('address')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="city" class="form-label">{{ __('City') }}</label>
                                            <select class="form-select @error('city') is-invalid @enderror" 
                                                    id="city" name="city" data-placeholder="{{ __('Select City') }}">
                                                <option value="">{{ __('Select City') }}</option>
                                                <option value="baghdad" {{ old('city') == 'baghdad' ? 'selected' : '' }}>🏛️ {{ __('Baghdad') }}</option>
                                                <option value="basra" {{ old('city') == 'basra' ? 'selected' : '' }}>🏭 {{ __('Basra') }}</option>
                                                <option value="erbil" {{ old('city') == 'erbil' ? 'selected' : '' }}>🏔️ {{ __('Erbil') }}</option>
                                                <option value="mosul" {{ old('city') == 'mosul' ? 'selected' : '' }}>🕌 {{ __('Mosul') }}</option>
                                                <option value="najaf" {{ old('city') == 'najaf' ? 'selected' : '' }}>🕌 {{ __('Najaf') }}</option>
                                                <option value="karbala" {{ old('city') == 'karbala' ? 'selected' : '' }}>🕌 {{ __('Karbala') }}</option>
                                                <option value="sulaymaniyah" {{ old('city') == 'sulaymaniyah' ? 'selected' : '' }}>🏔️ {{ __('Sulaymaniyah') }}</option>
                                                <option value="kirkuk" {{ old('city') == 'kirkuk' ? 'selected' : '' }}>🛢️ {{ __('Kirkuk') }}</option>
                                                <option value="duhok" {{ old('city') == 'duhok' ? 'selected' : '' }}>🏔️ {{ __('Duhok') }}</option>
                                            </select>
                                            @error('city')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="district" class="form-label">{{ __('District') }}</label>
                                            <input type="text" class="form-control @error('district') is-invalid @enderror" 
                                                   id="district" name="district" value="{{ old('district') }}" 
                                                   placeholder="{{ __('Enter district name') }}">
                                            @error('district')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="postal_code" class="form-label">{{ __('Postal Code') }}</label>
                                            <input type="text" class="form-control @error('postal_code') is-invalid @enderror" 
                                                   id="postal_code" name="postal_code" value="{{ old('postal_code') }}" 
                                                   placeholder="{{ __('Enter postal code') }}">
                                            @error('postal_code')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="country" class="form-label">{{ __('Country') }}</label>
                                            <select class="form-select @error('country') is-invalid @enderror" 
                                                    id="country" name="country" data-placeholder="{{ __('Select Country') }}">
                                                <option value="iraq" selected>🇮🇶 {{ __('Iraq') }}</option>
                                                <option value="kuwait" {{ old('country') == 'kuwait' ? 'selected' : '' }}>🇰🇼 {{ __('Kuwait') }}</option>
                                                <option value="jordan" {{ old('country') == 'jordan' ? 'selected' : '' }}>🇯🇴 {{ __('Jordan') }}</option>
                                                <option value="syria" {{ old('country') == 'syria' ? 'selected' : '' }}>🇸🇾 {{ __('Syria') }}</option>
                                                <option value="turkey" {{ old('country') == 'turkey' ? 'selected' : '' }}>🇹🇷 {{ __('Turkey') }}</option>
                                                <option value="iran" {{ old('country') == 'iran' ? 'selected' : '' }}>🇮🇷 {{ __('Iran') }}</option>
                                            </select>
                                            @error('country')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Business Information -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h6 class="card-title mb-0">{{ __('Business Information') }}</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="customer_type" class="form-label">{{ __('Customer Type') }}</label>
                                            <select class="form-select @error('customer_type') is-invalid @enderror" 
                                                    id="customer_type" name="customer_type" data-placeholder="{{ __('Select Customer Type') }}">
                                                <option value="">{{ __('Select Customer Type') }}</option>
                                                <option value="individual" {{ old('customer_type') == 'individual' ? 'selected' : '' }}>👤 {{ __('Individual') }}</option>
                                                <option value="pharmacy" {{ old('customer_type') == 'pharmacy' ? 'selected' : '' }}>💊 {{ __('Pharmacy') }}</option>
                                                <option value="hospital" {{ old('customer_type') == 'hospital' ? 'selected' : '' }}>🏥 {{ __('Hospital') }}</option>
                                                <option value="clinic" {{ old('customer_type') == 'clinic' ? 'selected' : '' }}>🩺 {{ __('Clinic') }}</option>
                                                <option value="distributor" {{ old('customer_type') == 'distributor' ? 'selected' : '' }}>🚚 {{ __('Distributor') }}</option>
                                                <option value="government" {{ old('customer_type') == 'government' ? 'selected' : '' }}>🏛️ {{ __('Government') }}</option>
                                            </select>
                                            @error('customer_type')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="credit_limit" class="form-label">{{ __('Credit Limit') }}</label>
                                            <div class="input-group">
                                                <span class="input-group-text">د.ع</span>
                                                <input type="number" class="form-control @error('credit_limit') is-invalid @enderror" 
                                                       id="credit_limit" name="credit_limit" value="{{ old('credit_limit', 0) }}" 
                                                       step="1000" min="0" placeholder="0">
                                            </div>
                                            @error('credit_limit')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="payment_terms" class="form-label">{{ __('Payment Terms') }}</label>
                                            <select class="form-select @error('payment_terms') is-invalid @enderror" 
                                                    id="payment_terms" name="payment_terms" data-placeholder="{{ __('Select Payment Terms') }}">
                                                <option value="">{{ __('Select Payment Terms') }}</option>
                                                <option value="cash" {{ old('payment_terms') == 'cash' ? 'selected' : '' }}>💵 {{ __('Cash Only') }}</option>
                                                <option value="net_7" {{ old('payment_terms') == 'net_7' ? 'selected' : '' }}>📅 {{ __('Net 7 Days') }}</option>
                                                <option value="net_15" {{ old('payment_terms') == 'net_15' ? 'selected' : '' }}>📅 {{ __('Net 15 Days') }}</option>
                                                <option value="net_30" {{ old('payment_terms') == 'net_30' ? 'selected' : '' }}>📅 {{ __('Net 30 Days') }}</option>
                                                <option value="net_60" {{ old('payment_terms') == 'net_60' ? 'selected' : '' }}>📅 {{ __('Net 60 Days') }}</option>
                                            </select>
                                            @error('payment_terms')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="tax_number" class="form-label">{{ __('Tax Number') }}</label>
                                            <input type="text" class="form-control @error('tax_number') is-invalid @enderror" 
                                                   id="tax_number" name="tax_number" value="{{ old('tax_number') }}" 
                                                   placeholder="{{ __('Enter tax registration number') }}">
                                            @error('tax_number')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="license_number" class="form-label">{{ __('License Number') }}</label>
                                            <input type="text" class="form-control @error('license_number') is-invalid @enderror" 
                                                   id="license_number" name="license_number" value="{{ old('license_number') }}" 
                                                   placeholder="{{ __('Enter business license number') }}">
                                            @error('license_number')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="notes" class="form-label">{{ __('Notes') }}</label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror" 
                                              id="notes" name="notes" rows="3" 
                                              placeholder="{{ __('Enter any additional notes about the customer...') }}">{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                                   value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_active">
                                                {{ __('Active Customer') }}
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="allow_credit" name="allow_credit" 
                                                   value="1" {{ old('allow_credit') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="allow_credit">
                                                {{ __('Allow Credit Sales') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-end">
                                    <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary {{ marginEnd('2') }}">
                                        <i class="fas fa-times {{ marginEnd('2') }}"></i>{{ __('Cancel') }}
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save {{ marginEnd('2') }}"></i>{{ __('Create Customer') }}
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
// Form validation
document.getElementById('customer-form').addEventListener('submit', function(e) {
    const name = document.getElementById('name').value.trim();
    const phone = document.getElementById('phone').value.trim();
    
    if (!name) {
        e.preventDefault();
        alert('{{ __("Customer name is required") }}');
        document.getElementById('name').focus();
        return;
    }
    
    if (!phone) {
        e.preventDefault();
        alert('{{ __("Phone number is required") }}');
        document.getElementById('phone').focus();
        return;
    }
});

// Phone number formatting
document.getElementById('phone').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.startsWith('964')) {
        value = '+' + value;
    } else if (value.startsWith('0')) {
        value = '+964 ' + value.substring(1);
    }
    e.target.value = value;
});

document.getElementById('secondary_phone').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.startsWith('964')) {
        value = '+' + value;
    } else if (value.startsWith('0')) {
        value = '+964 ' + value.substring(1);
    }
    e.target.value = value;
});

// Credit limit toggle
document.getElementById('allow_credit').addEventListener('change', function() {
    const creditLimitField = document.getElementById('credit_limit');
    if (this.checked) {
        creditLimitField.removeAttribute('disabled');
        creditLimitField.focus();
    } else {
        creditLimitField.setAttribute('disabled', 'disabled');
        creditLimitField.value = '0';
    }
});

// Initialize credit limit state
document.addEventListener('DOMContentLoaded', function() {
    const allowCredit = document.getElementById('allow_credit');
    const creditLimit = document.getElementById('credit_limit');
    
    if (!allowCredit.checked) {
        creditLimit.setAttribute('disabled', 'disabled');
    }
});
</script>
@endpush
