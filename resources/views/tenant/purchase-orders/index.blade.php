@extends('tenant.layouts.app')

@section('title', __('Purchase Orders'))
@section('page-title', __('Purchase Orders Management'))

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">{{ __('app.dashboard') }}</a></li>
<li class="breadcrumb-item active">{{ __('Purchase Orders') }}</li>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-shopping-cart {{ marginEnd('2') }}"></i>
                            {{ __('Purchase Orders Management') }}
                        </h5>
                        <div class="btn-group" role="group">
                            <a href="{{ route('purchase-orders.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus {{ marginEnd('2') }}"></i>{{ __('Create Purchase Order') }}
                            </a>
                            <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" 
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="visually-hidden">{{ __('Toggle Dropdown') }}</span>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="#">
                                        <i class="fas fa-download {{ marginEnd('2') }}"></i>{{ __('Export to Excel') }}
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#">
                                        <i class="fas fa-file-pdf {{ marginEnd('2') }}"></i>{{ __('Export to PDF') }}
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="#">
                                        <i class="fas fa-chart-bar {{ marginEnd('2') }}"></i>{{ __('Purchase Reports') }}
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-primary">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col {{ marginEnd('2') }}">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                {{ __('Total Orders') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $purchaseOrders->count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-warning">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col {{ marginEnd('2') }}">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                {{ __('Pending Orders') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $purchaseOrders->where('status', 'pending')->count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-success">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col {{ marginEnd('2') }}">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                {{ __('Approved Orders') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $purchaseOrders->where('status', 'approved')->count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-info">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col {{ marginEnd('2') }}">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                {{ __('Total Value') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ formatCurrency($purchaseOrders->sum('total_amount')) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">{{ __('Search & Filter') }}</h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('purchase-orders.index') }}">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="search" class="form-label">{{ __('Search') }}</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="{{ request('search') }}" placeholder="{{ __('Search orders...') }}">
                            </div>
                            <div class="col-md-2">
                                <label for="status" class="form-label">{{ __('Status') }}</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">{{ __('All Status') }}</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>⏳ {{ __('Pending') }}</option>
                                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>✅ {{ __('Approved') }}</option>
                                    <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>📦 {{ __('Received') }}</option>
                                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>❌ {{ __('Cancelled') }}</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="supplier" class="form-label">{{ __('Supplier') }}</label>
                                <select class="form-select" id="supplier" name="supplier">
                                    <option value="">{{ __('All Suppliers') }}</option>
                                    <option value="1">Medical Supplies Co.</option>
                                    <option value="2">Pharma Distribution Ltd.</option>
                                    <option value="3">Equipment Solutions</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="date_from" class="form-label">{{ __('From Date') }}</label>
                                <input type="date" class="form-control" id="date_from" name="date_from" 
                                       value="{{ request('date_from') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search {{ marginEnd('2') }}"></i>{{ __('Search') }}
                                    </button>
                                    <a href="{{ route('purchase-orders.index') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times {{ marginEnd('2') }}"></i>{{ __('Clear') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Purchase Orders Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-0">{{ __('Purchase Orders List') }}</h6>
                        <span class="text-muted">{{ __('Showing') }} {{ $purchaseOrders->count() }} {{ __('orders') }}</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('PO Number') }}</th>
                                    <th>{{ __('Supplier') }}</th>
                                    <th>{{ __('Order Date') }}</th>
                                    <th>{{ __('Expected Date') }}</th>
                                    <th class="text-center">{{ __('Items') }}</th>
                                    <th class="text-end">{{ __('Total Amount') }}</th>
                                    <th class="text-center">{{ __('Status') }}</th>
                                    <th class="text-center">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchaseOrders as $order)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center {{ marginEnd('3') }}">
                                                <i class="fas fa-file-alt text-white"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $order['po_number'] }}</h6>
                                                <small class="text-muted">{{ __('Created by') }}: {{ $order['created_by'] }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $order['supplier_name'] }}</strong>
                                        </div>
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($order['order_date'])->format('M d, Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($order['expected_date'])->format('M d, Y') }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark">{{ $order['items_count'] }}</span>
                                    </td>
                                    <td class="text-end">
                                        <strong class="text-success">{{ formatCurrency($order['total_amount']) }}</strong>
                                    </td>
                                    <td class="text-center">
                                        @if($order['status'] == 'pending')
                                            <span class="badge bg-warning">{{ __('Pending') }}</span>
                                        @elseif($order['status'] == 'approved')
                                            <span class="badge bg-success">{{ __('Approved') }}</span>
                                        @elseif($order['status'] == 'received')
                                            <span class="badge bg-info">{{ __('Received') }}</span>
                                        @else
                                            <span class="badge bg-secondary">{{ __('Cancelled') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('purchase-orders.show', $order['id']) }}" class="btn btn-sm btn-outline-primary" title="{{ __('View') }}">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($order['status'] == 'pending')
                                            <a href="{{ route('purchase-orders.edit', $order['id']) }}" class="btn btn-sm btn-outline-warning" title="{{ __('Edit') }}">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @endif
                                            <a href="{{ route('purchase-orders.print', $order['id']) }}" class="btn btn-sm btn-outline-info" title="{{ __('Print') }}" target="_blank">
                                                <i class="fas fa-print"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.avatar-sm {
    width: 40px;
    height: 40px;
}

.border-left-primary {
    border-left: 4px solid #4e73df !important;
}

.border-left-success {
    border-left: 4px solid #1cc88a !important;
}

.border-left-info {
    border-left: 4px solid #36b9cc !important;
}

.border-left-warning {
    border-left: 4px solid #f6c23e !important;
}

.table th {
    border-top: none;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.8rem;
    letter-spacing: 0.5px;
}

.card {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    border: 1px solid #e3e6f0;
}

.text-gray-800 {
    color: #5a5c69 !important;
}

.text-gray-300 {
    color: #dddfeb !important;
}
</style>
@endpush
