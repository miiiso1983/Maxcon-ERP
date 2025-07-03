@extends('tenant.layouts.app')

@section('title', __('Create Purchase Order'))
@section('page-title', __('Create New Purchase Order'))

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">{{ __('app.dashboard') }}</a></li>
<li class="breadcrumb-item"><a href="{{ route('purchase-orders.index') }}">{{ __('Purchase Orders') }}</a></li>
<li class="breadcrumb-item active">{{ __('Create') }}</li>
@endsection

@section('content')
<div class="container-fluid">
    <form action="{{ route('purchase-orders.store') }}" method="POST" id="purchase-order-form">
        @csrf
        
        <!-- Purchase Order Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-file-alt {{ marginEnd('2') }}"></i>
                            {{ __('Purchase Order Information') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="supplier_id" class="form-label">{{ __('Supplier') }} <span class="text-danger">*</span></label>
                                    <select class="form-select @error('supplier_id') is-invalid @enderror" id="supplier_id" name="supplier_id" required>
                                        <option value="">{{ __('Select Supplier') }}</option>
                                        @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier['id'] }}" {{ old('supplier_id') == $supplier['id'] ? 'selected' : '' }}>
                                            {{ $supplier['name'] }} - {{ $supplier['contact'] }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('supplier_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="order_date" class="form-label">{{ __('Order Date') }}</label>
                                    <input type="date" class="form-control" id="order_date" name="order_date" 
                                           value="{{ old('order_date', date('Y-m-d')) }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="expected_date" class="form-label">{{ __('Expected Delivery Date') }} <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('expected_date') is-invalid @enderror" 
                                           id="expected_date" name="expected_date" value="{{ old('expected_date') }}" 
                                           min="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
                                    @error('expected_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="notes" class="form-label">{{ __('Notes') }}</label>
                                    <textarea class="form-control" id="notes" name="notes" rows="3" 
                                              placeholder="{{ __('Additional notes or special instructions...') }}">{{ old('notes') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Purchase Order Items -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-list {{ marginEnd('2') }}"></i>
                                {{ __('Order Items') }}
                            </h5>
                            <button type="button" class="btn btn-success btn-sm" onclick="addOrderItem()">
                                <i class="fas fa-plus {{ marginEnd('2') }}"></i>{{ __('Add Item') }}
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="items-table">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 35%;">{{ __('Product') }}</th>
                                        <th style="width: 15%;">{{ __('Quantity') }}</th>
                                        <th style="width: 15%;">{{ __('Unit') }}</th>
                                        <th style="width: 15%;">{{ __('Unit Price') }}</th>
                                        <th style="width: 15%;">{{ __('Total') }}</th>
                                        <th style="width: 5%;">{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="items-tbody">
                                    <!-- Items will be added here dynamically -->
                                </tbody>
                                <tfoot>
                                    <tr class="table-secondary">
                                        <td colspan="4" class="text-end"><strong>{{ __('Subtotal') }}:</strong></td>
                                        <td class="text-end"><strong id="subtotal">0.00</strong></td>
                                        <td></td>
                                    </tr>
                                    <tr class="table-secondary">
                                        <td colspan="4" class="text-end"><strong>{{ __('Tax') }} (0%):</strong></td>
                                        <td class="text-end"><strong id="tax-amount">0.00</strong></td>
                                        <td></td>
                                    </tr>
                                    <tr class="table-primary">
                                        <td colspan="4" class="text-end"><strong>{{ __('Total Amount') }}:</strong></td>
                                        <td class="text-end"><strong id="total-amount">0.00</strong></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                        <div class="alert alert-info mt-3" role="alert">
                            <i class="fas fa-info-circle {{ marginEnd('2') }}"></i>
                            {{ __('Click "Add Item" to start adding products to your purchase order. You must add at least one item.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <a href="{{ route('purchase-orders.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left {{ marginEnd('2') }}"></i>{{ __('Back to List') }}
                                </a>
                            </div>
                            <div>
                                <button type="button" class="btn btn-outline-primary {{ marginEnd('2') }}" onclick="saveDraft()">
                                    <i class="fas fa-save {{ marginEnd('2') }}"></i>{{ __('Save as Draft') }}
                                </button>
                                <button type="submit" class="btn btn-primary" id="submit-btn">
                                    <i class="fas fa-check {{ marginEnd('2') }}"></i>{{ __('Create Purchase Order') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Product Selection Modal -->
<div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productModalLabel">{{ __('Select Product') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" class="form-control" id="product-search" placeholder="{{ __('Search products...') }}">
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>{{ __('Product Name') }}</th>
                                <th>{{ __('Unit') }}</th>
                                <th class="text-end">{{ __('Price') }}</th>
                                <th class="text-center">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody id="products-list">
                            @foreach($products as $product)
                            <tr>
                                <td>{{ $product['name'] }}</td>
                                <td>{{ $product['unit'] }}</td>
                                <td class="text-end">{{ formatCurrency($product['price']) }}</td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-primary" 
                                            onclick="selectProduct({{ $product['id'] }}, '{{ $product['name'] }}', '{{ $product['unit'] }}', {{ $product['price'] }})">
                                        {{ __('Select') }}
                                    </button>
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
@endsection

@push('scripts')
<script>
let itemCounter = 0;
let currentRowIndex = -1;

// Add new order item
function addOrderItem() {
    currentRowIndex = itemCounter;
    $('#productModal').modal('show');
}

// Select product from modal
function selectProduct(productId, productName, unit, price) {
    const tbody = document.getElementById('items-tbody');
    const row = document.createElement('tr');
    row.innerHTML = `
        <td>
            <input type="hidden" name="items[${itemCounter}][product_id]" value="${productId}">
            <strong>${productName}</strong>
        </td>
        <td>
            <input type="number" class="form-control" name="items[${itemCounter}][quantity]" 
                   value="1" min="1" step="1" onchange="calculateRowTotal(${itemCounter})" required>
        </td>
        <td>${unit}</td>
        <td>
            <input type="number" class="form-control" name="items[${itemCounter}][unit_price]" 
                   value="${price}" min="0" step="0.01" onchange="calculateRowTotal(${itemCounter})" required>
        </td>
        <td class="text-end">
            <span class="row-total">${formatCurrency(price)}</span>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-danger" onclick="removeOrderItem(this)">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    
    tbody.appendChild(row);
    itemCounter++;
    
    $('#productModal').modal('hide');
    calculateTotals();
}

// Remove order item
function removeOrderItem(button) {
    button.closest('tr').remove();
    calculateTotals();
}

// Calculate row total
function calculateRowTotal(index) {
    const row = document.querySelector(`input[name="items[${index}][quantity]"]`).closest('tr');
    const quantity = parseFloat(row.querySelector(`input[name="items[${index}][quantity]"]`).value) || 0;
    const unitPrice = parseFloat(row.querySelector(`input[name="items[${index}][unit_price]"]`).value) || 0;
    const total = quantity * unitPrice;
    
    row.querySelector('.row-total').textContent = formatCurrency(total);
    calculateTotals();
}

// Calculate totals
function calculateTotals() {
    let subtotal = 0;
    document.querySelectorAll('.row-total').forEach(element => {
        const value = parseFloat(element.textContent.replace(/[^0-9.-]+/g, '')) || 0;
        subtotal += value;
    });
    
    const taxRate = 0; // 0% tax for now
    const taxAmount = subtotal * taxRate;
    const totalAmount = subtotal + taxAmount;
    
    document.getElementById('subtotal').textContent = formatCurrency(subtotal);
    document.getElementById('tax-amount').textContent = formatCurrency(taxAmount);
    document.getElementById('total-amount').textContent = formatCurrency(totalAmount);
}

// Format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(amount) + ' IQD';
}

// Save as draft
function saveDraft() {
    // Add draft status to form
    const draftInput = document.createElement('input');
    draftInput.type = 'hidden';
    draftInput.name = 'status';
    draftInput.value = 'draft';
    document.getElementById('purchase-order-form').appendChild(draftInput);
    
    document.getElementById('purchase-order-form').submit();
}

// Form validation
document.getElementById('purchase-order-form').addEventListener('submit', function(e) {
    const itemsCount = document.querySelectorAll('#items-tbody tr').length;
    if (itemsCount === 0) {
        e.preventDefault();
        alert('{{ __("Please add at least one item to the purchase order.") }}');
        return false;
    }
});

// Product search in modal
document.getElementById('product-search').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('#products-list tr');
    
    rows.forEach(row => {
        const productName = row.cells[0].textContent.toLowerCase();
        if (productName.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
</script>
@endpush
