@extends('tenant.layouts.app')

@section('title', __('WhatsApp Messages'))

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">{{ __('app.dashboard') }}</a></li>
<li class="breadcrumb-item"><a href="{{ route('whatsapp.dashboard') }}">{{ __('WhatsApp') }}</a></li>
<li class="breadcrumb-item active">{{ __('Messages') }}</li>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <i class="fas fa-paper-plane fa-2x text-primary mb-2"></i>
                    <h6 class="small">{{ __('Total Messages') }}</h6>
                    <div class="h5 mb-0">{{ $stats['total'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                    <h6 class="small">{{ __('Delivered') }}</h6>
                    <div class="h5 mb-0">{{ $stats['delivered'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <i class="fas fa-eye fa-2x text-info mb-2"></i>
                    <h6 class="small">{{ __('Read') }}</h6>
                    <div class="h5 mb-0">{{ $stats['read'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <i class="fas fa-exclamation-triangle fa-2x text-danger mb-2"></i>
                    <h6 class="small">{{ __('Failed') }}</h6>
                    <div class="h5 mb-0">{{ $stats['failed'] ?? 0 }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Messages Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fab fa-whatsapp text-success {{ marginEnd('2') }}"></i>
                        {{ __('WhatsApp Messages') }}
                    </h5>
                    <div>
                        <a href="{{ route('whatsapp.messages.create') }}" class="btn btn-success">
                            <i class="fas fa-plus {{ marginEnd('1') }}"></i>
                            {{ __('Send Message') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <select class="form-select" id="status-filter">
                                <option value="">{{ __('All Statuses') }}</option>
                                <option value="pending">{{ __('Pending') }}</option>
                                <option value="sent">{{ __('Sent') }}</option>
                                <option value="delivered">{{ __('Delivered') }}</option>
                                <option value="read">{{ __('Read') }}</option>
                                <option value="failed">{{ __('Failed') }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="type-filter">
                                <option value="">{{ __('All Types') }}</option>
                                @foreach($types as $key => $type)
                                    <option value="{{ $key }}">{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input type="text" class="form-control" id="search-input" placeholder="{{ __('Search messages...') }}">
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-outline-primary w-100" id="filter-btn">
                                <i class="fas fa-filter {{ marginEnd('1') }}"></i>
                                {{ __('Filter') }}
                            </button>
                        </div>
                    </div>

                    <!-- Messages Table -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('Recipient') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Content') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Sent At') }}</th>
                                    <th>{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($messages as $message)
                                    <tr>
                                        <td>
                                            <div>
                                                <strong>{{ $message->recipient_name }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $message->formatted_phone }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                {{ ucfirst(str_replace('_', ' ', $message->message_type)) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="message-preview">
                                                {{ Str::limit($message->content, 50) }}
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $message->status_color }}">
                                                {{ ucfirst($message->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($message->sent_at)
                                                {{ is_string($message->sent_at) ? $message->sent_at : $message->sent_at->format('M d, Y H:i') }}
                                            @else
                                                <span class="text-muted">{{ __('Not sent') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('whatsapp.messages.show', $message) }}" class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($message->status === 'failed')
                                                    <form action="{{ route('whatsapp.messages.resend', $message) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-outline-warning btn-sm" title="{{ __('Resend') }}">
                                                            <i class="fas fa-redo"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <i class="fab fa-whatsapp fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">{{ __('No messages found') }}</p>
                                            <a href="{{ route('whatsapp.messages.create') }}" class="btn btn-success">
                                                {{ __('Send Your First Message') }}
                                            </a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($messages->hasPages())
                        <div class="d-flex justify-content-center">
                            {{ $messages->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.message-preview {
    max-width: 200px;
    word-wrap: break-word;
}

.badge {
    font-size: 0.75em;
}

.btn-group .btn {
    border-radius: 0.25rem;
    margin-right: 2px;
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusFilter = document.getElementById('status-filter');
    const typeFilter = document.getElementById('type-filter');
    const searchInput = document.getElementById('search-input');
    const filterBtn = document.getElementById('filter-btn');
    
    // Filter functionality
    filterBtn.addEventListener('click', function() {
        const params = new URLSearchParams();
        
        if (statusFilter.value) params.append('status', statusFilter.value);
        if (typeFilter.value) params.append('type', typeFilter.value);
        if (searchInput.value) params.append('search', searchInput.value);
        
        const url = new URL(window.location);
        url.search = params.toString();
        window.location.href = url.toString();
    });
    
    // Enter key search
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            filterBtn.click();
        }
    });
    
    // Resend confirmation
    document.querySelectorAll('form[action*="resend"]').forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirm('{{ __("Are you sure you want to resend this message?") }}')) {
                e.preventDefault();
            }
        });
    });
});
</script>
@endpush
