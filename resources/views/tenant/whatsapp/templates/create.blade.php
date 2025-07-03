@extends('tenant.layouts.app')

@section('title', __('Create WhatsApp Template'))

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">{{ __('app.dashboard') }}</a></li>
<li class="breadcrumb-item"><a href="{{ route('whatsapp.dashboard') }}">{{ __('WhatsApp') }}</a></li>
<li class="breadcrumb-item"><a href="{{ route('whatsapp.templates.index') }}">{{ __('Templates') }}</a></li>
<li class="breadcrumb-item active">{{ __('Create Template') }}</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-file-alt text-primary {{ marginEnd('2') }}"></i>
                        {{ __('Create WhatsApp Template') }}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('whatsapp.templates.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <!-- Template Name -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">{{ __('Template Name') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required placeholder="{{ __('Enter template name') }}">
                                    @error('name')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <!-- Category -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="category" class="form-label">{{ __('Category') }} <span class="text-danger">*</span></label>
                                    <select name="category" id="category" class="form-select" required>
                                        <option value="">{{ __('Select Category') }}</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category }}" {{ old('category') == $category ? 'selected' : '' }}>
                                                {{ ucfirst($category) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Template Type -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="template_type" class="form-label">{{ __('Template Type') }} <span class="text-danger">*</span></label>
                                    <select name="template_type" id="template_type" class="form-select" required>
                                        @foreach($types as $key => $type)
                                            <option value="{{ $key }}" {{ old('template_type') == $key ? 'selected' : '' }}>
                                                {{ ucfirst(str_replace('_', ' ', $type)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('template_type')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <!-- Language -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="language" class="form-label">{{ __('Language') }} <span class="text-danger">*</span></label>
                                    <select name="language" id="language" class="form-select" required>
                                        <option value="en" {{ old('language', 'en') == 'en' ? 'selected' : '' }}>English</option>
                                        <option value="ar" {{ old('language') == 'ar' ? 'selected' : '' }}>العربية</option>
                                        <option value="ku" {{ old('language') == 'ku' ? 'selected' : '' }}>کوردی</option>
                                    </select>
                                    @error('language')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Template Content -->
                        <div class="mb-3">
                            <label for="content" class="form-label">{{ __('Template Content') }} <span class="text-danger">*</span></label>
                            <textarea name="content" id="content" class="form-control" rows="6" required placeholder="{{ __('Enter your template content here...') }}">{{ old('content') }}</textarea>
                            <div class="form-text">
                                {{ __('Use variables like {{customer_name}}, {{order_number}}, etc. for dynamic content') }}
                            </div>
                            @error('content')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Variables -->
                        <div class="mb-3">
                            <label for="variables" class="form-label">{{ __('Variables') }}</label>
                            <input type="text" name="variables" id="variables" class="form-control" value="{{ old('variables') }}" placeholder="{{ __('customer_name,order_number,amount') }}">
                            <div class="form-text">
                                {{ __('Comma-separated list of variables used in the template') }}
                            </div>
                            @error('variables')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">{{ __('Description') }}</label>
                            <textarea name="description" id="description" class="form-control" rows="3" placeholder="{{ __('Optional description of when to use this template') }}">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('whatsapp.templates.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left {{ marginEnd('1') }}"></i>
                                {{ __('Back') }}
                            </a>
                            
                            <div>
                                <button type="button" class="btn btn-outline-primary" id="preview-btn">
                                    <i class="fas fa-eye {{ marginEnd('1') }}"></i>
                                    {{ __('Preview') }}
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save {{ marginEnd('1') }}"></i>
                                    {{ __('Create Template') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Template Guidelines -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">{{ __('Template Guidelines') }}</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6>{{ __('Available Variables') }}</h6>
                        <div class="variable-list">
                            <code>{{customer_name}}</code> - {{ __('Customer name') }}<br>
                            <code>{{phone}}</code> - {{ __('Phone number') }}<br>
                            <code>{{order_number}}</code> - {{ __('Order number') }}<br>
                            <code>{{amount}}</code> - {{ __('Amount') }}<br>
                            <code>{{date}}</code> - {{ __('Current date') }}<br>
                            <code>{{company_name}}</code> - {{ __('Company name') }}
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <h6>{{ __('Best Practices') }}</h6>
                        <ul class="small">
                            <li>{{ __('Keep messages concise and clear') }}</li>
                            <li>{{ __('Use personalization variables') }}</li>
                            <li>{{ __('Include clear call-to-action') }}</li>
                            <li>{{ __('Follow WhatsApp Business policies') }}</li>
                            <li>{{ __('Test templates before approval') }}</li>
                        </ul>
                    </div>
                    
                    <div class="mb-3">
                        <h6>{{ __('Template Categories') }}</h6>
                        <ul class="small">
                            <li><strong>{{ __('Marketing') }}</strong> - {{ __('Promotional messages') }}</li>
                            <li><strong>{{ __('Utility') }}</strong> - {{ __('Account updates, alerts') }}</li>
                            <li><strong>{{ __('Authentication') }}</strong> - {{ __('OTP, verification') }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Template Preview') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="whatsapp-preview">
                    <div class="message-bubble">
                        <div id="preview-content"></div>
                        <div class="message-time">{{ now()->format('H:i') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.whatsapp-preview {
    background: #e5ddd5;
    padding: 20px;
    border-radius: 10px;
}

.message-bubble {
    background: #dcf8c6;
    padding: 15px;
    border-radius: 18px;
    max-width: 80%;
    margin-left: auto;
    position: relative;
}

.message-time {
    font-size: 11px;
    color: #666;
    text-align: right;
    margin-top: 8px;
}

.variable-list code {
    display: inline-block;
    margin-bottom: 5px;
    background: #f8f9fa;
    padding: 2px 6px;
    border-radius: 3px;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const previewBtn = document.getElementById('preview-btn');
    const previewModal = new bootstrap.Modal(document.getElementById('previewModal'));
    const contentTextarea = document.getElementById('content');
    
    // Preview functionality
    previewBtn.addEventListener('click', function() {
        let content = contentTextarea.value;
        
        if (content.trim()) {
            // Replace variables with sample data for preview
            content = content
                .replace(/\{\{customer_name\}\}/g, 'John Doe')
                .replace(/\{\{phone\}\}/g, '+1234567890')
                .replace(/\{\{order_number\}\}/g, 'ORD-12345')
                .replace(/\{\{amount\}\}/g, '$99.99')
                .replace(/\{\{date\}\}/g, new Date().toLocaleDateString())
                .replace(/\{\{company_name\}\}/g, 'MAXCON ERP');
            
            document.getElementById('preview-content').innerHTML = content.replace(/\n/g, '<br>');
            previewModal.show();
        } else {
            alert('{{ __("Please enter template content to preview") }}');
        }
    });
    
    // Auto-detect variables
    contentTextarea.addEventListener('input', function() {
        const content = this.value;
        const variables = content.match(/\{\{([^}]+)\}\}/g);
        
        if (variables) {
            const uniqueVars = [...new Set(variables.map(v => v.replace(/[{}]/g, '')))];
            document.getElementById('variables').value = uniqueVars.join(',');
        }
    });
});
</script>
@endpush
