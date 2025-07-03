@extends('tenant.layouts.app')

@section('title', __('Financial Dashboard'))

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">{{ __('app.dashboard') }}</a></li>
<li class="breadcrumb-item active">{{ __('Financial') }}</li>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Financial Overview Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <i class="fas fa-dollar-sign fa-2x text-success mb-2"></i>
                    <h6 class="small">{{ __('Total Revenue') }}</h6>
                    <div class="h4 mb-0 text-success">$125,430</div>
                    <small class="text-muted">{{ __('This Month') }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <i class="fas fa-credit-card fa-2x text-warning mb-2"></i>
                    <h6 class="small">{{ __('Accounts Receivable') }}</h6>
                    <div class="h4 mb-0 text-warning">$45,230</div>
                    <small class="text-muted">{{ __('Outstanding') }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <i class="fas fa-file-invoice fa-2x text-danger mb-2"></i>
                    <h6 class="small">{{ __('Accounts Payable') }}</h6>
                    <div class="h4 mb-0 text-danger">$23,150</div>
                    <small class="text-muted">{{ __('Due') }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <i class="fas fa-chart-line fa-2x text-info mb-2"></i>
                    <h6 class="small">{{ __('Net Profit') }}</h6>
                    <div class="h4 mb-0 text-info">$67,890</div>
                    <small class="text-muted">{{ __('This Month') }}</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Modules -->
    <div class="row">
        <!-- Accounting Module -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-calculator text-primary {{ marginEnd('2') }}"></i>
                        {{ __('Accounting') }}
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">{{ __('Manage your accounting operations, chart of accounts, and financial reports.') }}</p>
                    
                    <div class="row">
                        <div class="col-6 mb-3">
                            <a href="{{ route('financial.accounting.chart-of-accounts') }}" class="btn btn-outline-primary w-100">
                                <i class="fas fa-list {{ marginEnd('1') }}"></i>
                                {{ __('Chart of Accounts') }}
                            </a>
                        </div>
                        <div class="col-6 mb-3">
                            <a href="{{ route('financial.accounting.journal-entries') }}" class="btn btn-outline-primary w-100">
                                <i class="fas fa-book {{ marginEnd('1') }}"></i>
                                {{ __('Journal Entries') }}
                            </a>
                        </div>
                        <div class="col-6 mb-3">
                            <a href="{{ route('financial.accounting.general-ledger') }}" class="btn btn-outline-primary w-100">
                                <i class="fas fa-file-alt {{ marginEnd('1') }}"></i>
                                {{ __('General Ledger') }}
                            </a>
                        </div>
                        <div class="col-6 mb-3">
                            <a href="{{ route('financial.accounting.trial-balance') }}" class="btn btn-outline-primary w-100">
                                <i class="fas fa-balance-scale {{ marginEnd('1') }}"></i>
                                {{ __('Trial Balance') }}
                            </a>
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <a href="{{ route('financial.accounting.dashboard') }}" class="btn btn-primary">
                            <i class="fas fa-tachometer-alt {{ marginEnd('1') }}"></i>
                            {{ __('Accounting Dashboard') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Collections Module -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-money-bill-wave text-success {{ marginEnd('2') }}"></i>
                        {{ __('Collections') }}
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">{{ __('Manage debt collection, overdue accounts, and payment follow-ups.') }}</p>
                    
                    <div class="row">
                        <div class="col-6 mb-3">
                            <a href="{{ route('financial.collections.index') }}" class="btn btn-outline-success w-100">
                                <i class="fas fa-list {{ marginEnd('1') }}"></i>
                                {{ __('All Collections') }}
                            </a>
                        </div>
                        <div class="col-6 mb-3">
                            <a href="{{ route('financial.collections.create') }}" class="btn btn-outline-success w-100">
                                <i class="fas fa-plus {{ marginEnd('1') }}"></i>
                                {{ __('New Collection') }}
                            </a>
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <a href="{{ route('financial.collections.dashboard') }}" class="btn btn-success">
                            <i class="fas fa-tachometer-alt {{ marginEnd('1') }}"></i>
                            {{ __('Collections Dashboard') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Reports -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar text-info {{ marginEnd('2') }}"></i>
                        {{ __('Financial Reports') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('financial.accounting.balance-sheet') }}" class="btn btn-outline-info w-100">
                                <i class="fas fa-file-alt {{ marginEnd('1') }}"></i>
                                {{ __('Balance Sheet') }}
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('financial.accounting.income-statement') }}" class="btn btn-outline-info w-100">
                                <i class="fas fa-chart-line {{ marginEnd('1') }}"></i>
                                {{ __('Income Statement') }}
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('reports.cash-flow') }}" class="btn btn-outline-info w-100">
                                <i class="fas fa-water {{ marginEnd('1') }}"></i>
                                {{ __('Cash Flow') }}
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('reports.profit-loss') }}" class="btn btn-outline-info w-100">
                                <i class="fas fa-calculator {{ marginEnd('1') }}"></i>
                                {{ __('Profit & Loss') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.card {
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.btn {
    transition: all 0.2s;
}

.btn:hover {
    transform: translateY(-1px);
}
</style>
@endpush
