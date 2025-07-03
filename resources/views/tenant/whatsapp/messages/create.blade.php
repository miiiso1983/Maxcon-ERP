@extends('tenant.layouts.app')

@section('title', __('Send WhatsApp Message'))

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">{{ __('app.dashboard') }}</a></li>
<li class="breadcrumb-item"><a href="{{ route('whatsapp.dashboard') }}">{{ __('WhatsApp') }}</a></li>
<li class="breadcrumb-item"><a href="{{ route('whatsapp.messages.index') }}">{{ __('Messages') }}</a></li>
<li class="breadcrumb-item active">{{ __('Send Message') }}</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fab fa-whatsapp text-success {{ marginEnd('2') }}"></i>
                        {{ __('Send WhatsApp Message') }}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('whatsapp.messages.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row">
                            <!-- Recipient Selection -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="customer_id" class="form-label">{{ __('Recipient') }} <span class="text-danger">*</span></label>
                                    <select name="customer_id" id="customer_id" class="form-select select2" required>
                                        <option value="">{{ __('Select Customer') }}</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                                {{ $customer->name }} - {{ $customer->phone }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('customer_id')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <!-- Message Type -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="message_type" class="form-label">{{ __('Message Type') }} <span class="text-danger">*</span></label>
                                    <select name="message_type" id="message_type" class="form-select" required>
                                        @foreach($types as $key => $type)
                                            <option value="{{ $key }}" {{ old('message_type') == $key ? 'selected' : '' }}>
                                                {{ ucfirst(str_replace('_', ' ', $type)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('message_type')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Template Selection (for template messages) -->
                        <div class="row" id="template-section" style="display: none;">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="template_id" class="form-label">{{ __('Template') }}</label>
                                    <select name="template_id" id="template_id" class="form-select select2">
                                        <option value="">{{ __('Select Template') }}</option>
                                        @foreach($templates as $template)
                                            <option value="{{ $template->id }}" {{ old('template_id') == $template->id ? 'selected' : '' }}>
                                                {{ $template->name }} - {{ $template->category }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('template_id')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Message Content -->
                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="content" class="form-label">{{ __('Message Content') }} <span class="text-danger">*</span></label>
                                    <textarea name="content" id="content" class="form-control" rows="5" required placeholder="{{ __('Enter your message here...') }}">{{ old('content') }}</textarea>
                                    <div class="form-text">{{ __('Maximum 4096 characters') }}</div>
                                    @error('content')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Media Upload (for media messages) -->
                        <div class="row" id="media-section" style="display: none;">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="media_file" class="form-label">{{ __('Media File') }}</label>
                                    <input type="file" name="media_file" id="media_file" class="form-control" accept="image/*,video/*,audio/*,.pdf,.doc,.docx">
                                    <div class="form-text">{{ __('Supported: Images, Videos, Audio, PDF, Documents') }}</div>
                                    @error('media_file')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="media_caption" class="form-label">{{ __('Caption') }}</label>
                                    <input type="text" name="media_caption" id="media_caption" class="form-control" value="{{ old('media_caption') }}" placeholder="{{ __('Optional caption for media') }}">
                                    @error('media_caption')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Priority and Scheduling -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="priority" class="form-label">{{ __('Priority') }}</label>
                                    <select name="priority" id="priority" class="form-select">
                                        @foreach($priorities as $key => $priority)
                                            <option value="{{ $key }}" {{ old('priority', 'normal') == $key ? 'selected' : '' }}>
                                                {{ ucfirst($priority) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('priority')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="scheduled_at" class="form-label">{{ __('Schedule For') }}</label>
                                    <input type="datetime-local" name="scheduled_at" id="scheduled_at" class="form-control" value="{{ old('scheduled_at') }}">
                                    <div class="form-text">{{ __('Leave empty to send immediately') }}</div>
                                    @error('scheduled_at')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('whatsapp.messages.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left {{ marginEnd('1') }}"></i>
                                        {{ __('Back') }}
                                    </a>
                                    
                                    <div>
                                        <button type="button" class="btn btn-outline-primary" id="preview-btn">
                                            <i class="fas fa-eye {{ marginEnd('1') }}"></i>
                                            {{ __('Preview') }}
                                        </button>
                                        <button type="submit" class="btn btn-success">
                                            <i class="fab fa-whatsapp {{ marginEnd('1') }}"></i>
                                            {{ __('Send Message') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
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
                <h5 class="modal-title">{{ __('Message Preview') }}</h5>
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
    padding: 10px 15px;
    border-radius: 18px;
    max-width: 80%;
    margin-left: auto;
    position: relative;
}

.message-time {
    font-size: 11px;
    color: #666;
    text-align: right;
    margin-top: 5px;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const messageType = document.getElementById('message_type');
    const templateSection = document.getElementById('template-section');
    const mediaSection = document.getElementById('media-section');
    const previewBtn = document.getElementById('preview-btn');
    const previewModal = new bootstrap.Modal(document.getElementById('previewModal'));
    
    // Toggle sections based on message type
    messageType.addEventListener('change', function() {
        const type = this.value;
        
        if (type === 'template') {
            templateSection.style.display = 'block';
        } else {
            templateSection.style.display = 'none';
        }
        
        if (['image', 'video', 'audio', 'document'].includes(type)) {
            mediaSection.style.display = 'block';
        } else {
            mediaSection.style.display = 'none';
        }
    });
    
    // Preview functionality
    previewBtn.addEventListener('click', function() {
        const content = document.getElementById('content').value;
        const previewContent = document.getElementById('preview-content');
        
        if (content.trim()) {
            previewContent.textContent = content;
            previewModal.show();
        } else {
            alert('{{ __("Please enter message content to preview") }}');
        }
    });
    
    // Initialize Select2
    if (typeof $ !== 'undefined' && $.fn.select2) {
        $('.select2').select2({
            theme: 'bootstrap-5',
            placeholder: function() {
                return $(this).data('placeholder') || '{{ __("Select an option") }}';
            }
        });
    }
});
</script>
@endpush
