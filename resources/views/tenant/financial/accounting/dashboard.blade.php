@extends('tenant.layouts.app')

@section('title', __('Accounting Dashboard'))
@section('page-title', __('Accounting Dashboard'))

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">{{ __('app.dashboard') }}</a></li>
<li class="breadcrumb-item"><a href="{{ route('financial.index') }}">{{ __('Financial') }}</a></li>
<li class="breadcrumb-item active">{{ __('Accounting') }}</li>
@endsection

@section('content')
<!-- Financial Health Overview -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stats-card-success">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col {{ marginEnd('2') }}">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            {{ __('Total Assets') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ formatCurrency($stats['total_assets']) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-chart-line fa-2x opacity-75"></i>
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
                            {{ __('Total Liabilities') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ formatCurrency($stats['total_liabilities']) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stats-card-primary">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col {{ marginEnd('2') }}">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            {{ __('Total Equity') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ formatCurrency($stats['total_equity']) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-balance-scale fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stats-card-{{ $stats['net_income'] >= 0 ? 'success' : 'warning' }}">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col {{ marginEnd('2') }}">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            {{ __('Net Income') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ formatCurrency($stats['net_income']) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-{{ $stats['net_income'] >= 0 ? 'arrow-up' : 'arrow-down' }} fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Balance Check Alert -->
@if(abs($stats['balance_check']) > 0.01)
<div class="alert alert-danger" role="alert">
    <i class="fas fa-exclamation-triangle {{ marginEnd('2') }}"></i>
    <strong>{{ __('Balance Sheet Out of Balance!') }}</strong>
    {{ __('The accounting equation is not balanced. Difference: ') }}{{ formatCurrency($stats['balance_check']) }} }}
    <br>
    <small>{{ __('Assets must equal Liabilities + Equity. Please review your journal entries.') }}</small>
</div>
@endif

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2 mb-2">
                        <a href="{{ route('financial.accounting.chart-of-accounts') }}" class="btn btn-primary w-100">
                            <i class="fas fa-sitemap {{ marginEnd('2') }}"></i>{{ __('Chart of Accounts') }}
                        </a>
                    </div>
                    <div class="col-md-2 mb-2">
                        <a href="{{ route('financial.accounting.journal-entries') }}" class="btn btn-info w-100">
                            <i class="fas fa-book {{ marginEnd('2') }}"></i>{{ __('Journal Entries') }}
                        </a>
                    </div>
                    <div class="col-md-2 mb-2">
                        <a href="{{ route('financial.accounting.trial-balance') }}" class="btn btn-success w-100">
                            <i class="fas fa-balance-scale {{ marginEnd('2') }}"></i>{{ __('Trial Balance') }}
                        </a>
                    </div>
                    <div class="col-md-2 mb-2">
                        <a href="{{ route('financial.accounting.balance-sheet') }}" class="btn btn-warning w-100">
                            <i class="fas fa-chart-pie {{ marginEnd('2') }}"></i>{{ __('Balance Sheet') }}
                        </a>
                    </div>
                    <div class="col-md-2 mb-2">
                        <a href="{{ route('financial.accounting.income-statement') }}" class="btn btn-secondary w-100">
                            <i class="fas fa-chart-bar {{ marginEnd('2') }}"></i>{{ __('Income Statement') }}
                        </a>
                    </div>
                    <div class="col-md-2 mb-2">
                        <a href="{{ route('financial.accounting.general-ledger') }}" class="btn btn-dark w-100">
                            <i class="fas fa-ledger {{ marginEnd('2') }}"></i>{{ __('General Ledger') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Current Period & Alerts -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0">{{ __('Current Financial Period') }}</h6>
            </div>
            <div class="card-body">
                @if($currentPeriod)
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1">{{ $currentPeriod->name }}</h5>
                        <p class="text-muted mb-0">{{ $currentPeriod->duration }}</p>
                    </div>
                    <div>
                        <span class="badge bg-{{ $currentPeriod->status_color }} fs-6">
                            {{ $currentPeriod->status_text }}
                        </span>
                    </div>
                </div>
                @if($currentPeriod->canBeClosed())
                <div class="mt-3">
                    <button class="btn btn-warning btn-sm" onclick="closePeriod('{{ $currentPeriod->id }}')">
                        <i class="fas fa-lock {{ marginEnd('2') }}"></i>{{ __('Close Period') }}
                    </button>
                </div>
                @endif
                @else
                <div class="text-center py-3">
                    <i class="fas fa-calendar-times fa-2x text-muted mb-2"></i>
                    <p class="text-muted">{{ __('No active financial period') }}</p>
                    <button class="btn btn-primary btn-sm">
                        <i class="fas fa-plus {{ marginEnd('2') }}"></i>{{ __('Create Period') }}
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0">{{ __('Accounting Alerts') }}</h6>
            </div>
            <div class="card-body">
                @if($stats['unposted_entries'] > 0)
                <div class="alert alert-warning py-2 mb-2">
                    <i class="fas fa-exclamation-triangle {{ marginEnd('2') }}"></i>
                    <strong>{{ $stats['unposted_entries'] }}</strong> {{ __('unposted journal entries') }}
                    <a href="{{ route('financial.accounting.journal-entries', ['status' => 'unposted']) }}" class="alert-link">{{ __('Review') }}</a>
                </div>
                @endif

                @if(abs($stats['balance_check']) > 0.01)
                <div class="alert alert-danger py-2 mb-2">
                    <i class="fas fa-exclamation-circle {{ marginEnd('2') }}"></i>
                    {{ __('Balance sheet is out of balance') }}
                    <a href="{{ route('financial.accounting.trial-balance') }}" class="alert-link">{{ __('Check') }}</a>
                </div>
                @endif

                @if($stats['unposted_entries'] == 0 && abs($stats['balance_check']) <= 0.01)
                <div class="alert alert-success py-2 mb-0">
                    <i class="fas fa-check-circle {{ marginEnd('2') }}"></i>
                    {{ __('All accounts are balanced and up to date') }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Account Summary & Recent Entries -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0">{{ __('Account Summary') }}</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('Account') }}</th>
                                <th class="text-end">{{ __('Balance') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($accountSummary as $summary)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-{{ $summary['account']->type_color }} {{ marginEnd('2') }}">
                                            {{ $summary['account']->account_code }}
                                        </span>
                                        {{ $summary['account']->account_name }}
                                    </div>
                                </td>
                                <td class="text-end">
                                    <strong>{{ formatCurrency($summary['balance']) }}</strong>
                                    @if($summary['children_balance'] != 0)
                                    <br>
                                    <small class="text-muted">{{ __('Sub-accounts') }}: {{ formatCurrency($summary['children_balance']) }}</small>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="m-0">{{ __('Recent Journal Entries') }}</h6>
                <a href="{{ route('financial.accounting.journal-entries') }}" class="btn btn-sm btn-outline-primary">
                    {{ __('View All') }}
                </a>
            </div>
            <div class="card-body p-0">
                @if($recentEntries->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Description') }}</th>
                                <th class="text-end">{{ __('Amount') }}</th>
                                <th>{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentEntries as $entry)
                            <tr>
                                <td>
                                    <small>{{ $entry->entry_date->format('M d, Y') }}</small>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $entry->description }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            {{ $entry->debitAccount->account_name }} → {{ $entry->creditAccount->account_name }}
                                        </small>
                                    </div>
                                </td>
                                <td class="text-end">
                                    {{ formatCurrency($entry->amount) }} }}
                                </td>
                                <td>
                                    <span class="badge bg-{{ $entry->status_color }}">
                                        {{ $entry->status_text }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4">
                    <i class="fas fa-book fa-2x text-muted mb-2"></i>
                    <p class="text-muted">{{ __('No journal entries found') }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function closePeriod(periodId) {
    if (confirm('Are you sure you want to close this financial period? This action will post closing entries and cannot be easily undone.')) {
        // Implementation for closing period
        fetch(`/financial/periods/${periodId}/close`, {
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
                alert('Error closing period: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error closing period: ' + error.message);
        });
    }
}
</script>
@endpush
