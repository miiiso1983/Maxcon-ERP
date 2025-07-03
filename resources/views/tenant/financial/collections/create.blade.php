@extends('tenant.layouts.app')

@section('title', __('Create Collection'))

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">💰 {{ __('Create New Collection') }}</h1>
            <p class="text-muted">{{ __('Record a new collection activity') }}</p>
        </div>
        <div>
            <a href="{{ route('financial.collections.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> {{ __('Back to Collections') }}
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Collection Details') }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('financial.collections.store') }}" method="POST" id="collectionForm">
                        @csrf
                        
                        <!-- Customer Information -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h6 class="text-primary mb-3">{{ __('Customer Information') }}</h6>
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
                                <label class="form-label">{{ __('Collection Type') }} <span class="text-danger">*</span></label>
                                <select class="form-select @error('collection_type') is-invalid @enderror" 
                                        name="collection_type" required>
                                    <option value="">{{ __('Select type...') }}</option>
                                    <option value="payment" {{ old('collection_type') == 'payment' ? 'selected' : '' }}>
                                        {{ __('Payment Collection') }}
                                    </option>
                                    <option value="follow_up" {{ old('collection_type') == 'follow_up' ? 'selected' : '' }}>
                                        {{ __('Follow-up Call') }}
                                    </option>
                                    <option value="visit" {{ old('collection_type') == 'visit' ? 'selected' : '' }}>
                                        {{ __('Customer Visit') }}
                                    </option>
                                    <option value="reminder" {{ old('collection_type') == 'reminder' ? 'selected' : '' }}>
                                        {{ __('Payment Reminder') }}
                                    </option>
                                </select>
                                @error('collection_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Collection Details -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h6 class="text-primary mb-3">{{ __('Collection Details') }}</h6>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Amount') }}</label>
                                <input type="number" class="form-control @error('amount') is-invalid @enderror" 
                                       name="amount" value="{{ old('amount') }}" step="0.01" min="0">
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Priority') }}</label>
                                <select class="form-select @error('priority') is-invalid @enderror" name="priority">
                                    <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>
                                        {{ __('Low') }}
                                    </option>
                                    <option value="medium" {{ old('priority', 'medium') == 'medium' ? 'selected' : '' }}>
                                        {{ __('Medium') }}
                                    </option>
                                    <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>
                                        {{ __('High') }}
                                    </option>
                                    <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>
                                        {{ __('Urgent') }}
                                    </option>
                                </select>
                                @error('priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Assigned Collector') }}</label>
                                <select class="form-select @error('assigned_to') is-invalid @enderror" name="assigned_to">
                                    <option value="">{{ __('Select collector...') }}</option>
                                    @foreach($collectors as $collector)
                                        <option value="{{ $collector->id }}" {{ old('assigned_to') == $collector->id ? 'selected' : '' }}>
                                            {{ $collector->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('assigned_to')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Follow-up Date') }}</label>
                                <input type="date" class="form-control @error('follow_up_date') is-invalid @enderror" 
                                       name="follow_up_date" value="{{ old('follow_up_date') }}" 
                                       min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                                @error('follow_up_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">{{ __('Notes') }}</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                          name="notes" rows="4" placeholder="{{ __('Add any relevant notes...') }}">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Outstanding Sales -->
                        <div class="row mb-4" id="outstandingSalesSection" style="display: none;">
                            <div class="col-md-12">
                                <h6 class="text-primary mb-3">{{ __('Outstanding Sales') }}</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Select') }}</th>
                                                <th>{{ __('Sale #') }}</th>
                                                <th>{{ __('Date') }}</th>
                                                <th>{{ __('Amount') }}</th>
                                                <th>{{ __('Outstanding') }}</th>
                                                <th>{{ __('Status') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody id="outstandingSalesTable">
                                            <!-- Will be populated by JavaScript -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="row">
                            <div class="col-md-12">
                                <hr>
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('financial.collections.index') }}" class="btn btn-secondary">
                                        {{ __('Cancel') }}
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> {{ __('Create Collection') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Collection Tips -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">{{ __('Collection Tips') }}</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6>{{ __('Best Practices:') }}</h6>
                        <ul class="mb-0 small">
                            <li>{{ __('Be polite and professional') }}</li>
                            <li>{{ __('Document all interactions') }}</li>
                            <li>{{ __('Set clear follow-up dates') }}</li>
                            <li>{{ __('Offer payment plans if needed') }}</li>
                            <li>{{ __('Keep detailed notes') }}</li>
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
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Outstanding sales data (would come from backend)
const outstandingSalesData = @json($outstandingSales ?? []);

document.addEventListener('DOMContentLoaded', function() {
    const customerSelect = document.getElementById('customerSelect');
    const outstandingSalesSection = document.getElementById('outstandingSalesSection');
    const outstandingSalesTable = document.getElementById('outstandingSalesTable');
    const customerInfoCard = document.getElementById('customerInfoCard');
    const customerInfo = document.getElementById('customerInfo');

    customerSelect.addEventListener('change', function() {
        const customerId = this.value;
        
        if (customerId) {
            // Show customer info
            customerInfoCard.style.display = 'block';
            const selectedOption = this.options[this.selectedIndex];
            customerInfo.innerHTML = `
                <p><strong>{{ __('Name') }}:</strong> ${selectedOption.text.split(' - ')[0]}</p>
                <p><strong>{{ __('Phone') }}:</strong> ${selectedOption.text.split(' - ')[1]}</p>
                <p><strong>{{ __('Outstanding Amount') }}:</strong> $${Math.floor(Math.random() * 10000)}</p>
                <p><strong>{{ __('Last Payment') }}:</strong> ${new Date(Date.now() - Math.random() * 30 * 24 * 60 * 60 * 1000).toLocaleDateString()}</p>
            `;

            // Show outstanding sales if any
            if (outstandingSalesData[customerId]) {
                outstandingSalesSection.style.display = 'block';
                populateOutstandingSales(outstandingSalesData[customerId]);
            } else {
                // Generate sample data
                outstandingSalesSection.style.display = 'block';
                generateSampleSales();
            }
        } else {
            customerInfoCard.style.display = 'none';
            outstandingSalesSection.style.display = 'none';
        }
    });

    function populateOutstandingSales(sales) {
        let html = '';
        sales.forEach(sale => {
            html += `
                <tr>
                    <td><input type="checkbox" name="selected_sales[]" value="${sale.id}"></td>
                    <td>${sale.sale_number}</td>
                    <td>${new Date(sale.sale_date).toLocaleDateString()}</td>
                    <td>$${sale.total_amount}</td>
                    <td>$${sale.outstanding_amount}</td>
                    <td><span class="badge bg-warning">${sale.payment_status}</span></td>
                </tr>
            `;
        });
        outstandingSalesTable.innerHTML = html;
    }

    function generateSampleSales() {
        let html = '';
        for (let i = 1; i <= 3; i++) {
            const amount = Math.floor(Math.random() * 5000) + 1000;
            const outstanding = Math.floor(amount * 0.7);
            html += `
                <tr>
                    <td><input type="checkbox" name="selected_sales[]" value="${i}"></td>
                    <td>SALE-${String(i).padStart(4, '0')}</td>
                    <td>${new Date(Date.now() - Math.random() * 60 * 24 * 60 * 60 * 1000).toLocaleDateString()}</td>
                    <td>$${amount}</td>
                    <td>$${outstanding}</td>
                    <td><span class="badge bg-warning">Partial</span></td>
                </tr>
            `;
        }
        outstandingSalesTable.innerHTML = html;
    }
});

// Form validation
document.getElementById('collectionForm').addEventListener('submit', function(e) {
    const customerSelect = document.getElementById('customerSelect');
    const collectionType = document.querySelector('select[name="collection_type"]');
    
    if (!customerSelect.value) {
        e.preventDefault();
        alert("{{ __('Please select a customer') }}");
        customerSelect.focus();
        return;
    }
    
    if (!collectionType.value) {
        e.preventDefault();
        alert("{{ __('Please select a collection type') }}");
        collectionType.focus();
        return;
    }
});
</script>
@endpush
