@extends('tenant.layouts.app')

@section('title', __('Chart of Accounts'))

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">📊 {{ __('Chart of Accounts') }}</h1>
            <p class="text-muted">{{ __('Complete overview of your accounting structure') }}</p>
        </div>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAccountModal">
                <i class="fas fa-plus"></i> {{ __('Add Account') }}
            </button>
            <a href="{{ route('financial.accounting.dashboard') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> {{ __('Back to Accounting') }}
            </a>
        </div>
    </div>

    <!-- Account Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ number_format(25) }}</h4>
                            <p class="mb-0">{{ __('Assets') }}</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-building fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ number_format(12) }}</h4>
                            <p class="mb-0">{{ __('Liabilities') }}</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-credit-card fa-2x"></i>
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
                            <h4 class="mb-0">{{ number_format(8) }}</h4>
                            <p class="mb-0">{{ __('Equity') }}</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-chart-pie fa-2x"></i>
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
                            <h4 class="mb-0">{{ number_format(35) }}</h4>
                            <p class="mb-0">{{ __('Revenue & Expenses') }}</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-chart-line fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart of Accounts -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ __('Account Structure') }}</h5>
            <div>
                <button class="btn btn-sm btn-outline-primary" onclick="expandAll()">
                    <i class="fas fa-expand-arrows-alt"></i> {{ __('Expand All') }}
                </button>
                <button class="btn btn-sm btn-outline-secondary" onclick="collapseAll()">
                    <i class="fas fa-compress-arrows-alt"></i> {{ __('Collapse All') }}
                </button>
                <button class="btn btn-sm btn-outline-success" onclick="exportChart()">
                    <i class="fas fa-download"></i> {{ __('Export') }}
                </button>
            </div>
        </div>
        <div class="card-body">
            <!-- Assets Section -->
            <div class="account-section mb-4">
                <div class="account-header d-flex justify-content-between align-items-center p-3 bg-primary text-white rounded" 
                     onclick="toggleSection('assets')">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-chevron-down me-2" id="assets-icon"></i>
                        <h5 class="mb-0">{{ __('1000 - ASSETS') }}</h5>
                    </div>
                    <span class="badge bg-light text-dark">{{ number_format(2500000, 0) }} {{ __('IQD') }}</span>
                </div>
                <div class="account-content" id="assets-content">
                    <!-- Current Assets -->
                    <div class="sub-account-section ms-3 mt-2">
                        <div class="sub-account-header d-flex justify-content-between align-items-center p-2 bg-light rounded"
                             onclick="toggleSubSection('current-assets')">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-chevron-down me-2" id="current-assets-icon"></i>
                                <h6 class="mb-0">{{ __('1100 - Current Assets') }}</h6>
                            </div>
                            <span class="badge bg-primary">{{ number_format(1800000, 0) }} {{ __('IQD') }}</span>
                        </div>
                        <div class="sub-account-content ms-3" id="current-assets-content">
                            @foreach([
                                ['1101', 'Cash in Hand', 150000],
                                ['1102', 'Bank Account - Main', 850000],
                                ['1103', 'Bank Account - Savings', 300000],
                                ['1110', 'Accounts Receivable', 450000],
                                ['1120', 'Inventory', 50000]
                            ] as $account)
                            <div class="account-item d-flex justify-content-between align-items-center p-2 border-bottom">
                                <div class="d-flex align-items-center">
                                    <span class="account-code me-3 text-muted">{{ $account[0] }}</span>
                                    <span class="account-name">{{ __($account[1]) }}</span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <span class="account-balance me-3">{{ number_format($account[2], 0) }} {{ __('IQD') }}</span>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" onclick="viewAccount('{{ $account[0] }}')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-secondary" onclick="editAccount('{{ $account[0] }}')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Fixed Assets -->
                    <div class="sub-account-section ms-3 mt-2">
                        <div class="sub-account-header d-flex justify-content-between align-items-center p-2 bg-light rounded"
                             onclick="toggleSubSection('fixed-assets')">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-chevron-down me-2" id="fixed-assets-icon"></i>
                                <h6 class="mb-0">{{ __('1200 - Fixed Assets') }}</h6>
                            </div>
                            <span class="badge bg-primary">{{ number_format(700000, 0) }} {{ __('IQD') }}</span>
                        </div>
                        <div class="sub-account-content ms-3" id="fixed-assets-content">
                            @foreach([
                                ['1201', 'Equipment', 400000],
                                ['1202', 'Furniture & Fixtures', 150000],
                                ['1203', 'Vehicles', 150000]
                            ] as $account)
                            <div class="account-item d-flex justify-content-between align-items-center p-2 border-bottom">
                                <div class="d-flex align-items-center">
                                    <span class="account-code me-3 text-muted">{{ $account[0] }}</span>
                                    <span class="account-name">{{ __($account[1]) }}</span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <span class="account-balance me-3">{{ number_format($account[2], 0) }} {{ __('IQD') }}</span>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" onclick="viewAccount('{{ $account[0] }}')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-secondary" onclick="editAccount('{{ $account[0] }}')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Liabilities Section -->
            <div class="account-section mb-4">
                <div class="account-header d-flex justify-content-between align-items-center p-3 bg-danger text-white rounded" 
                     onclick="toggleSection('liabilities')">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-chevron-down me-2" id="liabilities-icon"></i>
                        <h5 class="mb-0">{{ __('2000 - LIABILITIES') }}</h5>
                    </div>
                    <span class="badge bg-light text-dark">{{ number_format(800000, 0) }} {{ __('IQD') }}</span>
                </div>
                <div class="account-content" id="liabilities-content">
                    <div class="sub-account-section ms-3 mt-2">
                        <div class="sub-account-header d-flex justify-content-between align-items-center p-2 bg-light rounded"
                             onclick="toggleSubSection('current-liabilities')">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-chevron-down me-2" id="current-liabilities-icon"></i>
                                <h6 class="mb-0">{{ __('2100 - Current Liabilities') }}</h6>
                            </div>
                            <span class="badge bg-danger">{{ number_format(500000, 0) }} {{ __('IQD') }}</span>
                        </div>
                        <div class="sub-account-content ms-3" id="current-liabilities-content">
                            @foreach([
                                ['2101', 'Accounts Payable', 300000],
                                ['2102', 'Accrued Expenses', 100000],
                                ['2103', 'Short-term Loans', 100000]
                            ] as $account)
                            <div class="account-item d-flex justify-content-between align-items-center p-2 border-bottom">
                                <div class="d-flex align-items-center">
                                    <span class="account-code me-3 text-muted">{{ $account[0] }}</span>
                                    <span class="account-name">{{ __($account[1]) }}</span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <span class="account-balance me-3">{{ number_format($account[2], 0) }} {{ __('IQD') }}</span>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" onclick="viewAccount('{{ $account[0] }}')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-secondary" onclick="editAccount('{{ $account[0] }}')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Equity Section -->
            <div class="account-section mb-4">
                <div class="account-header d-flex justify-content-between align-items-center p-3 bg-success text-white rounded" 
                     onclick="toggleSection('equity')">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-chevron-down me-2" id="equity-icon"></i>
                        <h5 class="mb-0">{{ __('3000 - EQUITY') }}</h5>
                    </div>
                    <span class="badge bg-light text-dark">{{ number_format(1200000, 0) }} {{ __('IQD') }}</span>
                </div>
                <div class="account-content" id="equity-content">
                    <div class="sub-account-content ms-3 mt-2">
                        @foreach([
                            ['3001', 'Owner\'s Capital', 1000000],
                            ['3002', 'Retained Earnings', 200000]
                        ] as $account)
                        <div class="account-item d-flex justify-content-between align-items-center p-2 border-bottom">
                            <div class="d-flex align-items-center">
                                <span class="account-code me-3 text-muted">{{ $account[0] }}</span>
                                <span class="account-name">{{ __($account[1]) }}</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="account-balance me-3">{{ number_format($account[2], 0) }} {{ __('IQD') }}</span>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="viewAccount('{{ $account[0] }}')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-secondary" onclick="editAccount('{{ $account[0] }}')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Revenue Section -->
            <div class="account-section mb-4">
                <div class="account-header d-flex justify-content-between align-items-center p-3 bg-info text-white rounded" 
                     onclick="toggleSection('revenue')">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-chevron-down me-2" id="revenue-icon"></i>
                        <h5 class="mb-0">{{ __('4000 - REVENUE') }}</h5>
                    </div>
                    <span class="badge bg-light text-dark">{{ number_format(500000, 0) }} {{ __('IQD') }}</span>
                </div>
                <div class="account-content" id="revenue-content">
                    <div class="sub-account-content ms-3 mt-2">
                        @foreach([
                            ['4001', 'Sales Revenue', 450000],
                            ['4002', 'Service Revenue', 50000]
                        ] as $account)
                        <div class="account-item d-flex justify-content-between align-items-center p-2 border-bottom">
                            <div class="d-flex align-items-center">
                                <span class="account-code me-3 text-muted">{{ $account[0] }}</span>
                                <span class="account-name">{{ __($account[1]) }}</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="account-balance me-3">{{ number_format($account[2], 0) }} {{ __('IQD') }}</span>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="viewAccount('{{ $account[0] }}')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-secondary" onclick="editAccount('{{ $account[0] }}')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Expenses Section -->
            <div class="account-section mb-4">
                <div class="account-header d-flex justify-content-between align-items-center p-3 bg-warning text-dark rounded" 
                     onclick="toggleSection('expenses')">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-chevron-down me-2" id="expenses-icon"></i>
                        <h5 class="mb-0">{{ __('5000 - EXPENSES') }}</h5>
                    </div>
                    <span class="badge bg-dark text-light">{{ number_format(200000, 0) }} {{ __('IQD') }}</span>
                </div>
                <div class="account-content" id="expenses-content">
                    <div class="sub-account-content ms-3 mt-2">
                        @foreach([
                            ['5001', 'Cost of Goods Sold', 120000],
                            ['5002', 'Rent Expense', 30000],
                            ['5003', 'Utilities Expense', 20000],
                            ['5004', 'Salaries Expense', 30000]
                        ] as $account)
                        <div class="account-item d-flex justify-content-between align-items-center p-2 border-bottom">
                            <div class="d-flex align-items-center">
                                <span class="account-code me-3 text-muted">{{ $account[0] }}</span>
                                <span class="account-name">{{ __($account[1]) }}</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="account-balance me-3">{{ number_format($account[2], 0) }} {{ __('IQD') }}</span>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="viewAccount('{{ $account[0] }}')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-secondary" onclick="editAccount('{{ $account[0] }}')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Account Modal -->
