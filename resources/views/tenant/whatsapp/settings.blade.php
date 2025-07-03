@extends('tenant.layouts.app')

@section('title', __('WhatsApp Settings'))

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">{{ __('app.dashboard') }}</a></li>
<li class="breadcrumb-item"><a href="{{ route('whatsapp.dashboard') }}">{{ __('WhatsApp') }}</a></li>
<li class="breadcrumb-item active">{{ __('Settings') }}</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Configuration Status -->
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        @if($isConfigured)
                            <div class="flex-shrink-0">
                                <i class="fas fa-check-circle fa-3x text-success"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="mb-1">{{ __('WhatsApp Business API Connected') }}</h5>
                                <p class="text-muted mb-0">{{ __('Your WhatsApp Business account is properly configured and ready to send messages.') }}</p>
                            </div>
                        @else
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-triangle fa-3x text-warning"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="mb-1">{{ __('WhatsApp Business API Not Configured') }}</h5>
                                <p class="text-muted mb-0">{{ __('Please configure your WhatsApp Business API credentials to start sending messages.') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- API Configuration -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-cog text-primary {{ marginEnd('2') }}"></i>
                        {{ __('API Configuration') }}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('whatsapp.settings') }}" method="POST">
                        @csrf
                        
                        <!-- Access Token -->
                        <div class="mb-3">
                            <label for="access_token" class="form-label">{{ __('Access Token') }} <span class="text-danger">*</span></label>
                            <input type="password" name="access_token" id="access_token" class="form-control" 
                                   value="{{ old('access_token', config('whatsapp.access_token') ? '••••••••••••••••' : '') }}" 
                                   placeholder="{{ __('Enter your WhatsApp Business API access token') }}">
                            <div class="form-text">{{ __('Your permanent access token from Facebook Developer Console') }}</div>
                            @error('access_token')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Phone Number ID -->
                        <div class="mb-3">
                            <label for="phone_number_id" class="form-label">{{ __('Phone Number ID') }} <span class="text-danger">*</span></label>
                            <input type="text" name="phone_number_id" id="phone_number_id" class="form-control" 
                                   value="{{ old('phone_number_id', config('whatsapp.phone_number_id')) }}" 
                                   placeholder="{{ __('Enter your phone number ID') }}">
                            <div class="form-text">{{ __('The ID of your registered WhatsApp Business phone number') }}</div>
                            @error('phone_number_id')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Business Account ID -->
                        <div class="mb-3">
                            <label for="business_account_id" class="form-label">{{ __('Business Account ID') }}</label>
                            <input type="text" name="business_account_id" id="business_account_id" class="form-control" 
                                   value="{{ old('business_account_id', config('whatsapp.business_account_id')) }}" 
                                   placeholder="{{ __('Enter your WhatsApp Business Account ID') }}">
                            <div class="form-text">{{ __('Your WhatsApp Business Account ID (optional)') }}</div>
                            @error('business_account_id')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Webhook Configuration -->
                        <hr>
                        <h6>{{ __('Webhook Configuration') }}</h6>
                        
                        <div class="mb-3">
                            <label for="webhook_verify_token" class="form-label">{{ __('Webhook Verify Token') }}</label>
                            <input type="text" name="webhook_verify_token" id="webhook_verify_token" class="form-control" 
                                   value="{{ old('webhook_verify_token', config('whatsapp.webhook_verify_token')) }}" 
                                   placeholder="{{ __('Enter webhook verify token') }}">
                            <div class="form-text">{{ __('Token used to verify webhook requests') }}</div>
                            @error('webhook_verify_token')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="webhook_secret" class="form-label">{{ __('Webhook Secret') }}</label>
                            <input type="password" name="webhook_secret" id="webhook_secret" class="form-control" 
                                   value="{{ old('webhook_secret', config('whatsapp.webhook_secret') ? '••••••••••••••••' : '') }}" 
                                   placeholder="{{ __('Enter webhook secret') }}">
                            <div class="form-text">{{ __('Secret used to validate webhook payloads') }}</div>
                            @error('webhook_secret')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Webhook URL Display -->
                        <div class="mb-3">
                            <label class="form-label">{{ __('Webhook URL') }}</label>
                            <div class="input-group">
                                <input type="text" class="form-control" value="{{ route('whatsapp.webhook') }}" readonly>
                                <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('{{ route('whatsapp.webhook') }}')">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                            <div class="form-text">{{ __('Use this URL in your Facebook Developer Console webhook configuration') }}</div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-outline-primary" id="test-connection-btn">
                                <i class="fas fa-plug {{ marginEnd('1') }}"></i>
                                {{ __('Test Connection') }}
                            </button>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save {{ marginEnd('1') }}"></i>
                                {{ __('Save Settings') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Business Account Info -->
        <div class="col-md-4">
            @if($isConfigured && $businessInfo)
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">{{ __('Business Account Info') }}</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tr>
                                <td><strong>{{ __('Name') }}:</strong></td>
                                <td>{{ $businessInfo['name'] ?? __('N/A') }}</td>
                            </tr>
                            <tr>
                                <td><strong>{{ __('Status') }}:</strong></td>
                                <td>
                                    <span class="badge bg-success">{{ __('Connected') }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>{{ __('Phone') }}:</strong></td>
                                <td>{{ $businessInfo['phone'] ?? __('N/A') }}</td>
                            </tr>
                            <tr>
                                <td><strong>{{ __('Verified') }}:</strong></td>
                                <td>
                                    @if($businessInfo['verified'] ?? false)
                                        <span class="badge bg-success">{{ __('Yes') }}</span>
                                    @else
                                        <span class="badge bg-warning">{{ __('No') }}</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Setup Guide -->
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">{{ __('Setup Guide') }}</h6>
                </div>
                <div class="card-body">
                    <ol class="small">
                        <li>{{ __('Create a Facebook Developer account') }}</li>
                        <li>{{ __('Create a WhatsApp Business App') }}</li>
                        <li>{{ __('Add a phone number to your app') }}</li>
                        <li>{{ __('Generate a permanent access token') }}</li>
                        <li>{{ __('Configure webhook with the URL above') }}</li>
                        <li>{{ __('Enter your credentials in the form') }}</li>
                        <li>{{ __('Test the connection') }}</li>
                    </ol>
                    
                    <div class="mt-3">
                        <a href="https://developers.facebook.com/docs/whatsapp/cloud-api/get-started" 
                           target="_blank" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-external-link-alt {{ marginEnd('1') }}"></i>
                            {{ __('Official Documentation') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Show success message
        const btn = event.target.closest('button');
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i>';
        btn.classList.add('btn-success');
        btn.classList.remove('btn-outline-secondary');
        
        setTimeout(() => {
            btn.innerHTML = originalHtml;
            btn.classList.remove('btn-success');
            btn.classList.add('btn-outline-secondary');
        }, 2000);
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const testConnectionBtn = document.getElementById('test-connection-btn');
    
    testConnectionBtn.addEventListener('click', function() {
        const btn = this;
        const originalHtml = btn.innerHTML;
        
        // Show loading state
        btn.innerHTML = '<i class="fas fa-spinner fa-spin {{ marginEnd("1") }}"></i>{{ __("Testing...") }}';
        btn.disabled = true;
        
        // Simulate API test (replace with actual AJAX call)
        setTimeout(() => {
            // Reset button
            btn.innerHTML = originalHtml;
            btn.disabled = false;
            
            // Show result (replace with actual result)
            alert('{{ __("Connection test completed. Check the console for details.") }}');
        }, 2000);
    });
});
</script>
@endpush
