@extends('tenant.layouts.app')

@section('title', __('Message Details'))

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">{{ __('app.dashboard') }}</a></li>
<li class="breadcrumb-item"><a href="{{ route('whatsapp.dashboard') }}">{{ __('WhatsApp') }}</a></li>
<li class="breadcrumb-item"><a href="{{ route('whatsapp.messages.index') }}">{{ __('Messages') }}</a></li>
<li class="breadcrumb-item active">{{ __('Message Details') }}</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Message Details -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fab fa-whatsapp text-success {{ marginEnd('2') }}"></i>
                        {{ __('Message Details') }}
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Message Info -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>{{ __('Recipient Information') }}</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>{{ __('Name') }}:</strong></td>
                                    <td>{{ $message->recipient_name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('Phone') }}:</strong></td>
                                    <td>{{ $message->formatted_phone }}</td>
                                </tr>
                                @if($message->customer)
                                    <tr>
                                        <td><strong>{{ __('Customer') }}:</strong></td>
                                        <td>
                                            <a href="{{ route('customers.show', $message->customer) }}" class="text-decoration-none">
                                                {{ $message->customer->name }}
                                            </a>
                                        </td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>{{ __('Message Information') }}</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>{{ __('Type') }}:</strong></td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            {{ ucfirst(str_replace('_', ' ', $message->message_type)) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('Status') }}:</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $message->status_color }}">
                                            {{ ucfirst($message->status) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('Priority') }}:</strong></td>
                                    <td>{{ ucfirst($message->priority) }}</td>
                                </tr>
                                @if($message->template)
                                    <tr>
                                        <td><strong>{{ __('Template') }}:</strong></td>
                                        <td>
                                            <a href="{{ route('whatsapp.templates.show', $message->template) }}" class="text-decoration-none">
                                                {{ $message->template->name }}
                                            </a>
                                        </td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    <!-- Message Content -->
                    <div class="mb-4">
                        <h6>{{ __('Message Content') }}</h6>
                        <div class="whatsapp-message">
                            <div class="message-bubble">
                                <div class="message-content">
                                    {!! nl2br(e($message->content)) !!}
                                </div>
                                @if($message->media_url)
                                    <div class="message-media mt-2">
                                        @if(str_contains($message->message_type, 'image'))
                                            <img src="{{ $message->media_url }}" alt="Message Image" class="img-fluid rounded">
                                        @elseif(str_contains($message->message_type, 'video'))
                                            <video controls class="w-100 rounded">
                                                <source src="{{ $message->media_url }}" type="video/mp4">
                                            </video>
                                        @elseif(str_contains($message->message_type, 'audio'))
                                            <audio controls class="w-100">
                                                <source src="{{ $message->media_url }}" type="audio/mpeg">
                                            </audio>
                                        @else
                                            <a href="{{ $message->media_url }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-download {{ marginEnd('1') }}"></i>
                                                {{ __('Download File') }}
                                            </a>
                                        @endif
                                    </div>
                                @endif
                                <div class="message-time">
                                    @if($message->sent_at)
                                        {{ is_string($message->sent_at) ? $message->sent_at : $message->sent_at->format('H:i') }}
                                    @else
                                        {{ __('Not sent') }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('whatsapp.messages.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left {{ marginEnd('1') }}"></i>
                            {{ __('Back to Messages') }}
                        </a>
                        
                        @if($message->status === 'failed')
                            <form action="{{ route('whatsapp.messages.resend', $message) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-redo {{ marginEnd('1') }}"></i>
                                    {{ __('Resend Message') }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Timeline & Metadata -->
        <div class="col-md-4">
            <!-- Timeline -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">{{ __('Message Timeline') }}</h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item {{ $message->created_at ? 'completed' : '' }}">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <h6>{{ __('Created') }}</h6>
                                <small class="text-muted">
                                    {{ $message->created_at ? $message->created_at->format('M d, Y H:i') : __('Unknown') }}
                                </small>
                            </div>
                        </div>
                        
                        <div class="timeline-item {{ $message->sent_at ? 'completed' : '' }}">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <h6>{{ __('Sent') }}</h6>
                                <small class="text-muted">
                                    @if($message->sent_at)
                                        {{ is_string($message->sent_at) ? $message->sent_at : $message->sent_at->format('M d, Y H:i') }}
                                    @else
                                        {{ __('Not sent yet') }}
                                    @endif
                                </small>
                            </div>
                        </div>
                        
                        <div class="timeline-item {{ $message->delivered_at ? 'completed' : '' }}">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <h6>{{ __('Delivered') }}</h6>
                                <small class="text-muted">
                                    @if($message->delivered_at)
                                        {{ is_string($message->delivered_at) ? $message->delivered_at : $message->delivered_at->format('M d, Y H:i') }}
                                    @else
                                        {{ __('Not delivered yet') }}
                                    @endif
                                </small>
                            </div>
                        </div>
                        
                        <div class="timeline-item {{ $message->read_at ? 'completed' : '' }}">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <h6>{{ __('Read') }}</h6>
                                <small class="text-muted">
                                    @if($message->read_at)
                                        {{ is_string($message->read_at) ? $message->read_at : $message->read_at->format('M d, Y H:i') }}
                                    @else
                                        {{ __('Not read yet') }}
                                    @endif
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Metadata -->
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">{{ __('Metadata') }}</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        @if($message->whatsapp_message_id)
                            <tr>
                                <td><strong>{{ __('WhatsApp ID') }}:</strong></td>
                                <td><code>{{ $message->whatsapp_message_id }}</code></td>
                            </tr>
                        @endif
                        @if($message->error_message)
                            <tr>
                                <td><strong>{{ __('Error') }}:</strong></td>
                                <td><span class="text-danger">{{ $message->error_message }}</span></td>
                            </tr>
                        @endif
                        <tr>
                            <td><strong>{{ __('Sent By') }}:</strong></td>
                            <td>{{ $message->user ? $message->user->name : __('System') }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ __('Created') }}:</strong></td>
                            <td>{{ $message->created_at ? $message->created_at->format('M d, Y H:i') : __('Unknown') }}</td>
                        </tr>
                        @if($message->scheduled_at)
                            <tr>
                                <td><strong>{{ __('Scheduled') }}:</strong></td>
                                <td>{{ is_string($message->scheduled_at) ? $message->scheduled_at : $message->scheduled_at->format('M d, Y H:i') }}</td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.whatsapp-message {
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

.message-content {
    word-wrap: break-word;
}

.message-time {
    font-size: 11px;
    color: #666;
    text-align: right;
    margin-top: 8px;
}

.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -35px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #dee2e6;
    border: 2px solid #fff;
}

.timeline-item.completed .timeline-marker {
    background: #28a745;
}

.timeline::before {
    content: '';
    position: absolute;
    left: -29px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}
</style>
@endpush