<div class="modal fade" id="addAccountModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Add New Account') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addAccountForm">
                    <div class="mb-3">
                        <label class="form-label">{{ __('Account Code') }}</label>
                        <input type="text" class="form-control" name="account_code" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Account Name') }}</label>
                        <input type="text" class="form-control" name="account_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Account Type') }}</label>
                        <select class="form-select" name="account_type" required>
                            <option value="">{{ __('Select type...') }}</option>
                            <option value="asset">{{ __('Asset') }}</option>
                            <option value="liability">{{ __('Liability') }}</option>
                            <option value="equity">{{ __('Equity') }}</option>
                            <option value="revenue">{{ __('Revenue') }}</option>
                            <option value="expense">{{ __('Expense') }}</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Parent Account') }}</label>
                        <select class="form-select" name="parent_account">
                            <option value="">{{ __('No parent (Main account)') }}</option>
                            <option value="1100">{{ __('1100 - Current Assets') }}</option>
                            <option value="1200">{{ __('1200 - Fixed Assets') }}</option>
                            <option value="2100">{{ __('2100 - Current Liabilities') }}</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-primary" onclick="addAccount()">{{ __('Add Account') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleSection(sectionId) {
    const content = document.getElementById(sectionId + '-content');
    const icon = document.getElementById(sectionId + '-icon');
    
    if (content.style.display === 'none') {
        content.style.display = 'block';
        icon.className = 'fas fa-chevron-down me-2';
    } else {
        content.style.display = 'none';
        icon.className = 'fas fa-chevron-right me-2';
    }
}

