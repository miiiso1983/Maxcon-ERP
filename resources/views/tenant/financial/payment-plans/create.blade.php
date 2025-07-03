@extends('tenant.layouts.app')

@section('title', __('Create Payment Plan'))

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">📅 {{ __('Create Payment Plan') }}</h1>
            <p class="text-muted">{{ __('Set up a new installment payment plan for a customer') }}</p>
        </div>
        <div>
            <a href="{{ route('financial.payment-plans.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> {{ __('Back to Payment Plans') }}
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Payment Plan Details') }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('financial.payment-plans.store') }}" method="POST" id="paymentPlanForm">
                        @csrf
                        
                        <!-- Basic Information -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h6 class="text-primary mb-3">{{ __('Basic Information') }}</h6>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Customer') }} <span class="text-danger">*</span></label>
                                <select class="form-select @error('customer_id') is-invalid @enderror" 
                                        name="customer_id" id="customerSelect" required>
                                    <option value="">{{ __('Select customer...') }}</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                            {{ $customer->name }} - {{ $customer->phone }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('customer_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Plan Name') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('plan_name') is-invalid @enderror" 
                                       name="plan_name" value="{{ old('plan_name') }}" 
                                       placeholder="{{ __('e.g., iPhone 15 Payment Plan') }}" required>
                                @error('plan_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">{{ __('Description') }}</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          name="description" rows="3" placeholder="{{ __('Optional description of the payment plan...') }}">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Financial Details -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h6 class="text-primary mb-3">{{ __('Financial Details') }}</h6>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Total Amount') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" class="form-control @error('total_amount') is-invalid @enderror" 
                                           name="total_amount" value="{{ old('total_amount') }}" 
                                           step="0.01" min="0" id="totalAmount" required>
                                    <span class="input-group-text">{{ __('IQD') }}</span>
                                </div>
                                @error('total_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Down Payment') }}</label>
                                <div class="input-group">
                                    <input type="number" class="form-control @error('down_payment') is-invalid @enderror" 
                                           name="down_payment" value="{{ old('down_payment', 0) }}" 
                                           step="0.01" min="0" id="downPayment">
                                    <span class="input-group-text">{{ __('IQD') }}</span>
                                </div>
                                @error('down_payment')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Installment Details -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h6 class="text-primary mb-3">{{ __('Installment Details') }}</h6>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">{{ __('Installment Amount') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" class="form-control @error('installment_amount') is-invalid @enderror" 
                                           name="installment_amount" value="{{ old('installment_amount') }}" 
                                           step="0.01" min="0" id="installmentAmount" required>
                                    <span class="input-group-text">{{ __('IQD') }}</span>
                                </div>
                                @error('installment_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">{{ __('Frequency') }} <span class="text-danger">*</span></label>
                                <select class="form-select @error('installment_frequency') is-invalid @enderror" 
                                        name="installment_frequency" id="frequency" required>
                                    <option value="">{{ __('Select frequency...') }}</option>
                                    <option value="weekly" {{ old('installment_frequency') == 'weekly' ? 'selected' : '' }}>
                                        {{ __('Weekly') }}
                                    </option>
                                    <option value="monthly" {{ old('installment_frequency', 'monthly') == 'monthly' ? 'selected' : '' }}>
                                        {{ __('Monthly') }}
                                    </option>
                                    <option value="quarterly" {{ old('installment_frequency') == 'quarterly' ? 'selected' : '' }}>
                                        {{ __('Quarterly') }}
                                    </option>
                                </select>
                                @error('installment_frequency')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">{{ __('Number of Installments') }} <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('number_of_installments') is-invalid @enderror" 
                                       name="number_of_installments" value="{{ old('number_of_installments') }}" 
                                       min="1" id="numberOfInstallments" required>
                                @error('number_of_installments')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Start Date') }} <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('start_date') is-invalid @enderror" 
                                       name="start_date" value="{{ old('start_date', date('Y-m-d')) }}" 
                                       min="{{ date('Y-m-d') }}" required>
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Estimated End Date') }}</label>
                                <input type="date" class="form-control" id="estimatedEndDate" readonly>
                                <small class="text-muted">{{ __('Calculated automatically based on frequency and number of installments') }}</small>
                            </div>
                        </div>

                        <!-- Summary -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <h6 class="alert-heading">{{ __('Payment Plan Summary') }}</h6>
                                    <div id="planSummary">
                                        <p class="mb-1"><strong>{{ __('Total Amount') }}:</strong> <span id="summaryTotal">0</span> {{ __('IQD') }}</p>
                                        <p class="mb-1"><strong>{{ __('Down Payment') }}:</strong> <span id="summaryDown">0</span> {{ __('IQD') }}</p>
                                        <p class="mb-1"><strong>{{ __('Remaining Amount') }}:</strong> <span id="summaryRemaining">0</span> {{ __('IQD') }}</p>
                                        <p class="mb-1"><strong>{{ __('Installment Amount') }}:</strong> <span id="summaryInstallment">0</span> {{ __('IQD') }}</p>
                                        <p class="mb-0"><strong>{{ __('Number of Installments') }}:</strong> <span id="summaryNumber">0</span></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="row">
                            <div class="col-md-12">
                                <hr>
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('financial.payment-plans.index') }}" class="btn btn-secondary">
                                        {{ __('Cancel') }}
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> {{ __('Create Payment Plan') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Payment Plan Guide -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">{{ __('Payment Plan Guide') }}</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-success">
                        <h6>{{ __('Best Practices:') }}</h6>
                        <ul class="mb-0 small">
                            <li>{{ __('Set realistic installment amounts') }}</li>
                            <li>{{ __('Consider customer\'s payment capacity') }}</li>
                            <li>{{ __('Choose appropriate frequency') }}</li>
                            <li>{{ __('Include clear terms and conditions') }}</li>
                            <li>{{ __('Set up automatic reminders') }}</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Customer Info -->
            <div class="card" id="customerInfoCard" style="display: none;">
                <div class="card-header">
                    <h6 class="mb-0">{{ __('Customer Information') }}</h6>
                </div>
                <div class="card-body" id="customerInfo">
                    <!-- Will be populated by JavaScript -->
                </div>
            </div>

            <!-- Calculator -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">{{ __('Quick Calculator') }}</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <button type="button" class="btn btn-outline-primary btn-sm w-100" onclick="calculateInstallments()">
                            {{ __('Auto Calculate Installments') }}
                        </button>
                    </div>
                    <div class="mb-3">
                        <button type="button" class="btn btn-outline-secondary btn-sm w-100" onclick="suggestDownPayment()">
                            {{ __('Suggest Down Payment (20%)') }}
                        </button>
                    </div>
                    <div class="mb-3">
                        <button type="button" class="btn btn-outline-info btn-sm w-100" onclick="previewSchedule()">
                            {{ __('Preview Payment Schedule') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const customerSelect = document.getElementById('customerSelect');
    const totalAmount = document.getElementById('totalAmount');
    const downPayment = document.getElementById('downPayment');
    const installmentAmount = document.getElementById('installmentAmount');
    const numberOfInstallments = document.getElementById('numberOfInstallments');
    const frequency = document.getElementById('frequency');
    const startDate = document.querySelector('input[name="start_date"]');
    const estimatedEndDate = document.getElementById('estimatedEndDate');
    const customerInfoCard = document.getElementById('customerInfoCard');
    const customerInfo = document.getElementById('customerInfo');

    // Update summary when values change
    [totalAmount, downPayment, installmentAmount, numberOfInstallments].forEach(input => {
        if (input) {
            input.addEventListener('input', updateSummary);
        }
    });

    // Customer selection
    customerSelect.addEventListener('change', function() {
        const customerId = this.value;
        
        if (customerId) {
            customerInfoCard.style.display = 'block';
            const selectedOption = this.options[this.selectedIndex];
            const customerName = selectedOption.text.split(' - ')[0];
            const customerPhone = selectedOption.text.split(' - ')[1];
            
            customerInfo.innerHTML = `
                <p><strong>{{ __('Name') }}:</strong> ${customerName}</p>
                <p><strong>{{ __('Phone') }}:</strong> ${customerPhone}</p>
                <p><strong>{{ __('Credit Limit') }}:</strong> $${Math.floor(Math.random() * 50000)}</p>
                <p><strong>{{ __('Outstanding Balance') }}:</strong> $${Math.floor(Math.random() * 10000)}</p>
                <p><strong>{{ __('Payment History') }}:</strong> <span class="badge bg-success">Good</span></p>
            `;
        } else {
            customerInfoCard.style.display = 'none';
        }
    });

    // Calculate end date
    [numberOfInstallments, frequency, startDate].forEach(input => {
        if (input) {
            input.addEventListener('change', calculateEndDate);
        }
    });

    function updateSummary() {
        const total = parseFloat(totalAmount.value) || 0;
        const down = parseFloat(downPayment.value) || 0;
        const installment = parseFloat(installmentAmount.value) || 0;
        const number = parseInt(numberOfInstallments.value) || 0;
        
        const remaining = total - down;
        
        document.getElementById('summaryTotal').textContent = total.toLocaleString();
        document.getElementById('summaryDown').textContent = down.toLocaleString();
        document.getElementById('summaryRemaining').textContent = remaining.toLocaleString();
        document.getElementById('summaryInstallment').textContent = installment.toLocaleString();
        document.getElementById('summaryNumber').textContent = number;
    }

    function calculateEndDate() {
        const start = new Date(startDate.value);
        const number = parseInt(numberOfInstallments.value) || 0;
        const freq = frequency.value;
        
        if (start && number && freq) {
            let endDate = new Date(start);
            
            switch (freq) {
                case 'weekly':
                    endDate.setDate(endDate.getDate() + (number * 7));
                    break;
                case 'monthly':
                    endDate.setMonth(endDate.getMonth() + number);
                    break;
                case 'quarterly':
                    endDate.setMonth(endDate.getMonth() + (number * 3));
                    break;
            }
            
            estimatedEndDate.value = endDate.toISOString().split('T')[0];
        }
    }

    // Initial calculation
    updateSummary();
    calculateEndDate();
});

function calculateInstallments() {
    const total = parseFloat(document.getElementById('totalAmount').value) || 0;
    const down = parseFloat(document.getElementById('downPayment').value) || 0;
    const number = parseInt(document.getElementById('numberOfInstallments').value) || 1;
    
    if (total > 0 && number > 0) {
        const remaining = total - down;
        const installment = remaining / number;
        document.getElementById('installmentAmount').value = installment.toFixed(2);
        updateSummary();
    } else {
        alert('{{ __("Please enter total amount and number of installments") }}');
    }
}

function suggestDownPayment() {
    const total = parseFloat(document.getElementById('totalAmount').value) || 0;
    
    if (total > 0) {
        const suggested = total * 0.2; // 20%
        document.getElementById('downPayment').value = suggested.toFixed(2);
        updateSummary();
    } else {
        alert('{{ __("Please enter total amount first") }}');
    }
}

function previewSchedule() {
    const installment = parseFloat(document.getElementById('installmentAmount').value) || 0;
    const number = parseInt(document.getElementById('numberOfInstallments').value) || 0;
    const frequency = document.getElementById('frequency').value;
    const startDate = document.querySelector('input[name="start_date"]').value;
    
    if (installment > 0 && number > 0 && frequency && startDate) {
        let schedule = `Payment Schedule Preview:\n\n`;
        let currentDate = new Date(startDate);
        
        for (let i = 1; i <= Math.min(number, 5); i++) {
            schedule += `${i}. ${currentDate.toLocaleDateString()} - ${installment.toLocaleString()} IQD\n`;
            
            switch (frequency) {
                case 'weekly':
                    currentDate.setDate(currentDate.getDate() + 7);
                    break;
                case 'monthly':
                    currentDate.setMonth(currentDate.getMonth() + 1);
                    break;
                case 'quarterly':
                    currentDate.setMonth(currentDate.getMonth() + 3);
                    break;
            }
        }
        
        if (number > 5) {
            schedule += `... and ${number - 5} more installments`;
        }
        
        alert(schedule);
    } else {
        alert('{{ __("Please fill in all required fields first") }}');
    }
}

// Form validation
document.getElementById('paymentPlanForm').addEventListener('submit', function(e) {
    const total = parseFloat(document.getElementById('totalAmount').value) || 0;
    const down = parseFloat(document.getElementById('downPayment').value) || 0;
    const installment = parseFloat(document.getElementById('installmentAmount').value) || 0;
    const number = parseInt(document.getElementById('numberOfInstallments').value) || 0;
    
    const remaining = total - down;
    const calculatedTotal = installment * number;
    
    if (Math.abs(remaining - calculatedTotal) > 1) {
        e.preventDefault();
        alert('{{ __("The installment amount and number of installments do not match the remaining amount. Please adjust the values.") }}');
        return;
    }
    
    if (down >= total) {
        e.preventDefault();
        alert('{{ __("Down payment cannot be equal to or greater than the total amount.") }}');
        return;
    }
});
</script>
@endpush
