@extends('tenant.layouts.app')

@section('title', __('WhatsApp Templates'))

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">{{ __('app.dashboard') }}</a></li>
<li class="breadcrumb-item"><a href="{{ route('whatsapp.dashboard') }}">{{ __('WhatsApp') }}</a></li>
<li class="breadcrumb-item active">{{ __('Templates') }}</li>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Templates Management -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-file-alt text-primary {{ marginEnd('2') }}"></i>
                        {{ __('WhatsApp Templates') }}
                    </h5>
                    <div>
                        <a href="{{ route('whatsapp.templates.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus {{ marginEnd('1') }}"></i>
                            {{ __('Create Template') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <select class="form-select" id="category-filter">
                                <option value="">{{ __('All Categories') }}</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category }}">{{ ucfirst($category) }}</option>
                                @endforeach
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
                            <input type="text" class="form-control" id="search-input" placeholder="{{ __('Search templates...') }}">
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-outline-primary w-100" id="filter-btn">
                                <i class="fas fa-filter {{ marginEnd('1') }}"></i>
                                {{ __('Filter') }}
                            </button>
                        </div>
                    </div>

                    <!-- Templates Grid -->
                    <div class="row">
                        @forelse($templates as $template)
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card h-100 template-card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">{{ $template->name }}</h6>
                                        <span class="badge bg-{{ $template->status_color }}">
                                            {{ ucfirst($template->status) }}
                                        </span>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-2">
                                            <span class="badge bg-secondary">{{ ucfirst($template->category) }}</span>
                                            <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $template->template_type)) }}</span>
                                        </div>
                                        
                                        <div class="template-preview mb-3">
                                            <small class="text-muted">{{ __('Preview') }}:</small>
                                            <div class="template-content">
                                                {{ Str::limit($template->content, 100) }}
                                            </div>
                                        </div>
                                        
                                        <div class="template-stats">
                                            <small class="text-muted">
                                                {{ __('Used') }}: {{ $template->usage_count ?? 0 }} {{ __('times') }}
                                                @if($template->last_used_at)
                                                    <br>{{ __('Last used') }}: {{ is_string($template->last_used_at) ? $template->last_used_at : $template->last_used_at->format('M d, Y') }}
                                                @endif
                                            </small>
                                        </div>
                                    </div>
                                    <div class="card-footer">
                                        <div class="btn-group w-100" role="group">
                                            <a href="{{ route('whatsapp.templates.show', $template) }}" class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-eye"></i> {{ __('View') }}
                                            </a>
                                            @if($template->status === 'approved')
                                                <button class="btn btn-outline-success btn-sm use-template-btn" data-template-id="{{ $template->id }}">
                                                    <i class="fas fa-paper-plane"></i> {{ __('Use') }}
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="text-center py-5">
                                    <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">{{ __('No templates found') }}</h5>
                                    <p class="text-muted">{{ __('Create your first WhatsApp template to get started') }}</p>
                                    <a href="{{ route('whatsapp.templates.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus {{ marginEnd('1') }}"></i>
                                        {{ __('Create Template') }}
                                    </a>
                                </div>
                            </div>
                        @endforelse
                    </div>

                    <!-- Pagination -->
                    @if($templates->hasPages())
                        <div class="d-flex justify-content-center">
                            {{ $templates->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Use Template Modal -->
<div class="modal fade" id="useTemplateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Use Template') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('whatsapp.messages.create') }}" method="GET">
                <div class="modal-body">
                    <input type="hidden" name="template_id" id="selected-template-id">
                    <div class="mb-3">
                        <label for="recipient-select" class="form-label">{{ __('Select Recipient') }}</label>
                        <select name="customer_id" id="recipient-select" class="form-select" required>
                            <option value="">{{ __('Choose a customer...') }}</option>
                            <!-- Will be populated via AJAX -->
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Continue') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.template-card {
    transition: transform 0.2s;
}

.template-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.template-content {
    background: #f8f9fa;
    padding: 10px;
    border-radius: 5px;
    font-size: 0.9em;
    border-left: 3px solid #007bff;
}

.template-stats {
    font-size: 0.8em;
}

.badge {
    font-size: 0.75em;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const categoryFilter = document.getElementById('category-filter');
    const typeFilter = document.getElementById('type-filter');
    const searchInput = document.getElementById('search-input');
    const filterBtn = document.getElementById('filter-btn');
    const useTemplateModal = new bootstrap.Modal(document.getElementById('useTemplateModal'));
    
    // Filter functionality
    filterBtn.addEventListener('click', function() {
        const params = new URLSearchParams();
        
        if (categoryFilter.value) params.append('category', categoryFilter.value);
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
    
    // Use template functionality
    document.querySelectorAll('.use-template-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const templateId = this.dataset.templateId;
            document.getElementById('selected-template-id').value = templateId;
            
            // Load customers via AJAX (simplified for now)
            // In a real implementation, you'd fetch customers from an API
            useTemplateModal.show();
        });
    });
});
</script>
@endpush
