@extends('tenant.layouts.app')

@section('title', __('Edit Sale'))
@section('page-title', __('Edit Sale'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">{{ __('app.dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('sales.index') }}">{{ __('app.sales') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Edit Sale') }}</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-edit {{ marginEnd('2') }}"></i>
                        {{ __('Edit Sale') }} #SALE-{{ date('Ymd') }}-001
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('sales.update', 1) }}" method="POST" id="sale-form">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <!-- Customer Information -->
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">{{ __('Customer Information') }}</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="customer_id" class="form-label">{{ __('Customer') }} <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <select class="form-select @error('customer_id') is-invalid @enderror" 
                                                        id="customer_id" name="customer_id" required>
                                                    <option value="">{{ __('Select Customer') }}</option>
                                                    <option value="1" selected>{{ __('Ahmed Al-Rashid') }}</option>
                                                    <option value="2">{{ __('Fatima Hassan') }}</option>
                                                    <option value="3">{{ __('Omar Khalil') }}</option>
                                                    <option value="4">{{ __('Layla Ahmed') }}</option>
                                                    <option value="5">{{ __('Hassan Ali') }}</option>
                                                </select>
                                                <button type="button" class="btn btn-outline-primary" onclick="addNewCustomer()">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                            @error('customer_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="sale_date" class="form-label">{{ __('Sale Date') }} <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control @error('sale_date') is-invalid @enderror" 
                                                   id="sale_date" name="sale_date" value="{{ date('Y-m-d') }}" required>
                                            @error('sale_date')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="reference_number" class="form-label">{{ __('Reference Number') }}</label>
                                            <input type="text" class="form-control @error('reference_number') is-invalid @enderror" 
                                                   id="reference_number" name="reference_number" value="SALE-{{ date('Ymd') }}-001" readonly>
                                            @error('reference_number')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="notes" class="form-label">{{ __('Notes') }}</label>
                                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                                      id="notes" name="notes" rows="3" placeholder="{{ __('Additional notes about this sale...') }}">{{ __('Customer requested express delivery. Items to be delivered by 3 PM today.') }}</textarea>
                                            @error('notes')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Information -->
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">{{ __('Payment Information') }}</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="payment_method" class="form-label">{{ __('Payment Method') }} <span class="text-danger">*</span></label>
                                            <select class="form-select @error('payment_method') is-invalid @enderror" 
                                                    id="payment_method" name="payment_method" required>
                                                <option value="">{{ __('Select Payment Method') }}</option>
                                                <option value="cash" selected>{{ __('Cash') }}</option>
                                                <option value="card">{{ __('Credit/Debit Card') }}</option>
                                                <option value="bank_transfer">{{ __('Bank Transfer') }}</option>
                                                <option value="check">{{ __('Check') }}</option>
                                            </select>
                                            @error('payment_method')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="payment_status" class="form-label">{{ __('Payment Status') }} <span class="text-danger">*</span></label>
                                            <select class="form-select @error('payment_status') is-invalid @enderror" 
                                                    id="payment_status" name="payment_status" required>
                                                <option value="">{{ __('Select Payment Status') }}</option>
                                                <option value="paid" selected>{{ __('Paid') }}</option>
                                                <option value="partial">{{ __('Partially Paid') }}</option>
                                                <option value="pending">{{ __('Pending') }}</option>
                                            </select>
                                            @error('payment_status')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3" id="paid_amount_field" style="display: none;">
                                            <label for="paid_amount" class="form-label">{{ __('Paid Amount') }}</label>
                                            <div class="input-group">
                                                <span class="input-group-text">د.ع</span>
                                                <input type="number" class="form-control @error('paid_amount') is-invalid @enderror" 
                                                       id="paid_amount" name="paid_amount" value="57000" 
                                                       step="0.01" min="0">
                                            </div>
                                            @error('paid_amount')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="due_date" class="form-label">{{ __('Due Date') }}</label>
                                            <input type="date" class="form-control @error('due_date') is-invalid @enderror" 
                                                   id="due_date" name="due_date" value="">
                                            @error('due_date')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sale Items -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="card-title mb-0">{{ __('Sale Items') }}</h6>
                                    <button type="button" class="btn btn-primary btn-sm" onclick="addSaleItem()">
                                        <i class="fas fa-plus {{ marginEnd('2') }}"></i>{{ __('Add Item') }}
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="sale-items-table">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 30%;">{{ __('Product') }}</th>
                                                <th style="width: 15%;">{{ __('Quantity') }}</th>
                                                <th style="width: 15%;">{{ __('Unit Price') }}</th>
                                                <th style="width: 15%;">{{ __('Discount') }}</th>
                                                <th style="width: 15%;">{{ __('Total') }}</th>
                                                <th style="width: 10%;">{{ __('Action') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody id="sale-items-body">
                                            <!-- Existing items will be loaded here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Sale Summary -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h6 class="card-title mb-0">{{ __('Sale Summary') }}</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="discount_type" class="form-label">{{ __('Discount Type') }}</label>
                                            <select class="form-select" id="discount_type" name="discount_type" onchange="calculateTotals()">
                                                <option value="none">{{ __('No Discount') }}</option>
                                                <option value="percentage">{{ __('Percentage') }}</option>
                                                <option value="fixed" selected>{{ __('Fixed Amount') }}</option>
                                            </select>
                                        </div>
                                        <div class="mb-3" id="discount_value_field">
                                            <label for="discount_value" class="form-label">{{ __('Discount Value') }}</label>
                                            <input type="number" class="form-control" id="discount_value" name="discount_value" 
                                                   step="0.01" min="0" value="2000" onchange="calculateTotals()">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <tr>
                                                    <td><strong>{{ __('Subtotal') }}:</strong></td>
                                                    <td class="text-end" id="subtotal-display">{{ formatCurrency(59000) }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>{{ __('Discount') }}:</strong></td>
                                                    <td class="text-end" id="discount-display">{{ formatCurrency(2000) }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>{{ __('Tax') }} (0%):</strong></td>
                                                    <td class="text-end" id="tax-display">{{ formatCurrency(0) }}</td>
                                                </tr>
                                                <tr class="table-primary">
                                                    <td><strong>{{ __('Total') }}:</strong></td>
                                                    <td class="text-end"><strong id="total-display">{{ formatCurrency(57000) }}</strong></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Hidden inputs for totals -->
                        <input type="hidden" id="subtotal" name="subtotal" value="59000">
                        <input type="hidden" id="discount_amount" name="discount_amount" value="2000">
                        <input type="hidden" id="tax_amount" name="tax_amount" value="0">
                        <input type="hidden" id="total_amount" name="total_amount" value="57000">

                        <!-- Submit Buttons -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-end">
                                    <a href="{{ route('sales.show', 1) }}" class="btn btn-outline-info {{ marginEnd('2') }}">
                                        <i class="fas fa-eye {{ marginEnd('2') }}"></i>{{ __('View Sale') }}
                                    </a>
                                    <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary {{ marginEnd('2') }}">
                                        <i class="fas fa-times {{ marginEnd('2') }}"></i>{{ __('Cancel') }}
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save {{ marginEnd('2') }}"></i>{{ __('Update Sale') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Customer Modal -->
<div class="modal fade" id="addCustomerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Add New Customer') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="customer-form">
                    <div class="mb-3">
                        <label for="customer-name" class="form-label">{{ __('Name') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="customer-name" required>
                    </div>
                    <div class="mb-3">
                        <label for="customer-phone" class="form-label">{{ __('Phone') }}</label>
                        <input type="tel" class="form-control" id="customer-phone">
                    </div>
                    <div class="mb-3">
                        <label for="customer-email" class="form-label">{{ __('Email') }}</label>
                        <input type="email" class="form-control" id="customer-email">
                    </div>
                    <div class="mb-3">
                        <label for="customer-address" class="form-label">{{ __('Address') }}</label>
                        <textarea class="form-control" id="customer-address" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-primary" onclick="saveCustomer()">{{ __('Save Customer') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let itemCounter = 0;

// Sample products data
const products = [
    { id: 1, name: 'Paracetamol 500mg', sku: 'PAR001', price: 5000, stock: 100 },
    { id: 2, name: 'Amoxicillin 250mg', sku: 'AMX001', price: 15000, stock: 50 },
    { id: 3, name: 'Digital Thermometer', sku: 'THERM001', price: 25000, stock: 20 },
    { id: 4, name: 'Vitamin D3 Tablets', sku: 'VIT001', price: 8000, stock: 200 },
    { id: 5, name: 'Surgical Mask (50pcs)', sku: 'MASK001', price: 12000, stock: 100 },
    { id: 6, name: 'Blood Pressure Monitor', sku: 'BP001', price: 45000, stock: 15 },
    { id: 7, name: 'Insulin Pen', sku: 'INS001', price: 35000, stock: 30 },
    { id: 8, name: 'Bandages Pack', sku: 'BAND001', price: 3000, stock: 150 },
];

// Existing sale items
const existingItems = [
    { product_id: 1, quantity: 2, unit_price: 5000, discount: 0 },
    { product_id: 3, quantity: 1, unit_price: 25000, discount: 2000 },
    { product_id: 4, quantity: 3, unit_price: 8000, discount: 0 }
];

function addSaleItem(existingItem = null) {
    itemCounter++;
    const tbody = document.getElementById('sale-items-body');
    const row = document.createElement('tr');
    row.id = `item-row-${itemCounter}`;
    
    const selectedProductId = existingItem ? existingItem.product_id : '';
    const quantity = existingItem ? existingItem.quantity : 1;
    const unitPrice = existingItem ? existingItem.unit_price : '';
    const discount = existingItem ? existingItem.discount : 0;
    
    row.innerHTML = `
        <td>
            <select class="form-select product-select" name="items[${itemCounter}][product_id]" onchange="updateItemPrice(${itemCounter})" required data-placeholder="{{ __('Select Product') }}">
                <option value="">{{ __('Select Product') }}</option>
                ${products.map(product =>
                    `<option value="${product.id}" data-price="${product.price}" data-stock="${product.stock}"
                        ${product.id == selectedProductId ? 'selected' : ''}>
                        ${product.name} (${product.sku}) - {{ __('Stock') }}: ${product.stock}
                    </option>`
                ).join('')}
            </select>
        </td>
        <td>
            <input type="number" class="form-control" name="items[${itemCounter}][quantity]" 
                   min="1" value="${quantity}" onchange="calculateItemTotal(${itemCounter})" required>
        </td>
        <td>
            <div class="input-group">
                <span class="input-group-text">د.ع</span>
                <input type="number" class="form-control" name="items[${itemCounter}][unit_price]" 
                       step="0.01" min="0" value="${unitPrice}" onchange="calculateItemTotal(${itemCounter})" required>
            </div>
        </td>
        <td>
            <div class="input-group">
                <input type="number" class="form-control" name="items[${itemCounter}][discount]" 
                       step="0.01" min="0" value="${discount}" onchange="calculateItemTotal(${itemCounter})">
                <span class="input-group-text">د.ع</span>
            </div>
        </td>
        <td>
            <span class="item-total" id="item-total-${itemCounter}">{{ formatCurrency(0) }}</span>
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItem(${itemCounter})">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    
    tbody.appendChild(row);
    
    if (existingItem) {
        calculateItemTotal(itemCounter);
    }
}

function removeItem(itemId) {
    const row = document.getElementById(`item-row-${itemId}`);
    if (row) {
        row.remove();
        calculateTotals();
    }
}

function updateItemPrice(itemId) {
    const select = document.querySelector(`select[name="items[${itemId}][product_id]"]`);
    const priceInput = document.querySelector(`input[name="items[${itemId}][unit_price]"]`);
    
    if (select.value) {
        const selectedOption = select.options[select.selectedIndex];
        const price = selectedOption.getAttribute('data-price');
        priceInput.value = price;
        calculateItemTotal(itemId);
    }
}

function calculateItemTotal(itemId) {
    const quantity = parseFloat(document.querySelector(`input[name="items[${itemId}][quantity]"]`).value) || 0;
    const unitPrice = parseFloat(document.querySelector(`input[name="items[${itemId}][unit_price]"]`).value) || 0;
    const discount = parseFloat(document.querySelector(`input[name="items[${itemId}][discount]"]`).value) || 0;
    
    const total = (quantity * unitPrice) - discount;
    document.getElementById(`item-total-${itemId}`).textContent = formatCurrency(total);
    
    calculateTotals();
}

function calculateTotals() {
    let subtotal = 0;
    
    // Calculate subtotal from all items
    document.querySelectorAll('.item-total').forEach(element => {
        const text = element.textContent.replace(/[^\d.-]/g, '');
        subtotal += parseFloat(text) || 0;
    });
    
    // Calculate discount
    const discountType = document.getElementById('discount_type').value;
    const discountValue = parseFloat(document.getElementById('discount_value').value) || 0;
    let discountAmount = 0;
    
    if (discountType === 'percentage') {
        discountAmount = (subtotal * discountValue) / 100;
    } else if (discountType === 'fixed') {
        discountAmount = discountValue;
    }
    
    // Calculate tax (0% for now)
    const taxAmount = 0;
    
    // Calculate total
    const total = subtotal - discountAmount + taxAmount;
    
    // Update displays
    document.getElementById('subtotal-display').textContent = formatCurrency(subtotal);
    document.getElementById('discount-display').textContent = formatCurrency(discountAmount);
    document.getElementById('tax-display').textContent = formatCurrency(taxAmount);
    document.getElementById('total-display').textContent = formatCurrency(total);
    
    // Update hidden inputs
    document.getElementById('subtotal').value = subtotal;
    document.getElementById('discount_amount').value = discountAmount;
    document.getElementById('tax_amount').value = taxAmount;
    document.getElementById('total_amount').value = total;
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('ar-IQ', {
        style: 'currency',
        currency: 'IQD',
        minimumFractionDigits: 0
    }).format(amount);
}

function addNewCustomer() {
    const modal = new bootstrap.Modal(document.getElementById('addCustomerModal'));
    modal.show();
}

function saveCustomer() {
    const name = document.getElementById('customer-name').value;
    const phone = document.getElementById('customer-phone').value;
    const email = document.getElementById('customer-email').value;
    const address = document.getElementById('customer-address').value;
    
    if (!name) {
        alert('{{ __("Customer name is required") }}');
        return;
    }
    
    // In a real app, you would save to database
    console.log('Saving customer:', { name, phone, email, address });
    
    // Add to customer select
    const select = document.getElementById('customer_id');
    const option = document.createElement('option');
    option.value = Date.now();
    option.textContent = name;
    option.selected = true;
    select.appendChild(option);
    
    // Close modal and clear form
    const modal = bootstrap.Modal.getInstance(document.getElementById('addCustomerModal'));
    modal.hide();
    document.getElementById('customer-form').reset();
}

// Event listeners
document.getElementById('discount_type').addEventListener('change', function() {
    const discountField = document.getElementById('discount_value_field');
    discountField.style.display = this.value !== 'none' ? 'block' : 'none';
    calculateTotals();
});

document.getElementById('payment_status').addEventListener('change', function() {
    const paidAmountField = document.getElementById('paid_amount_field');
    paidAmountField.style.display = this.value === 'partial' ? 'block' : 'none';
});

// Load existing items on page load
document.addEventListener('DOMContentLoaded', function() {
    existingItems.forEach(item => {
        addSaleItem(item);
    });
    
    // Show discount field since it's set to fixed
    document.getElementById('discount_value_field').style.display = 'block';
});
</script>
@endpush
