@extends('tenant.layouts.app')

@section('title', __('Point of Sale'))
@section('page-title', __('Point of Sale System'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Product Selection -->
        <div class="col-lg-8">
            <!-- Search and Categories -->
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input type="text" class="form-control" id="product-search" 
                                       placeholder="{{ __('Search products by name, SKU, or barcode...') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <select class="form-select" id="category-filter"
                                    data-placeholder="{{ __('All Categories') }}">
                                <option value="">{{ __('All Categories') }}</option>
                                <option value="medicines">🏥 {{ __('Medicines') }}</option>
                                <option value="medical-devices">🩺 {{ __('Medical Devices') }}</option>
                                <option value="supplements">💊 {{ __('Supplements') }}</option>
                                <option value="equipment">⚕️ {{ __('Medical Equipment') }}</option>
                                <option value="consumables">🧪 {{ __('Medical Consumables') }}</option>
                                <option value="laboratory">🔬 {{ __('Laboratory Supplies') }}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-boxes {{ marginEnd('2') }}"></i>
                        {{ __('Products') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row" id="products-grid">
                        <!-- Sample Products -->
                        @for($i = 1; $i <= 12; $i++)
                        <div class="col-xl-3 col-lg-4 col-md-6 mb-3">
                            <div class="card product-card h-100" onclick="addToCart({{ $i }})">
                                <div class="card-body text-center p-3">
                                    <img src="https://via.placeholder.com/80x80?text=P{{ $i }}" 
                                         class="img-fluid mb-2 rounded" alt="Product {{ $i }}">
                                    <h6 class="card-title mb-1">{{ __('Medical Product') }} {{ $i }}</h6>
                                    <p class="text-muted small mb-2">SKU: MED{{ str_pad($i, 3, '0', STR_PAD_LEFT) }}</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-success">{{ __('In Stock') }}</span>
                                        <strong class="text-primary">{{ formatCurrency(rand(5000, 50000)) }}</strong>
                                    </div>
                                    @php $stock = rand(5, 100); @endphp
                                    <small class="text-muted">{{ __('Stock') }}: {{ $stock }}</small>
                                </div>
                            </div>
                        </div>
                        @endfor
                    </div>
                </div>
            </div>
        </div>

        <!-- Cart and Checkout -->
        <div class="col-lg-4">
            <!-- Customer Selection -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-user {{ marginEnd('2') }}"></i>
                        {{ __('Customer') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="input-group">
                        <select class="form-select" id="customer-select"
                                data-placeholder="{{ __('Walk-in Customer') }}">
                            <option value="">{{ __('Walk-in Customer') }}</option>
                            <option value="1">👤 {{ __('Ahmed Al-Rashid') }} - +964 770 123 4567</option>
                            <option value="2">👤 {{ __('Fatima Hassan') }} - +964 771 234 5678</option>
                            <option value="3">👤 {{ __('Omar Khalil') }} - +964 772 345 6789</option>
                            <option value="4">👤 {{ __('Layla Ahmed') }} - +964 773 456 7890</option>
                            <option value="5">👤 {{ __('Hassan Ali') }} - +964 774 567 8901</option>
                            <option value="6">👤 {{ __('Noor Mohammed') }} - +964 775 678 9012</option>
                        </select>
                        <button class="btn btn-outline-primary" type="button" onclick="addNewCustomer()">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Shopping Cart -->
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-shopping-cart {{ marginEnd('2') }}"></i>
                        {{ __('Cart') }}
                    </h6>
                    <button class="btn btn-sm btn-outline-danger" onclick="clearCart()">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <div class="card-body p-0">
                    <div id="cart-items" class="list-group list-group-flush">
                        <!-- Cart items will be added here -->
                        <div class="text-center p-4 text-muted" id="empty-cart">
                            <i class="fas fa-shopping-cart fa-2x mb-2"></i>
                            <p class="mb-0">{{ __('Cart is empty') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-calculator {{ marginEnd('2') }}"></i>
                        {{ __('Order Summary') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>{{ __('Subtotal') }}:</span>
                        <span id="subtotal">{{ formatCurrency(0) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>{{ __('Tax') }} (<span id="tax-rate">0</span>%):</span>
                        <span id="tax-amount">{{ formatCurrency(0) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>{{ __('Discount') }}:</span>
                        <span id="discount-amount">{{ formatCurrency(0) }}</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between h5">
                        <strong>{{ __('Total') }}:</strong>
                        <strong id="total-amount">{{ formatCurrency(0) }}</strong>
                    </div>
                </div>
            </div>

            <!-- Payment Methods -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-credit-card {{ marginEnd('2') }}"></i>
                        {{ __('Payment Method') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-6">
                            <button class="btn btn-outline-primary w-100 payment-method active" data-method="cash">
                                <i class="fas fa-money-bill-wave d-block mb-1"></i>
                                <small>{{ __('Cash') }}</small>
                            </button>
                        </div>
                        <div class="col-6">
                            <button class="btn btn-outline-primary w-100 payment-method" data-method="card">
                                <i class="fas fa-credit-card d-block mb-1"></i>
                                <small>{{ __('Card') }}</small>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Cash Payment Details -->
                    <div id="cash-payment" class="mt-3">
                        <label for="amount-received" class="form-label">{{ __('Amount Received') }}</label>
                        <input type="number" class="form-control" id="amount-received" 
                               placeholder="0" step="0.01" onchange="calculateChange()">
                        <div class="d-flex justify-content-between mt-2">
                            <span>{{ __('Change') }}:</span>
                            <span id="change-amount">{{ formatCurrency(0) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-grid gap-2">
                <button class="btn btn-success btn-lg" onclick="processPayment()" id="checkout-btn" disabled>
                    <i class="fas fa-check {{ marginEnd('2') }}"></i>
                    {{ __('Complete Sale') }}
                </button>
                <div class="row g-2">
                    <div class="col-6">
                        <button class="btn btn-outline-secondary w-100" onclick="holdOrder()">
                            <i class="fas fa-pause {{ marginEnd('2') }}"></i>{{ __('Hold') }}
                        </button>
                    </div>
                    <div class="col-6">
                        <button class="btn btn-outline-info w-100" onclick="printReceipt()">
                            <i class="fas fa-print {{ marginEnd('2') }}"></i>{{ __('Print') }}
                        </button>
                    </div>
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
                        <label for="customer-name" class="form-label">{{ __('Name') }}</label>
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
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-primary" onclick="saveCustomer()">{{ __('Save Customer') }}</button>
            </div>
        </div>
    </div>
</div>

<!-- Receipt Modal -->
<div class="modal fade" id="receiptModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Sale Receipt') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="receipt-content">
                <!-- Receipt content will be generated here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                <button type="button" class="btn btn-primary" onclick="printReceipt()">{{ __('Print Receipt') }}</button>
                <button type="button" class="btn btn-success" onclick="newSale()">{{ __('New Sale') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.product-card {
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.product-card:hover {
    border-color: #0d6efd;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.cart-item {
    border-bottom: 1px solid #dee2e6;
    padding: 0.75rem;
}

.cart-item:last-child {
    border-bottom: none;
}

.payment-method {
    transition: all 0.3s ease;
}

.payment-method.active {
    background-color: #0d6efd;
    color: white;
    border-color: #0d6efd;
}

.quantity-controls {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.quantity-btn {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    border: 1px solid #dee2e6;
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

.quantity-btn:hover {
    background-color: #f8f9fa;
}

#receipt-content {
    font-family: 'Courier New', monospace;
    font-size: 12px;
    line-height: 1.4;
}
</style>
@endpush

@push('scripts')
<script>
let cart = [];
let currentCustomer = null;
let paymentMethod = 'cash';

// Sample products data
const products = [
    @for($i = 1; $i <= 12; $i++)
    {
        id: {{ $i }},
        name: 'Medical Product {{ $i }}',
        sku: 'MED{{ str_pad($i, 3, "0", STR_PAD_LEFT) }}',
        price: {{ rand(5000, 50000) }},
        stock: {{ rand(5, 100) }},
        category: 'medicines'
    },
    @endfor
];

function addToCart(productId) {
    const product = products.find(p => p.id === productId);
    if (!product) return;

    const existingItem = cart.find(item => item.id === productId);
    
    if (existingItem) {
        if (existingItem.quantity < product.stock) {
            existingItem.quantity++;
        } else {
            alert('{{ __("Insufficient stock") }}');
            return;
        }
    } else {
        cart.push({
            ...product,
            quantity: 1
        });
    }
    
    updateCartDisplay();
    updateOrderSummary();
}

function removeFromCart(productId) {
    cart = cart.filter(item => item.id !== productId);
    updateCartDisplay();
    updateOrderSummary();
}

function updateQuantity(productId, change) {
    const item = cart.find(item => item.id === productId);
    if (!item) return;
    
    const product = products.find(p => p.id === productId);
    const newQuantity = item.quantity + change;
    
    if (newQuantity <= 0) {
        removeFromCart(productId);
    } else if (newQuantity <= product.stock) {
        item.quantity = newQuantity;
        updateCartDisplay();
        updateOrderSummary();
    } else {
        alert('{{ __("Insufficient stock") }}');
    }
}

function updateCartDisplay() {
    const cartContainer = document.getElementById('cart-items');
    const emptyCart = document.getElementById('empty-cart');
    
    if (cart.length === 0) {
        emptyCart.style.display = 'block';
        cartContainer.innerHTML = '';
        cartContainer.appendChild(emptyCart);
        return;
    }
    
    emptyCart.style.display = 'none';
    cartContainer.innerHTML = '';
    
    cart.forEach(item => {
        const cartItem = document.createElement('div');
        cartItem.className = 'cart-item';
        cartItem.innerHTML = `
            <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                    <h6 class="mb-1">${item.name}</h6>
                    <small class="text-muted">${item.sku}</small>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <div class="quantity-controls">
                            <button class="quantity-btn" onclick="updateQuantity(${item.id}, -1)">
                                <i class="fas fa-minus"></i>
                            </button>
                            <span class="mx-2">${item.quantity}</span>
                            <button class="quantity-btn" onclick="updateQuantity(${item.id}, 1)">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        <strong>${formatCurrency(item.price * item.quantity)}</strong>
                    </div>
                </div>
                <button class="btn btn-sm btn-outline-danger ms-2" onclick="removeFromCart(${item.id})">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        cartContainer.appendChild(cartItem);
    });
}

function updateOrderSummary() {
    const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const taxRate = 0; // No tax for now
    const taxAmount = subtotal * (taxRate / 100);
    const discountAmount = 0; // No discount for now
    const total = subtotal + taxAmount - discountAmount;
    
    document.getElementById('subtotal').textContent = formatCurrency(subtotal);
    document.getElementById('tax-rate').textContent = taxRate;
    document.getElementById('tax-amount').textContent = formatCurrency(taxAmount);
    document.getElementById('discount-amount').textContent = formatCurrency(discountAmount);
    document.getElementById('total-amount').textContent = formatCurrency(total);
    
    // Enable/disable checkout button
    document.getElementById('checkout-btn').disabled = cart.length === 0;
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('ar-IQ', {
        style: 'currency',
        currency: 'IQD',
        minimumFractionDigits: 0
    }).format(amount);
}

function calculateChange() {
    const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const received = parseFloat(document.getElementById('amount-received').value) || 0;
    const change = received - total;
    
    document.getElementById('change-amount').textContent = formatCurrency(Math.max(0, change));
}

function clearCart() {
    if (cart.length === 0) return;
    
    if (confirm('{{ __("Are you sure you want to clear the cart?") }}')) {
        cart = [];
        updateCartDisplay();
        updateOrderSummary();
    }
}

function processPayment() {
    if (cart.length === 0) return;
    
    const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    
    if (paymentMethod === 'cash') {
        const received = parseFloat(document.getElementById('amount-received').value) || 0;
        if (received < total) {
            alert('{{ __("Insufficient payment amount") }}');
            return;
        }
    }
    
    // Process the sale
    const saleData = {
        customer: currentCustomer,
        items: cart,
        payment_method: paymentMethod,
        total: total,
        timestamp: new Date()
    };
    
    console.log('Processing sale:', saleData);
    
    // Show receipt
    generateReceipt(saleData);
    
    // Clear cart
    cart = [];
    updateCartDisplay();
    updateOrderSummary();
    document.getElementById('amount-received').value = '';
    calculateChange();
}

function generateReceipt(saleData) {
    const receiptContent = document.getElementById('receipt-content');
    const receiptNumber = 'R' + Date.now().toString().slice(-6);
    
    receiptContent.innerHTML = `
        <div class="text-center mb-3">
            <h4>MAXCON MEDICAL</h4>
            <p class="mb-1">{{ __('Medical Supply Store') }}</p>
            <p class="mb-1">{{ __('Baghdad, Iraq') }}</p>
            <p class="mb-0">{{ __('Phone: +964 XXX XXX XXXX') }}</p>
        </div>
        
        <hr>
        
        <div class="row">
            <div class="col-6">{{ __('Receipt #') }}: ${receiptNumber}</div>
            <div class="col-6 text-end">${new Date().toLocaleString()}</div>
        </div>
        
        <hr>
        
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>{{ __('Item') }}</th>
                    <th class="text-center">{{ __('Qty') }}</th>
                    <th class="text-end">{{ __('Price') }}</th>
                    <th class="text-end">{{ __('Total') }}</th>
                </tr>
            </thead>
            <tbody>
                ${saleData.items.map(item => `
                    <tr>
                        <td>${item.name}</td>
                        <td class="text-center">${item.quantity}</td>
                        <td class="text-end">${formatCurrency(item.price)}</td>
                        <td class="text-end">${formatCurrency(item.price * item.quantity)}</td>
                    </tr>
                `).join('')}
            </tbody>
        </table>
        
        <hr>
        
        <div class="row">
            <div class="col-6"><strong>{{ __('Total') }}:</strong></div>
            <div class="col-6 text-end"><strong>${formatCurrency(saleData.total)}</strong></div>
        </div>
        
        <div class="row">
            <div class="col-6">{{ __('Payment Method') }}:</div>
            <div class="col-6 text-end">${paymentMethod.toUpperCase()}</div>
        </div>
        
        <hr>
        
        <div class="text-center">
            <p class="mb-1">{{ __('Thank you for your business!') }}</p>
            <p class="mb-0">{{ __('Please keep this receipt for your records') }}</p>
        </div>
    `;
    
    const modal = new bootstrap.Modal(document.getElementById('receiptModal'));
    modal.show();
}

function newSale() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('receiptModal'));
    modal.hide();
    
    // Reset everything
    cart = [];
    currentCustomer = null;
    document.getElementById('customer-select').value = '';
    document.getElementById('amount-received').value = '';
    updateCartDisplay();
    updateOrderSummary();
    calculateChange();
}

function addNewCustomer() {
    const modal = new bootstrap.Modal(document.getElementById('addCustomerModal'));
    modal.show();
}

function saveCustomer() {
    const name = document.getElementById('customer-name').value;
    const phone = document.getElementById('customer-phone').value;
    const email = document.getElementById('customer-email').value;
    
    if (!name) {
        alert('{{ __("Customer name is required") }}');
        return;
    }
    
    // In a real app, you would save to database
    console.log('Saving customer:', { name, phone, email });
    
    // Add to customer select
    const select = document.getElementById('customer-select');
    const option = document.createElement('option');
    option.value = Date.now();
    option.textContent = name;
    option.selected = true;
    select.appendChild(option);
    
    currentCustomer = { name, phone, email };
    
    // Close modal and clear form
    const modal = bootstrap.Modal.getInstance(document.getElementById('addCustomerModal'));
    modal.hide();
    document.getElementById('customer-form').reset();
}

// Payment method selection
document.querySelectorAll('.payment-method').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.payment-method').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        paymentMethod = this.dataset.method;
        
        // Show/hide payment details
        document.getElementById('cash-payment').style.display = 
            paymentMethod === 'cash' ? 'block' : 'none';
    });
});

// Product search
document.getElementById('product-search').addEventListener('input', function() {
    const query = this.value.toLowerCase();
    // In a real app, you would filter products here
    console.log('Searching for:', query);
});

// Category filter
document.getElementById('category-filter').addEventListener('change', function() {
    const category = this.value;
    // In a real app, you would filter products here
    console.log('Filtering by category:', category);
});

// Customer selection
document.getElementById('customer-select').addEventListener('change', function() {
    const customerId = this.value;
    if (customerId) {
        currentCustomer = { id: customerId, name: this.options[this.selectedIndex].text };
    } else {
        currentCustomer = null;
    }
});

// Initialize
updateCartDisplay();
updateOrderSummary();
</script>
@endpush
