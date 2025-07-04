@extends('tenant.layouts.app')

@section('title', __('Collections'))
@section('page-title', __('Collections'))

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">{{ __('app.dashboard') }}</a></li>
<li class="breadcrumb-item"><a href="{{ route('financial.index') }}">{{ __('Financial') }}</a></li>
<li class="breadcrumb-item active">{{ __('Collections') }}</li>
@endsection

@section('content')
<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stats-card">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col {{ marginEnd('2') }}">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            {{ __('Total Collections') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ $stats['total_collections'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-file-invoice-dollar fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stats-card-warning">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col {{ marginEnd('2') }}">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            {{ __('Pending Amount') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ formatCurrency($stats['pending_amount']) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stats-card-success">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col {{ marginEnd('2') }}">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            {{ __('Collected Amount') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ formatCurrency($stats['collected_amount']) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stats-card-danger">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col {{ marginEnd('2') }}">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            {{ __('Overdue Amount') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ formatCurrency($stats['overdue_amount']) }}</div>
                        <div class="text-xs">{{ $stats['overdue_count'] }} {{ __('overdue') }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2 mb-2">
                        <a href="{{ route('financial.collections.create') }}" class="btn btn-primary w-100">
                            <i class="fas fa-plus {{ marginEnd('2') }}"></i>{{ __('New Collection') }}
                        </a>
                    </div>
                    <div class="col-md-2 mb-2">
                        <a href="{{ route('financial.collections.index', ['filter' => 'overdue']) }}" class="btn btn-danger w-100">
                            <i class="fas fa-exclamation-triangle {{ marginEnd('2') }}"></i>{{ __('Overdue') }}
                        </a>
                    </div>
                    <div class="col-md-2 mb-2">
                        <a href="{{ route('financial.collections.index', ['filter' => 'due_today']) }}" class="btn btn-warning w-100">
                            <i class="fas fa-calendar-day {{ marginEnd('2') }}"></i>{{ __('Due Today') }}
                        </a>
                    </div>
                    <div class="col-md-2 mb-2">
                        <a href="{{ route('financial.collections.index', ['filter' => 'follow_up']) }}" class="btn btn-info w-100">
                            <i class="fas fa-phone {{ marginEnd('2') }}"></i>{{ __('Follow-up') }}
                        </a>
                    </div>
                    <div class="col-md-2 mb-2">
                        <a href="{{ route('financial.payment-plans.index') }}" class="btn btn-success w-100">
                            <i class="fas fa-calendar-alt {{ marginEnd('2') }}"></i>{{ __('Payment Plans') }}
                        </a>
                    </div>
                    <div class="col-md-2 mb-2">
                        <a href="{{ route('financial.collections.dashboard') }}" class="btn btn-secondary w-100">
                            <i class="fas fa-chart-bar {{ marginEnd('2') }}"></i>{{ __('Dashboard') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters and Search -->
<div class="card mb-4">
    <div class="card-header">
        <h6 class="m-0">{{ __('app.search') }} & {{ __('app.filter') }}</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('financial.collections.index') }}">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label for="search" class="form-label">{{ __('app.search') }}</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="{{ request('search') }}" placeholder="{{ __('Search collections...') }}">
                </div>
                <div class="col-md-2 mb-3">
                    <label for="status" class="form-label">{{ __('app.status') }}</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">{{ __('All Status') }}</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>{{ __('In Progress') }}</option>
                        <option value="collected" {{ request('status') == 'collected' ? 'selected' : '' }}>{{ __('Collected') }}</option>
                        <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>{{ __('Partial') }}</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>{{ __('Cancelled') }}</option>
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <label for="priority" class="form-label">{{ __('Priority') }}</label>
                    <select class="form-select" id="priority" name="priority">
                        <option value="">{{ __('All Priorities') }}</option>
                        <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>{{ __('Low') }}</option>
                        <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>{{ __('Medium') }}</option>
                        <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>{{ __('High') }}</option>
                        <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>{{ __('Urgent') }}</option>
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <label for="collector_id" class="form-label">{{ __('Collector') }}</label>
                    <select class="form-select" id="collector_id" name="collector_id">
                        <option value="">{{ __('All Collectors') }}</option>
                        @foreach($collectors as $collector)
                        <option value="{{ $collector->id }}" {{ request('collector_id') == $collector->id ? 'selected' : '' }}>
                            {{ $collector->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <label for="date_from" class="form-label">{{ __('Due From') }}</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" 
                           value="{{ request('date_from') }}">
                </div>
                <div class="col-md-1 mb-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <a href="{{ route('financial.collections.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times {{ marginEnd('2') }}"></i>{{ __('Clear') }}
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Collections Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="m-0">{{ __('Collections') }}</h6>
        <div>
            <span class="text-muted">{{ $collections->total() }} {{ __('collections found') }}</span>
        </div>
    </div>
    <div class="card-body p-0">
        @if($collections->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>
                            <input type="checkbox" id="selectAll" class="form-check-input">
                        </th>
                        <th>{{ __('Collection #') }}</th>
                        <th>{{ __('Customer') }}</th>
                        <th>{{ __('Amount') }}</th>
                        <th>{{ __('Due Date') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Priority') }}</th>
                        <th>{{ __('Collector') }}</th>
                        <th width="120">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($collections as $collection)
                    <tr class="{{ $collection->is_overdue ? 'table-danger' : '' }}">
                        <td>
                            <input type="checkbox" name="collections[]" value="{{ $collection->id }}" class="form-check-input collection-checkbox">
                        </td>
                        <td>
                            <div>
                                <strong>{{ $collection->collection_number }}</strong>
                                @if($collection->is_overdue)
                                <br><small class="text-danger">{{ $collection->days_overdue }} {{ __('days overdue') }}</small>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div>
                                <strong>{{ $collection->customer->name }}</strong>
                                <br>
                                <small class="text-muted">{{ $collection->customer->customer_code }}</small>
                            </div>
                        </td>
                        <td>
                            <div>
                                <strong>{{ formatCurrency($collection->amount_due) }}</strong>
                                @if($collection->amount_collected > 0)
                                <br>
                                <small class="text-success">{{ __('Collected') }}: {{ formatCurrency($collection->amount_collected) }}</small>
                                @endif
                                @if($collection->balance_amount > 0)
                                <br>
                                <small class="text-warning">{{ __('Balance') }}: {{ formatCurrency($collection->balance_amount) }}</small>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div>
                                {{ $collection->due_date->format('M d, Y') }}
                                @if($collection->follow_up_date)
                                <br>
                                <small class="text-info">
                                    <i class="fas fa-phone {{ marginEnd('1') }}"></i>
                                    {{ $collection->follow_up_date->format('M d, Y') }}
                                </small>
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-{{ $collection->status_color }}">
                                {{ ucfirst(str_replace('_', ' ', $collection->status)) }}
                            </span>
                            @if($collection->collection_rate > 0 && $collection->collection_rate < 100)
                            <br>
                            <div class="progress mt-1" style="height: 4px;">
                                <div class="progress-bar" role="progressbar" data-width="{{ number_format($collection->collection_rate ?? 0, 1) }}"></div>
                            </div>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $collection->priority_color }}">
                                {{ ucfirst($collection->priority) }}
                            </span>
                        </td>
                        <td>
                            @if($collection->collector)
                            <div>
                                {{ $collection->collector->name }}
                                @if($collection->contact_attempts > 0)
                                <br>
                                <small class="text-muted">{{ $collection->contact_attempts }} {{ __('attempts') }}</small>
                                @endif
                            </div>
                            @else
                            <span class="text-muted">{{ __('Unassigned') }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('collections.show', $collection) }}" 
                                   class="btn btn-outline-info" title="{{ __('View') }}">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($collection->canBeEdited())
                                <a href="{{ route('collections.edit', $collection) }}" 
                                   class="btn btn-outline-primary" title="{{ __('app.edit') }}">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endif
                                @if($collection->balance_amount > 0)
                                <button type="button" class="btn btn-outline-success" 
                                        onclick="addPayment('{{ $collection->id }}')" title="{{ __('Add Payment') }}">
                                    <i class="fas fa-dollar-sign"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        @if($collections->hasPages())
        <div class="card-footer">
            {{ $collections->appends(request()->query())->links() }}
        </div>
        @endif
        @else
        <div class="text-center py-5">
            <i class="fas fa-file-invoice-dollar fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">{{ __('No collections found') }}</h5>
            <p class="text-muted">{{ __('Try adjusting your search criteria or create a new collection.') }}</p>
            <a href="{{ route('financial.collections.create') }}" class="btn btn-primary">
                <i class="fas fa-plus {{ marginEnd('2') }}"></i>{{ __('New Collection') }}
            </a>
        </div>
        @endif
    </div>
</div>

<!-- Bulk Actions -->
@if($collections->count() > 0)
<div class="card mt-3" id="bulkActions" style="display: none;">
    <div class="card-body">
        <form method="POST" action="{{ route('collections.bulk-action') }}" id="bulkActionForm">
            @csrf
            <div class="row align-items-center">
                <div class="col-md-3">
                    <select name="action" class="form-select" required>
                        <option value="">{{ __('Select Action') }}</option>
                        <option value="assign_collector">{{ __('Assign Collector') }}</option>
                        <option value="update_priority">{{ __('Update Priority') }}</option>
                        <option value="schedule_follow_up">{{ __('Schedule Follow-up') }}</option>
                    </select>
                </div>
                <div class="col-md-3" id="collectorField" style="display: none;">
                    <select name="collector_id" class="form-select">
                        <option value="">{{ __('Select Collector') }}</option>
                        @foreach($collectors as $collector)
                        <option value="{{ $collector->id }}">{{ $collector->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3" id="priorityField" style="display: none;">
                    <select name="priority" class="form-select">
                        <option value="">{{ __('Select Priority') }}</option>
                        <option value="low">{{ __('Low') }}</option>
                        <option value="medium">{{ __('Medium') }}</option>
                        <option value="high">{{ __('High') }}</option>
                        <option value="urgent">{{ __('Urgent') }}</option>
                    </select>
                </div>
                <div class="col-md-3" id="followUpField" style="display: none;">
                    <input type="date" name="follow_up_date" class="form-control" min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-bolt {{ marginEnd('2') }}"></i>{{ __('Apply to Selected') }}
                    </button>
                </div>
                <div class="col-md-6">
                    <span id="selectedCount" class="text-muted"></span>
                </div>
            </div>
        </form>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    const collectionCheckboxes = document.querySelectorAll('.collection-checkbox');
    const bulkActions = document.getElementById('bulkActions');
    const selectedCount = document.getElementById('selectedCount');
    const bulkActionForm = document.getElementById('bulkActionForm');
    const actionSelect = bulkActionForm.querySelector('select[name="action"]');

    function updateBulkActions() {
        const checkedBoxes = document.querySelectorAll('.collection-checkbox:checked');
        const count = checkedBoxes.length;
        
        if (count > 0) {
            bulkActions.style.display = 'block';
            selectedCount.textContent = `${count} collection(s) selected`;
            
            // Add hidden inputs for selected collections
            const existingInputs = bulkActionForm.querySelectorAll('input[name="collections[]"]');
            existingInputs.forEach(input => input.remove());
            
            checkedBoxes.forEach(checkbox => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'collections[]';
                input.value = checkbox.value;
                bulkActionForm.appendChild(input);
            });
        } else {
            bulkActions.style.display = 'none';
        }
    }

    function toggleActionFields() {
        const action = actionSelect.value;
        document.getElementById('collectorField').style.display = action === 'assign_collector' ? 'block' : 'none';
        document.getElementById('priorityField').style.display = action === 'update_priority' ? 'block' : 'none';
        document.getElementById('followUpField').style.display = action === 'schedule_follow_up' ? 'block' : 'none';
    }

    selectAll.addEventListener('change', function() {
        collectionCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkActions();
    });

    collectionCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkActions);
    });

    actionSelect.addEventListener('change', toggleActionFields);

    document.querySelectorAll('.progress-bar[data-width]').forEach(function(bar) {
        bar.style.width = bar.getAttribute('data-width') + '%';
    });
});

function addPayment(collectionId) {
    // This would typically open a modal for adding payment
    // For now, redirect to the collection detail page
    window.location.href = `/collections/${collectionId}#add-payment`;
}
</script>
@endpush
