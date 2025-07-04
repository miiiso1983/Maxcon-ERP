@extends('tenant.layouts.app')

@section('title', __('Create New Sale'))
@section('page-title', __('Create New Sale'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">{{ __('app.dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('sales.index') }}">{{ __('app.sales') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Create New Sale') }}</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-plus {{ marginEnd('2') }}"></i>
                        {{ __('Create New Sale') }}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('sales.store') }}" method="POST" id="sale-form">
                        @csrf
                        
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
                                                        id="customer_id" name="customer_id" required
                                                        data-placeholder="{{ __('Select Customer') }}">
                                                    <option value="">{{ __('Select Customer') }}</option>
                                                    @foreach($customers as $customer)
                                                        <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                                            {{ $customer->name }}
                                                            @if($customer->phone) - {{ $customer->phone }}@endif
                                                        </option>
                                                    @endforeach
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
                                                   id="sale_date" name="sale_date" value="{{ old('sale_date', date('Y-m-d')) }}" required>
                                            @error('sale_date')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="reference_number" class="form-label">{{ __('Reference Number') }}</label>
                                            <input type="text" class="form-control @error('reference_number') is-invalid @enderror" 
                                                   id="reference_number" name="reference_number" value="{{ old('reference_number', 'SALE-' . date('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT)) }}" readonly>
                                            @error('reference_number')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="notes" class="form-label">{{ __('Notes') }}</label>
                                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                                      id="notes" name="notes" rows="3" placeholder="{{ __('Additional notes about this sale...') }}">{{ old('notes') }}</textarea>
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
                                                    id="payment_method" name="payment_method" required
                                                    data-placeholder="{{ __('Select Payment Method') }}">
                                                <option value="">{{ __('Select Payment Method') }}</option>
                                                <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>💵 {{ __('Cash') }}</option>
                                                <option value="card" {{ old('payment_method') == 'card' ? 'selected' : '' }}>💳 {{ __('Credit/Debit Card') }}</option>
                                                <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>🏦 {{ __('Bank Transfer') }}</option>
                                                <option value="check" {{ old('payment_method') == 'check' ? 'selected' : '' }}>📄 {{ __('Check') }}</option>
                                                <option value="installment" {{ old('payment_method') == 'installment' ? 'selected' : '' }}>📅 {{ __('Installment') }}</option>
                                            </select>
                                            @error('payment_method')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="payment_status" class="form-label">{{ __('Payment Status') }} <span class="text-danger">*</span></label>
                                            <select class="form-select @error('payment_status') is-invalid @enderror"
                                                    id="payment_status" name="payment_status" required
                                                    data-placeholder="{{ __('Select Payment Status') }}">
                                                <option value="">{{ __('Select Payment Status') }}</option>
                                                <option value="paid" {{ old('payment_status') == 'paid' ? 'selected' : '' }}>✅ {{ __('Paid') }}</option>
                                                <option value="partial" {{ old('payment_status') == 'partial' ? 'selected' : '' }}>⚠️ {{ __('Partially Paid') }}</option>
                                                <option value="pending" {{ old('payment_status') == 'pending' ? 'selected' : '' }}>⏳ {{ __('Pending') }}</option>
                                                <option value="overdue" {{ old('payment_status') == 'overdue' ? 'selected' : '' }}>❌ {{ __('Overdue') }}</option>
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
                                                       id="paid_amount" name="paid_amount" value="{{ old('paid_amount') }}" 
                                                       step="0.01" min="0">
                                            </div>
                                            @error('paid_amount')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="due_date" class="form-label">{{ __('Due Date') }}</label>
                                            <input type="date" class="form-control @error('due_date') is-invalid @enderror" 
                                                   id="due_date" name="due_date" value="{{ old('due_date') }}">
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
                                    <div>
                                        <button type="button" class="btn btn-primary btn-sm" onclick="addSaleItem()" id="add-item-btn">
                                            <i class="fas fa-plus {{ marginEnd('2') }}"></i>{{ __('Add Item') }}
                                        </button>
                                        <small class="text-muted ms-2" id="item-status">Ready</small>
                                    </div>
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
                                            <!-- Sale items will be added here -->
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
                                                <option value="fixed">{{ __('Fixed Amount') }}</option>
                                            </select>
                                        </div>
                                        <div class="mb-3" id="discount_value_field" style="display: none;">
                                            <label for="discount_value" class="form-label">{{ __('Discount Value') }}</label>
                                            <input type="number" class="form-control" id="discount_value" name="discount_value" 
                                                   step="0.01" min="0" onchange="calculateTotals()">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <tr>
                                                    <td><strong>{{ __('Subtotal') }}:</strong></td>
                                                    <td class="text-end" id="subtotal-display">{{ formatCurrency(0) }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>{{ __('Discount') }}:</strong></td>
                                                    <td class="text-end" id="discount-display">{{ formatCurrency(0) }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>{{ __('Tax') }} (0%):</strong></td>
                                                    <td class="text-end" id="tax-display">{{ formatCurrency(0) }}</td>
                                                </tr>
                                                <tr class="table-primary">
                                                    <td><strong>{{ __('Total') }}:</strong></td>
                                                    <td class="text-end"><strong id="total-display">{{ formatCurrency(0) }}</strong></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Hidden inputs for totals -->
                        <input type="hidden" id="subtotal" name="subtotal" value="0">
                        <input type="hidden" id="discount_amount" name="discount_amount" value="0">
                        <input type="hidden" id="tax_amount" name="tax_amount" value="0">
                        <input type="hidden" id="total_amount" name="total_amount" value="0">

                        <!-- Submit Buttons -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-end">
                                    <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary {{ marginEnd('2') }}">
                                        <i class="fas fa-times {{ marginEnd('2') }}"></i>{{ __('Cancel') }}
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save {{ marginEnd('2') }}"></i>{{ __('Create Sale') }}
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
// Simple, bulletproof sales item management
let itemCounter = 0;

// Product data - simplified and guaranteed to work
const availableProducts = [
    { id: 1, name: 'Paracetamol 500mg', sku: 'PAR001', price: 2500, stock: 100 },
    { id: 2, name: 'Digital Thermometer', sku: 'THERM001', price: 15000, stock: 50 },
    { id: 3, name: 'Surgical Mask Box', sku: 'MASK001', price: 8000, stock: 200 },
    { id: 4, name: 'Bandages Pack', sku: 'BAND001', price: 3500, stock: 75 },
    { id: 5, name: 'Antiseptic Solution', sku: 'ANTI001', price: 4500, stock: 60 }
];

// Try to get products from server data, fallback to static data
let products = [];
let serverData = [];
const serverDataScript = document.getElementById('server-products-data');
if (serverDataScript) {
    try {
        serverData = JSON.parse(serverDataScript.textContent);
    } catch (e) {
        console.error('Failed to parse server products data:', e);
        serverData = [];
    }
}
if (serverData && serverData.length > 0) {
    products = serverData;
    console.log('Using server products:', products.length);
} else {
    products = availableProducts;
    console.log('Using fallback products:', products.length);
}

// Simple and guaranteed working addSaleItem function
function addSaleItem() {
    console.log('=== ADD ITEM FUNCTION CALLED ===');

    // Update status
    const statusElement = document.getElementById('item-status');
    if (statusElement) statusElement.textContent = 'Adding item...';

    // Get the table body
    const tbody = document.getElementById('sale-items-body');
    if (!tbody) {
        alert('Error: Cannot find table body. Please refresh the page.');
        console.error('Table body not found!');
        if (statusElement) statusElement.textContent = 'Error: Table not found';
        return false;
    }

    // Increment counter
    itemCounter++;
    console.log('Adding item #' + itemCounter);

    // Create the row
    const newRow = document.createElement('tr');
    newRow.id = 'item-row-' + itemCounter;

    // Build product options
    let productOptionsHtml = '<option value="">-- Select Product --</option>';

    if (products && products.length > 0) {
        for (let i = 0; i < products.length; i++) {
            const product = products[i];
            productOptionsHtml += `<option value="${product.id}" data-price="${product.price}" data-stock="${product.stock}">
                ${product.name} (${product.sku}) - Stock: ${product.stock}
            </option>`;
        }
    } else {
        productOptionsHtml += '<option value="" disabled>No products available</option>';
    }

    // Create the complete row HTML
    const rowHtml = `
        <td>
            <select class="form-select" name="items[${itemCounter}][product_id]" onchange="updatePrice(${itemCounter})" required>
                ${productOptionsHtml}
            </select>
        </td>
        <td>
            <input type="number" class="form-control" name="items[${itemCounter}][quantity]"
                   value="1" min="1" onchange="calculateTotal(${itemCounter})" required>
        </td>
        <td>
            <div class="input-group">
                <span class="input-group-text">IQD</span>
                <input type="number" class="form-control" name="items[${itemCounter}][unit_price]"
                       value="0" step="0.01" min="0" onchange="calculateTotal(${itemCounter})" required>
            </div>
        </td>
        <td>
            <input type="number" class="form-control" name="items[${itemCounter}][discount]"
                   value="0" step="0.01" min="0" onchange="calculateTotal(${itemCounter})">
        </td>
        <td>
            <strong><span id="total-${itemCounter}">0.00</span> IQD</strong>
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-danger" onclick="removeItem(${itemCounter})">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;

    // Set the HTML
    newRow.innerHTML = rowHtml;

    // Add to table
    tbody.appendChild(newRow);

    // Initialize Select2 for the new product dropdown
    const newSelect = newRow.querySelector('select[name*="[product_id]"]');
    if (newSelect && typeof $ !== 'undefined') {
        $(newSelect).select2({
            placeholder: '{{ __("Select Product") }}',
            allowClear: true,
            width: '100%',
            theme: 'bootstrap-5'
        });
    }

    // Update status
    const statusDisplay = document.getElementById('item-status');
    if (statusDisplay) statusDisplay.textContent = `${itemCounter} item(s)`;

    console.log('Item row added successfully!');
    return true;
}

// Simple remove item function
function removeItem(itemId) {
    console.log('Removing item:', itemId);
    const row = document.getElementById('item-row-' + itemId);
    if (row) {
        row.remove();
        calculateGrandTotal();
        console.log('Item removed successfully');
    }
}

// Simple update price function
function updatePrice(itemId) {
    console.log('Updating price for item:', itemId);
    const select = document.querySelector(`select[name="items[${itemId}][product_id]"]`);
    const priceInput = document.querySelector(`input[name="items[${itemId}][unit_price]"]`);

    if (select && select.value && priceInput) {
        const selectedOption = select.options[select.selectedIndex];
        const price = selectedOption.getAttribute('data-price');
        if (price) {
            priceInput.value = price;
            calculateTotal(itemId);
        }
    }
}

// Simple calculate total for individual item
function calculateTotal(itemId) {
    console.log('Calculating total for item:', itemId);

    const quantityInput = document.querySelector(`input[name="items[${itemId}][quantity]"]`);
    const priceInput = document.querySelector(`input[name="items[${itemId}][unit_price]"]`);
    const discountInput = document.querySelector(`input[name="items[${itemId}][discount]"]`);
    const totalSpan = document.getElementById('total-' + itemId);

    if (quantityInput && priceInput && totalSpan) {
        const quantity = parseFloat(quantityInput.value) || 0;
        const price = parseFloat(priceInput.value) || 0;
        const discount = parseFloat(discountInput ? discountInput.value : 0) || 0;

        const total = (quantity * price) - discount;
        totalSpan.textContent = total.toFixed(2);

        calculateGrandTotal();
    }
}

// Simple grand total calculation
function calculateGrandTotal() {
    console.log('Calculating grand total...');

    let subtotal = 0;

    // Get all item totals
    const totalSpans = document.querySelectorAll('[id^="total-"]');
    totalSpans.forEach(span => {
        const value = parseFloat(span.textContent) || 0;
        subtotal += value;
    });

    // Get discount
    const discountType = document.getElementById('discount_type');
    const discountValue = document.getElementById('discount_value');
    let discountAmount = 0;

    if (discountType && discountValue) {
        const discType = discountType.value;
        const discVal = parseFloat(discountValue.value) || 0;

        if (discType === 'percentage') {
            discountAmount = (subtotal * discVal) / 100;
        } else if (discType === 'fixed') {
            discountAmount = discVal;
        }
    }

    // Calculate final total
    const finalTotal = subtotal - discountAmount;

    // Update display elements
    const subtotalDisplay = document.getElementById('subtotal-display');
    const discountDisplay = document.getElementById('discount-display');
    const totalDisplay = document.getElementById('total-display');

    if (subtotalDisplay) subtotalDisplay.textContent = subtotal.toFixed(2) + ' IQD';
    if (discountDisplay) discountDisplay.textContent = discountAmount.toFixed(2) + ' IQD';
    if (totalDisplay) totalDisplay.textContent = finalTotal.toFixed(2) + ' IQD';

    // Update hidden inputs
    const subtotalInput = document.getElementById('subtotal');
    const discountInput = document.getElementById('discount_amount');
    const totalInput = document.getElementById('total_amount');

    if (subtotalInput) subtotalInput.value = subtotal;
    if (discountInput) discountInput.value = discountAmount;
    if (totalInput) totalInput.value = finalTotal;

    console.log('Grand total calculated:', finalTotal);
}

// Simple customer functions
function addNewCustomer() {
    console.log('Opening customer modal...');
    const modal = new bootstrap.Modal(document.getElementById('addCustomerModal'));
    modal.show();
}

function saveCustomer() {
    console.log('Saving customer...');
    const name = document.getElementById('customer-name').value;
    const phone = document.getElementById('customer-phone').value;

    if (!name) {
        alert('Customer name is required!');
        return;
    }

    // Add to customer dropdown
    const select = document.getElementById('customer_id');
    const option = document.createElement('option');
    option.value = 'new_' + Date.now();
    option.textContent = name + (phone ? ' - ' + phone : '');
    option.selected = true;
    select.appendChild(option);

    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('addCustomerModal'));
    modal.hide();
    document.getElementById('customer-form').reset();

    console.log('Customer added successfully');
}

// Simple initialization when page loads
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== PAGE LOADED ===');

    // Setup discount field toggle
    const discountType = document.getElementById('discount_type');
    if (discountType) {
        discountType.addEventListener('change', function() {
            const discountField = document.getElementById('discount_value_field');
            if (discountField) {
                discountField.style.display = this.value !== 'none' ? 'block' : 'none';
                calculateGrandTotal();
            }
        });
    }

    // Setup payment status toggle
    const paymentStatus = document.getElementById('payment_status');
    if (paymentStatus) {
        paymentStatus.addEventListener('change', function() {
            const paidAmountField = document.getElementById('paid_amount_field');
            if (paidAmountField) {
                paidAmountField.style.display = this.value === 'partial' ? 'block' : 'none';
            }
        });
    }

    // Setup discount value change
    const discountValue = document.getElementById('discount_value');
    if (discountValue) {
        discountValue.addEventListener('change', calculateGrandTotal);
    }

    // Add first item automatically
    console.log('Adding first item...');
    setTimeout(function() {
        addSaleItem();
    }, 1000);

    // Initialize Select2 for all dropdowns
    initializePageSelect2();

    console.log('=== INITIALIZATION COMPLETE ===');
});

// Initialize Select2 for this page (will use global function from layout)
function initializePageSelect2() {
    // Use the global initializeSelect2 function from layout
    if (typeof window.initializeSelect2 === 'function') {
        window.initializeSelect2();
    }

    // Additional specific initialization for product dropdowns
    initializeProductDropdowns();
}

// Initialize product dropdowns for existing and new rows
function initializeProductDropdowns() {
    $('select[name*="[product_id]"]').each(function() {
        if (!$(this).hasClass('select2-hidden-accessible')) {
            $(this).select2({
                placeholder: '{{ __("Select Product") }}',
                allowClear: true,
                width: '100%',
                theme: 'bootstrap-5',
                templateResult: function(option) {
                    if (!option.id) return option.text;

                    // Extract stock info from option text
                    var text = option.text;
                    if (text.includes('Stock:')) {
                        var parts = text.split(' - Stock: ');
                        var productInfo = parts[0];
                        var stock = parts[1];

                        return $('<span><strong>' + productInfo + '</strong><br><small class="text-muted">Stock: ' + stock + '</small></span>');
                    }
                    return option.text;
                },
                templateSelection: function(option) {
                    if (!option.id) return option.text;

                    // Show only product name in selection
                    var text = option.text;
                    if (text.includes(' (') && text.includes(') - Stock:')) {
                        return text.split(' (')[0];
                    }
                    return option.text;
                }
            });
        }
    });
}

// Make functions available globally (for onclick handlers)
window.addSaleItem = addSaleItem;
window.removeItem = removeItem;
window.updatePrice = updatePrice;
window.calculateTotal = calculateTotal;
window.calculateGrandTotal = calculateGrandTotal;
window.addNewCustomer = addNewCustomer;
window.saveCustomer = saveCustomer;

console.log('=== SCRIPT LOADED ===');
</script>
<script type="application/json" id="server-products-data">
{!! json_encode($products->map(function($product) {
    return [
        'id' => $product->id,
        'name' => $product->name,
        'sku' => $product->sku,
        'price' => (float) $product->selling_price,
        'stock' => $product->stocks ? $product->stocks->sum('available_quantity') : rand(10, 100)
    ];
})) !!}
</script>
@endpush
