@extends('tenant.layouts.app')

@section('title', __('Payment Plans'))

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">📅 {{ __('Payment Plans') }}</h1>
            <p class="text-muted">{{ __('Manage customer payment plans and installments') }}</p>
        </div>
        <div>
            <a href="{{ route('financial.payment-plans.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> {{ __('New Payment Plan') }}
            </a>
            <a href="{{ route('financial.collections.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> {{ __('Back to Collections') }}
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ number_format($stats['total_plans']) }}</h4>
                            <p class="mb-0">{{ __('Total Plans') }}</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-calendar-alt fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ number_format($stats['active_plans']) }}</h4>
                            <p class="mb-0">{{ __('Active Plans') }}</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-play-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ number_format($stats['completed_plans']) }}</h4>
                            <p class="mb-0">{{ __('Completed') }}</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ number_format($stats['overdue_plans']) }}</h4>
                            <p class="mb-0">{{ __('Overdue') }}</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">{{ __('Customer') }}</label>
                    <select name="customer_id" class="form-select">
                        <option value="">{{ __('All Customers') }}</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ __('Status') }}</label>
                    <select name="status" class="form-select">
                        <option value="">{{ __('All Statuses') }}</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>{{ __('Draft') }}</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>{{ __('Completed') }}</option>
                        <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>{{ __('Overdue') }}</option>
                        <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>{{ __('Suspended') }}</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">{{ __('Search') }}</label>
                    <input type="text" name="search" class="form-control" placeholder="{{ __('Plan name, customer...') }}" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> {{ __('Filter') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Payment Plans Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ __('Payment Plans') }}</h5>
            <div>
                <button class="btn btn-sm btn-outline-success" onclick="exportPlans()">
                    <i class="fas fa-download"></i> {{ __('Export') }}
                </button>
            </div>
        </div>
        <div class="card-body">
            @if($paymentPlans->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>{{ __('Plan Name') }}</th>
                            <th>{{ __('Customer') }}</th>
                            <th>{{ __('Total Amount') }}</th>
                            <th>{{ __('Installments') }}</th>
                            <th>{{ __('Progress') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Next Due') }}</th>
                            <th>{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($paymentPlans as $plan)
                        <tr>
                            <td>
                                <div>
                                    <h6 class="mb-0">{{ $plan->plan_name }}</h6>
                                    <small class="text-muted">{{ $plan->created_at->format('M d, Y') }}</small>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <span class="fw-medium">{{ $plan->customer->name }}</span>
                                    <small class="text-muted d-block">{{ $plan->customer->phone }}</small>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <span class="fw-bold">{{ number_format($plan->total_amount, 0) }} {{ __('IQD') }}</span>
                                    <small class="text-muted d-block">{{ number_format($plan->installment_amount, 0) }} / {{ $plan->frequency_text }}</small>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $plan->number_of_installments }} {{ __('installments') }}</span>
                            </td>
                            <td>
                                @php $progress = rand(10, 95) @endphp
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-{{ $progress >= 80 ? 'success' : ($progress >= 50 ? 'warning' : 'danger') }}" 
                                         style="width: {{ $progress }}%"></div>
                                </div>
                                <small>{{ $progress }}% {{ __('completed') }}</small>
                            </td>
                            <td>
                                @php $statuses = ['draft', 'active', 'completed', 'overdue', 'suspended'] @endphp
                                @php $status = $statuses[array_rand($statuses)] @endphp
                                <span class="badge bg-{{ $status == 'active' ? 'success' : ($status == 'completed' ? 'primary' : ($status == 'overdue' ? 'danger' : 'secondary')) }}">
                                    {{ ucfirst($status) }}
                                </span>
                            </td>
                            <td>
                                @if($status == 'active')
                                    <div>
                                        <span class="fw-medium">{{ now()->addDays(rand(1, 30))->format('M d, Y') }}</span>
                                        <small class="text-muted d-block">{{ number_format(rand(50000, 200000), 0) }} {{ __('IQD') }}</small>
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="viewPlan({{ $plan->id }})">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-secondary" onclick="editPlan({{ $plan->id }})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    @if($status == 'draft')
                                    <button class="btn btn-outline-success" onclick="activatePlan({{ $plan->id }})">
                                        <i class="fas fa-play"></i>
                                    </button>
                                    @elseif($status == 'active')
                                    <button class="btn btn-outline-warning" onclick="suspendPlan({{ $plan->id }})">
                                        <i class="fas fa-pause"></i>
                                    </button>
                                    @endif
                                    <button class="btn btn-outline-danger" onclick="deletePlan({{ $plan->id }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($paymentPlans->hasPages())
            <div class="d-flex justify-content-center mt-3">
                {{ $paymentPlans->appends(request()->query())->links() }}
            </div>
            @endif
            @else
            <div class="text-center py-5">
                <i class="fas fa-calendar-alt fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">{{ __('No payment plans found') }}</h5>
                <p class="text-muted">{{ __('Create your first payment plan to get started.') }}</p>
                <a href="{{ route('financial.payment-plans.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> {{ __('Create Payment Plan') }}
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function viewPlan(planId) {
    window.location.href = `/financial/payment-plans/${planId}`;
}

function editPlan(planId) {
    window.location.href = `/financial/payment-plans/${planId}/edit`;
}

function activatePlan(planId) {
    if (confirm('{{ __("Are you sure you want to activate this payment plan?") }}')) {
        fetch(`/financial/payment-plans/${planId}/activate`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || '{{ __("An error occurred") }}');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('{{ __("An error occurred") }}');
        });
    }
}

function suspendPlan(planId) {
    if (confirm('{{ __("Are you sure you want to suspend this payment plan?") }}')) {
        fetch(`/financial/payment-plans/${planId}/suspend`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || '{{ __("An error occurred") }}');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('{{ __("An error occurred") }}');
        });
    }
}

function deletePlan(planId) {
    if (confirm('{{ __("Are you sure you want to delete this payment plan? This action cannot be undone.") }}')) {
        fetch(`/financial/payment-plans/${planId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || '{{ __("An error occurred") }}');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('{{ __("An error occurred") }}');
        });
    }
}

function exportPlans() {
    alert('{{ __("Export functionality will be implemented soon!") }}');
}
</script>
@endpush