function toggleSubSection(sectionId) {
    const content = document.getElementById(sectionId + '-content');
    const icon = document.getElementById(sectionId + '-icon');
    
    if (content.style.display === 'none') {
        content.style.display = 'block';
        icon.className = 'fas fa-chevron-down me-2';
    } else {
        content.style.display = 'none';
        icon.className = 'fas fa-chevron-right me-2';
    }
}

function expandAll() {
    const contents = document.querySelectorAll('.account-content, .sub-account-content');
    const icons = document.querySelectorAll('[id$="-icon"]');
    
    contents.forEach(content => content.style.display = 'block');
    icons.forEach(icon => icon.className = 'fas fa-chevron-down me-2');
}

function collapseAll() {
    const contents = document.querySelectorAll('.account-content, .sub-account-content');
    const icons = document.querySelectorAll('[id$="-icon"]');
    
    contents.forEach(content => content.style.display = 'none');
    icons.forEach(icon => icon.className = 'fas fa-chevron-right me-2');
}

function viewAccount(accountCode) {
    alert('Viewing account: ' + accountCode);
}

function editAccount(accountCode) {
    alert('Editing account: ' + accountCode);
}

function addAccount() {
    const form = document.getElementById('addAccountForm');
    const formData = new FormData(form);
    
    // Simulate account creation
    alert('Account created successfully!');
    
    // Close modal and reset form
    const modal = bootstrap.Modal.getInstance(document.getElementById('addAccountModal'));
    modal.hide();
    form.reset();
}

function exportChart() {
    alert('Exporting chart of accounts...');
}

// Initialize collapsed state
document.addEventListener('DOMContentLoaded', function() {
    // Start with all sections expanded
    expandAll();
});
</script>

<style>
.account-header, .sub-account-header {
    cursor: pointer;
    transition: all 0.3s ease;
}

.account-header:hover, .sub-account-header:hover {
    opacity: 0.9;
    transform: translateY(-1px);
}

.account-item:hover {
    background-color: #f8f9fa;
}

.account-code {
    font-family: 'Courier New', monospace;
    font-weight: bold;
    min-width: 60px;
}

.account-balance {
    font-family: 'Courier New', monospace;
    font-weight: bold;
    min-width: 120px;
    text-align: right;
}
</style>
@endpush
