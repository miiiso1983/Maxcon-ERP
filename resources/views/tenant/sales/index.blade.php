@extends('tenant.layouts.app')

@section('title', __('app.sales'))
@section('page-title', __('app.sales'))

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">{{ __('app.dashboard') }}</a></li>
<li class="breadcrumb-item active">{{ __('app.sales') }}</li>
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
                            {{ __('Total Sales') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ formatCurrency($stats['total_sales']) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-chart-line fa-2x opacity-75"></i>
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
                            {{ __('Today Sales') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ formatCurrency($stats['today_sales']) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar-day fa-2x opacity-75"></i>
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
                            {{ __('Pending Payments') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ formatCurrency($stats['pending_payments']) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stats-card-info">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col {{ marginEnd('2') }}">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            {{ __('Total Transactions') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ $stats['total_transactions'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-receipt fa-2x opacity-75"></i>
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
                        <a href="{{ route('sales.pos') }}" class="btn btn-success w-100">
                            <i class="fas fa-cash-register {{ marginEnd('2') }}"></i>{{ __('POS') }}
                        </a>
                    </div>
                    <div class="col-md-2 mb-2">
                        <a href="{{ route('sales.create') }}" class="btn btn-primary w-100">
                            <i class="fas fa-plus {{ marginEnd('2') }}"></i>{{ __('New Sale') }}
                        </a>
                    </div>
                    <div class="col-md-2 mb-2">
                        <a href="{{ route('customers.create') }}" class="btn btn-info w-100">
                            <i class="fas fa-user-plus {{ marginEnd('2') }}"></i>{{ __('Add Customer') }}
                        </a>
                    </div>
                    <div class="col-md-2 mb-2">
                        <a href="{{ route('sales.index', ['payment_status' => 'pending']) }}" class="btn btn-warning w-100">
                            <i class="fas fa-exclamation-triangle {{ marginEnd('2') }}"></i>{{ __('Pending') }}
                        </a>
                    </div>
                    <div class="col-md-2 mb-2">
                        <a href="{{ route('sales.index', ['payment_status' => 'overdue']) }}" class="btn btn-danger w-100">
                            <i class="fas fa-calendar-times {{ marginEnd('2') }}"></i>{{ __('Overdue') }}
                        </a>
                    </div>
                    <div class="col-md-2 mb-2">
                        <a href="{{ route('reports.sales') }}" class="btn btn-secondary w-100">
                            <i class="fas fa-chart-bar {{ marginEnd('2') }}"></i>{{ __('Reports') }}
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
        <form method="GET" action="{{ route('sales.index') }}">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label for="search" class="form-label">{{ __('app.search') }}</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="{{ request('search') }}" placeholder="{{ __('Search sales...') }}">
                </div>
                <div class="col-md-2 mb-3">
                    <label for="status" class="form-label">{{ __('app.status') }}</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">{{ __('All Status') }}</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>{{ __('Draft') }}</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                        <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>{{ __('Confirmed') }}</option>
                        <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>{{ __('Delivered') }}</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>{{ __('Cancelled') }}</option>
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <label for="payment_status" class="form-label">{{ __('Payment Status') }}</label>
                    <select class="form-select" id="payment_status" name="payment_status">
                        <option value="">{{ __('All Payments') }}</option>
                        <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                        <option value="partial" {{ request('payment_status') == 'partial' ? 'selected' : '' }}>{{ __('Partial') }}</option>
                        <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>{{ __('Paid') }}</option>
                        <option value="overdue" {{ request('payment_status') == 'overdue' ? 'selected' : '' }}>{{ __('Overdue') }}</option>
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <label for="date_from" class="form-label">{{ __('Date From') }}</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" 
                           value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2 mb-3">
                    <label for="date_to" class="form-label">{{ __('Date To') }}</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" 
                           value="{{ request('date_to') }}">
                </div>
                <div class="col-md-1 mb-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times {{ marginEnd('2') }}"></i>{{ __('Clear') }}
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Sales Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="m-0">{{ __('Sales') }}</h6>
        <div>
            <span class="text-muted">{{ $sales->total() }} {{ __('sales found') }}</span>
        </div>
    </div>
    <div class="card-body p-0">
        @if($sales->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('Invoice') }}</th>
                        <th>{{ __('Customer') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Amount') }}</th>
                        <th>{{ __('Payment') }}</th>
                        <th>{{ __('app.status') }}</th>
                        <th width="150">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sales as $sale)
                    <tr>
                        <td>
                            <div>
                                <strong>{{ $sale->invoice_number }}</strong>
                                <br>
                                <small class="text-muted">{{ $sale->user->name ?? 'N/A' }}</small>
                            </div>
                        </td>
                        <td>
                            @if($sale->customer)
                            <div>
                                <strong>{{ $sale->customer->name }}</strong>
                                <br>
                                <small class="text-muted">{{ $sale->customer->customer_code }}</small>
                            </div>
                            @else
                            <span class="text-muted">{{ __('Walk-in Customer') }}</span>
                            @endif
                        </td>
                        <td>
                            <div>
                                {{ $sale->sale_date->format('M d, Y') }}
                                <br>
                                <small class="text-muted">{{ $sale->sale_date->format('H:i') }}</small>
                            </div>
                        </td>
                        <td>
                            <div>
                                <strong>{{ formatCurrency($sale->total_amount) }}</strong>
                                @if($sale->balance_amount > 0)
                                <br>
                                <small class="text-danger">{{ __('Balance') }}: {{ formatCurrency($sale->balance_amount) }}</small>
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-{{ $sale->payment_status_color }}">
                                {{ ucfirst($sale->payment_status) }}
                            </span>
                            @if($sale->is_overdue)
                            <br><small class="text-danger">{{ __('Overdue') }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $sale->status_color }}">
                                {{ ucfirst($sale->status) }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('sales.show', $sale) }}" 
                                   class="btn btn-outline-info" title="{{ __('View') }}">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('sales.print', $sale) }}" 
                                   class="btn btn-outline-secondary" title="{{ __('Print') }}" target="_blank">
                                    <i class="fas fa-print"></i>
                                </a>
                                @if($sale->canBeEdited())
                                <a href="{{ route('sales.edit', $sale) }}" 
                                   class="btn btn-outline-primary" title="{{ __('app.edit') }}">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endif
                                @if($sale->balance_amount > 0)
                                <button type="button" class="btn btn-outline-success" 
                                        onclick="addPayment('{{ $sale->id }}')" title="{{ __('Add Payment') }}">
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
        
        @if($sales->hasPages())
        <div class="card-footer">
            {{ $sales->appends(request()->query())->links() }}
        </div>
        @endif
        @else
        <div class="text-center py-5">
            <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">{{ __('No sales found') }}</h5>
            <p class="text-muted">{{ __('Try adjusting your search criteria or create a new sale.') }}</p>
            <a href="{{ route('sales.create') }}" class="btn btn-primary">
                <i class="fas fa-plus {{ marginEnd('2') }}"></i>{{ __('New Sale') }}
            </a>
        </div>
        @endif
    </div>
</div>

<!-- Add Payment Modal -->
<div class="modal fade" id="addPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Add Payment') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addPaymentForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="amount" class="form-label">{{ __('Amount') }}</label>
                        <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0.01" required>
                        <div class="form-text" id="balanceText"></div>
                    </div>
                    <div class="mb-3">
                        <label for="payment_method" class="form-label">{{ __('Payment Method') }}</label>
                        <select class="form-select" id="payment_method" name="payment_method" required>
                            <option value="cash">{{ __('Cash') }}</option>
                            <option value="card">{{ __('Card') }}</option>
                            <option value="transfer">{{ __('Bank Transfer') }}</option>
                            <option value="credit">{{ __('Credit') }}</option>
                            <option value="cheque">{{ __('Cheque') }}</option>
                            <option value="digital_wallet">{{ __('Digital Wallet') }}</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="reference" class="form-label">{{ __('Reference') }}</label>
                        <input type="text" class="form-control" id="reference" name="reference">
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">{{ __('Notes') }}</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Add Payment') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function addPayment(saleId) {
    const form = document.getElementById('addPaymentForm');
    form.action = `/sales/${saleId}/add-payment`;
    
    // You would typically fetch the sale details via AJAX here
    // For now, we'll just show the modal
    new bootstrap.Modal(document.getElementById('addPaymentModal')).show();
}
</script>
@endpush
