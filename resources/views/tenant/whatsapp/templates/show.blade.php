@extends('tenant.layouts.app')

@section('title', __('Template Details'))

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">{{ __('app.dashboard') }}</a></li>
<li class="breadcrumb-item"><a href="{{ route('whatsapp.dashboard') }}">{{ __('WhatsApp') }}</a></li>
<li class="breadcrumb-item"><a href="{{ route('whatsapp.templates.index') }}">{{ __('Templates') }}</a></li>
<li class="breadcrumb-item active">{{ __('Template Details') }}</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Template Details -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-file-alt text-primary {{ marginEnd('2') }}"></i>
                        {{ $template->name }}
                    </h5>
                    <span class="badge bg-{{ $template->status_color }} fs-6">
                        {{ ucfirst($template->status) }}
                    </span>
                </div>
                <div class="card-body">
                    <!-- Template Info -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>{{ __('Template Information') }}</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>{{ __('Name') }}:</strong></td>
                                    <td>{{ $template->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('Category') }}:</strong></td>
                                    <td>
                                        <span class="badge bg-secondary">{{ ucfirst($template->category) }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('Type') }}:</strong></td>
                                    <td>
                                        <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $template->template_type)) }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('Language') }}:</strong></td>
                                    <td>{{ strtoupper($template->language) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('Status') }}:</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $template->status_color }}">
                                            {{ ucfirst($template->status) }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>{{ __('Usage Statistics') }}</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>{{ __('Times Used') }}:</strong></td>
                                    <td>{{ $template->usage_count ?? 0 }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('Last Used') }}:</strong></td>
                                    <td>
                                        @if($template->last_used_at)
                                            {{ is_string($template->last_used_at) ? $template->last_used_at : $template->last_used_at->format('M d, Y H:i') }}
                                        @else
                                            {{ __('Never used') }}
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('Created') }}:</strong></td>
                                    <td>{{ $template->created_at ? $template->created_at->format('M d, Y H:i') : __('Unknown') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('Updated') }}:</strong></td>
                                    <td>{{ $template->updated_at ? $template->updated_at->format('M d, Y H:i') : __('Unknown') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Template Content -->
                    <div class="mb-4">
                        <h6>{{ __('Template Content') }}</h6>
                        <div class="whatsapp-template">
                            <div class="template-bubble">
                                <div class="template-content">
                                    {!! nl2br(e($template->content)) !!}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Variables -->
                    @if($template->variables)
                        <div class="mb-4">
                            <h6>{{ __('Template Variables') }}</h6>
                            <div class="variables-list">
                                @foreach(explode(',', $template->variables) as $variable)
                                    <code class="me-2 mb-1 d-inline-block">{{ '{{' . trim($variable) . '}}' }}</code>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Description -->
                    @if($template->description)
                        <div class="mb-4">
                            <h6>{{ __('Description') }}</h6>
                            <p class="text-muted">{{ $template->description }}</p>
                        </div>
                    @endif

                    <!-- Actions -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('whatsapp.templates.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left {{ marginEnd('1') }}"></i>
                            {{ __('Back to Templates') }}
                        </a>
                        
                        <div>
                            @if($template->status === 'approved')
                                <a href="{{ route('whatsapp.messages.create', ['template_id' => $template->id]) }}" class="btn btn-success">
                                    <i class="fas fa-paper-plane {{ marginEnd('1') }}"></i>
                                    {{ __('Use Template') }}
                                </a>
                            @endif
                            <button class="btn btn-outline-primary" id="preview-btn">
                                <i class="fas fa-eye {{ marginEnd('1') }}"></i>
                                {{ __('Preview') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Messages -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">{{ __('Recent Messages') }}</h6>
                </div>
                <div class="card-body">
                    @if($template->messages && $template->messages->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($template->messages->take(5) as $message)
                                <div class="list-group-item px-0">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">{{ $message->recipient_name }}</h6>
                                            <p class="mb-1 small text-muted">{{ $message->formatted_phone }}</p>
                                            <small class="text-muted">
                                                @if($message->sent_at)
                                                    {{ is_string($message->sent_at) ? $message->sent_at : $message->sent_at->format('M d, Y') }}
                                                @else
                                                    {{ __('Not sent') }}
                                                @endif
                                            </small>
                                        </div>
                                        <span class="badge bg-{{ $message->status_color }}">
                                            {{ ucfirst($message->status) }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        @if($template->messages->count() > 5)
                            <div class="text-center mt-3">
                                <a href="{{ route('whatsapp.messages.index', ['template_id' => $template->id]) }}" class="btn btn-outline-primary btn-sm">
                                    {{ __('View All Messages') }}
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-3">
                            <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                            <p class="text-muted small">{{ __('No messages sent with this template yet') }}</p>
                        </div>
                    @endif
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
.whatsapp-template {
    background: #e5ddd5;
    padding: 20px;
    border-radius: 10px;
}

.template-bubble {
    background: #dcf8c6;
    padding: 15px;
    border-radius: 18px;
    max-width: 80%;
    margin-left: auto;
    position: relative;
}

.template-content {
    word-wrap: break-word;
}

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

.variables-list code {
    background: #f8f9fa;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.9em;
}

.list-group-item {
    border: none;
    border-bottom: 1px solid #dee2e6;
}

.list-group-item:last-child {
    border-bottom: none;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const previewBtn = document.getElementById('preview-btn');
    const previewModal = new bootstrap.Modal(document.getElementById('previewModal'));
    
    // Preview functionality
    previewBtn.addEventListener('click', function() {
        let content = `{!! addslashes($template->content) !!}`;
        
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
    });
});
</script>
@endpush
