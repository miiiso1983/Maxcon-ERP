@extends('tenant.layouts.app')

@section('title', __('Journal Entries'))

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">📖 {{ __('Journal Entries') }}</h1>
            <p class="text-muted">{{ __('Record and manage all accounting transactions') }}</p>
        </div>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEntryModal">
                <i class="fas fa-plus"></i> {{ __('New Entry') }}
            </button>
            <a href="{{ route('financial.accounting.dashboard') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> {{ __('Back to Accounting') }}
            </a>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">{{ __('Date From') }}</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ __('Date To') }}</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ __('Status') }}</label>
                    <select name="status" class="form-select">
                        <option value="">{{ __('All Statuses') }}</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>{{ __('Draft') }}</option>
                        <option value="posted" {{ request('status') == 'posted' ? 'selected' : '' }}>{{ __('Posted') }}</option>
                        <option value="unposted" {{ request('status') == 'unposted' ? 'selected' : '' }}>{{ __('Unposted') }}</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ __('Search') }}</label>
                    <input type="text" name="search" class="form-control" placeholder="{{ __('Reference, description...') }}" value="{{ request('search') }}">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> {{ __('Filter') }}
                    </button>
                    <a href="{{ route('financial.accounting.journal-entries') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-refresh"></i> {{ __('Reset') }}
                    </a>
                    <button type="button" class="btn btn-outline-success" onclick="exportEntries()">
                        <i class="fas fa-download"></i> {{ __('Export') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ number_format(156) }}</h4>
                            <p class="mb-0">{{ __('Total Entries') }}</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-book fa-2x"></i>
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
                            <h4 class="mb-0">{{ number_format(142) }}</h4>
                            <p class="mb-0">{{ __('Posted') }}</p>
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
                            <h4 class="mb-0">{{ number_format(14) }}</h4>
                            <p class="mb-0">{{ __('Unposted') }}</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clock fa-2x"></i>
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
                            <h4 class="mb-0">{{ number_format(2850000, 0) }} {{ __('IQD') }}</h4>
                            <p class="mb-0">{{ __('Total Amount') }}</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-calculator fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Journal Entries Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ __('Journal Entries') }}</h5>
            <div>
                <button class="btn btn-sm btn-outline-primary" onclick="refreshEntries()">
                    <i class="fas fa-refresh"></i> {{ __('Refresh') }}
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Reference') }}</th>
                            <th>{{ __('Description') }}</th>
                            <th>{{ __('Debit Account') }}</th>
                            <th>{{ __('Credit Account') }}</th>
                            <th>{{ __('Amount') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for($i = 1; $i <= 20; $i++)
                        <tr>
                            <td>{{ now()->subDays(rand(0, 30))->format('M d, Y') }}</td>
                            <td>
                                <span class="font-monospace">JE-{{ str_pad($i, 4, '0', STR_PAD_LEFT) }}</span>
                            </td>
                            <td>
                                @php $descriptions = [
                                    'Sales transaction',
                                    'Purchase of inventory',
                                    'Payment to supplier',
                                    'Customer payment received',
                                    'Office rent payment',
                                    'Salary payment',
                                    'Utility bill payment',
                                    'Bank charges',
                                    'Equipment purchase',
                                    'Insurance payment'
                                ] @endphp
                                {{ $descriptions[array_rand($descriptions)] }}
                            </td>
                            <td>
                                @php $accounts = [
                                    ['1101', 'Cash in Hand'],
                                    ['1102', 'Bank Account'],
                                    ['1110', 'Accounts Receivable'],
                                    ['1120', 'Inventory'],
                                    ['5001', 'Cost of Goods Sold'],
                                    ['5002', 'Rent Expense']
                                ] @endphp
                                @php $debitAccount = $accounts[array_rand($accounts)] @endphp
                                <div>
                                    <span class="badge bg-primary">{{ $debitAccount[0] }}</span>
                                    <small class="text-muted d-block">{{ $debitAccount[1] }}</small>
                                </div>
                            </td>
                            <td>
                                @php $creditAccount = $accounts[array_rand($accounts)] @endphp
                                <div>
                                    <span class="badge bg-success">{{ $creditAccount[0] }}</span>
                                    <small class="text-muted d-block">{{ $creditAccount[1] }}</small>
                                </div>
                            </td>
                            <td>
                                @php $amount = rand(50000, 500000) @endphp
                                <span class="font-monospace">{{ number_format($amount, 0) }} {{ __('IQD') }}</span>
                            </td>
                            <td>
                                @php $status = ['posted', 'unposted', 'draft'][array_rand(['posted', 'unposted', 'draft'])] @endphp
                                <span class="badge bg-{{ $status == 'posted' ? 'success' : ($status == 'unposted' ? 'warning' : 'secondary') }}">
                                    {{ ucfirst($status) }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="viewEntry({{ $i }})">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-secondary" onclick="editEntry({{ $i }})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    @if($status != 'posted')
                                    <button class="btn btn-outline-success" onclick="postEntry({{ $i }})">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    @endif
                                    <button class="btn btn-outline-danger" onclick="deleteEntry({{ $i }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    <small class="text-muted">{{ __('Showing 1 to 20 of 156 entries') }}</small>
                </div>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item disabled">
                            <span class="page-link">{{ __('Previous') }}</span>
                        </li>
                        <li class="page-item active">
                            <span class="page-link">1</span>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="#">2</a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="#">3</a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="#">{{ __('Next') }}</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- Add Entry Modal -->
<div class="modal fade" id="addEntryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('New Journal Entry') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addEntryForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Date') }}</label>
                            <input type="date" class="form-control" name="entry_date" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Reference') }}</label>
                            <input type="text" class="form-control" name="reference" placeholder="JE-0001" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">{{ __('Description') }}</label>
                            <textarea class="form-control" name="description" rows="2" required></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Debit Account') }}</label>
                            <select class="form-select" name="debit_account" required>
                                <option value="">{{ __('Select account...') }}</option>
                                <option value="1101">1101 - {{ __('Cash in Hand') }}</option>
                                <option value="1102">1102 - {{ __('Bank Account') }}</option>
                                <option value="1110">1110 - {{ __('Accounts Receivable') }}</option>
                                <option value="1120">1120 - {{ __('Inventory') }}</option>
                                <option value="5001">5001 - {{ __('Cost of Goods Sold') }}</option>
                                <option value="5002">5002 - {{ __('Rent Expense') }}</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Credit Account') }}</label>
                            <select class="form-select" name="credit_account" required>
                                <option value="">{{ __('Select account...') }}</option>
                                <option value="1101">1101 - {{ __('Cash in Hand') }}</option>
                                <option value="1102">1102 - {{ __('Bank Account') }}</option>
                                <option value="2101">2101 - {{ __('Accounts Payable') }}</option>
                                <option value="4001">4001 - {{ __('Sales Revenue') }}</option>
                                <option value="4002">4002 - {{ __('Service Revenue') }}</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Amount') }}</label>
                            <input type="number" class="form-control" name="amount" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Status') }}</label>
                            <select class="form-select" name="status">
                                <option value="draft">{{ __('Draft') }}</option>
                                <option value="unposted">{{ __('Unposted') }}</option>
                                <option value="posted">{{ __('Posted') }}</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-primary" onclick="saveEntry()">{{ __('Save Entry') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function viewEntry(id) {
    alert('Viewing journal entry ' + id);
}

function editEntry(id) {
    alert('Editing journal entry ' + id);
}

function postEntry(id) {
    if (confirm('Are you sure you want to post this journal entry? This action cannot be undone.')) {
        alert('Journal entry ' + id + ' has been posted');
        // Refresh the page or update the row
        location.reload();
    }
}

function deleteEntry(id) {
    if (confirm('Are you sure you want to delete this journal entry?')) {
        alert('Journal entry ' + id + ' has been deleted');
        // Refresh the page or remove the row
        location.reload();
    }
}

function saveEntry() {
    const form = document.getElementById('addEntryForm');
    const formData = new FormData(form);
    
    // Validate form
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    // Simulate saving
    alert('Journal entry saved successfully!');
    
    // Close modal and reset form
    const modal = bootstrap.Modal.getInstance(document.getElementById('addEntryModal'));
    modal.hide();
    form.reset();
    
    // Refresh the page
    location.reload();
}

function refreshEntries() {
    location.reload();
}

function exportEntries() {
    alert('Exporting journal entries...');
}

// Auto-generate reference number
document.addEventListener('DOMContentLoaded', function() {
    const referenceInput = document.querySelector('input[name="reference"]');
    if (referenceInput && !referenceInput.value) {
        const nextNumber = Math.floor(Math.random() * 9999) + 1;
        referenceInput.value = 'JE-' + String(nextNumber).padStart(4, '0');
    }
});
</script>
@endpush
