@extends('tenant.layouts.app')

@section('title', __('Customer Details'))
@section('page-title', __('Customer Details'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">{{ __('app.dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('customers.index') }}">{{ __('app.customers') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Customer Details') }}</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Customer Information -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-user {{ marginEnd('2') }}"></i>
                            {{ __('Ahmed Al-Rashid') }}
                        </h5>
                        <div>
                            <span class="badge bg-success fs-6">{{ __('Active Customer') }}</span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Customer Header Info -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>{{ __('Contact Information') }}</h6>
                            <div class="border rounded p-3 bg-light">
                                <div class="row">
                                    <div class="col-12 mb-2">
                                        <strong>{{ __('Phone') }}:</strong>
                                        <span class="text-muted">+964 770 123 4567</span>
                                    </div>
                                    <div class="col-12 mb-2">
                                        <strong>{{ __('Email') }}:</strong>
                                        <span class="text-muted">ahmed.rashid@email.com</span>
                                    </div>
                                    <div class="col-12 mb-2">
                                        <strong>{{ __('Address') }}:</strong>
                                        <span class="text-muted">{{ __('Baghdad, Al-Karrada District, Street 14, Building 25') }}</span>
                                    </div>
                                    <div class="col-12">
                                        <strong>{{ __('Registration Date') }}:</strong>
                                        <span class="text-muted">{{ date('M d, Y', strtotime('-6 months')) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6>{{ __('Customer Statistics') }}</h6>
                            <div class="border rounded p-3 bg-light">
                                <div class="row text-center">
                                    <div class="col-6 mb-3">
                                        <h4 class="text-primary mb-1">15</h4>
                                        <small>{{ __('Total Orders') }}</small>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <h4 class="text-success mb-1">{{ formatCurrency(850000) }}</h4>
                                        <small>{{ __('Total Spent') }}</small>
                                    </div>
                                    <div class="col-6">
                                        <h4 class="text-info mb-1">{{ formatCurrency(56667) }}</h4>
                                        <small>{{ __('Average Order') }}</small>
                                    </div>
                                    <div class="col-6">
                                        <h4 class="text-warning mb-1">{{ date('M d') }}</h4>
                                        <small>{{ __('Last Order') }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Orders -->
                    <h6>{{ __('Recent Orders') }}</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('Order #') }}</th>
                                    <th>{{ __('Date') }}</th>
                                    <th class="text-center">{{ __('Items') }}</th>
                                    <th class="text-end">{{ __('Amount') }}</th>
                                    <th class="text-center">{{ __('Status') }}</th>
                                    <th class="text-center">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @for($i = 1; $i <= 5; $i++)
                                <tr>
                                    <td>
                                        <strong>SALE-{{ date('Ymd') }}-{{ str_pad($i, 3, '0', STR_PAD_LEFT) }}</strong>
                                    </td>
                                    <td>{{ date('M d, Y', strtotime("-{$i} days")) }}</td>
                                    <td class="text-center">{{ rand(2, 8) }}</td>
                                    <td class="text-end">{{ formatCurrency(rand(50000, 200000)) }}</td>
                                    <td class="text-center">
                                        @if($i <= 2)
                                            <span class="badge bg-success">{{ __('Completed') }}</span>
                                        @elseif($i == 3)
                                            <span class="badge bg-warning">{{ __('Processing') }}</span>
                                        @else
                                            <span class="badge bg-info">{{ __('Delivered') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-primary view-order-btn" data-order-id="{{ $i }}" title="{{ __('View Order') }}">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-info print-invoice-btn" data-order-id="{{ $i }}" title="{{ __('Print Invoice') }}">
                                                <i class="fas fa-print"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>

                    <!-- Customer Notes -->
                    <div class="mt-4">
                        <h6>{{ __('Customer Notes') }}</h6>
                        <div class="border rounded p-3 bg-light">
                            <p class="mb-2">{{ __('Regular customer with consistent orders. Prefers morning deliveries.') }}</p>
                            <p class="mb-2">{{ __('Has special discount agreement for bulk orders over 500,000 IQD.') }}</p>
                            <p class="mb-0">{{ __('Payment method: Usually cash, sometimes bank transfer.') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Activities -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-history {{ marginEnd('2') }}"></i>
                        {{ __('Customer Activities') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">{{ __('Order Completed') }}</h6>
                                <p class="timeline-text">{{ __('Order SALE-') }}{{ date('Ymd') }}-001 {{ __('completed successfully') }}</p>
                                <small class="text-muted">{{ date('M d, Y H:i') }}</small>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">{{ __('Payment Received') }}</h6>
                                <p class="timeline-text">{{ __('Payment of') }} {{ formatCurrency(125000) }} {{ __('received via cash') }}</p>
                                <small class="text-muted">{{ date('M d, Y H:i', strtotime('-2 hours')) }}</small>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">{{ __('New Order Placed') }}</h6>
                                <p class="timeline-text">{{ __('Customer placed order with 4 items') }}</p>
                                <small class="text-muted">{{ date('M d, Y H:i', strtotime('-4 hours')) }}</small>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-warning"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">{{ __('Contact Updated') }}</h6>
                                <p class="timeline-text">{{ __('Customer updated phone number') }}</p>
                                <small class="text-muted">{{ date('M d, Y H:i', strtotime('-1 week')) }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Customer Summary -->
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-chart-pie {{ marginEnd('2') }}"></i>
                        {{ __('Customer Summary') }}
                    </h6>
                </div>
                <div class="card-body text-center">
                    <img src="https://via.placeholder.com/100x100?text=AR" class="rounded-circle mb-3" alt="Customer Avatar">
                    <h5 class="mb-1">{{ __('Ahmed Al-Rashid') }}</h5>
                    <p class="text-muted mb-3">{{ __('Premium Customer') }}</p>
                    
                    <div class="row text-center mb-3">
                        <div class="col-4">
                            <h6 class="text-primary">A+</h6>
                            <small>{{ __('Credit Rating') }}</small>
                        </div>
                        <div class="col-4">
                            <h6 class="text-success">95%</h6>
                            <small>{{ __('Satisfaction') }}</small>
                        </div>
                        <div class="col-4">
                            <h6 class="text-info">6</h6>
                            <small>{{ __('Months') }}</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-tools {{ marginEnd('2') }}"></i>
                        {{ __('Quick Actions') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-primary" onclick="createOrder()">
                            <i class="fas fa-plus {{ marginEnd('2') }}"></i>{{ __('Create New Order') }}
                        </button>
                        <button class="btn btn-outline-info" onclick="sendMessage()">
                            <i class="fas fa-envelope {{ marginEnd('2') }}"></i>{{ __('Send Message') }}
                        </button>
                        <button class="btn btn-outline-success" onclick="makeCall()">
                            <i class="fas fa-phone {{ marginEnd('2') }}"></i>{{ __('Make Call') }}
                        </button>
                        <a href="{{ route('customers.edit', 1) }}" class="btn btn-outline-warning">
                            <i class="fas fa-edit {{ marginEnd('2') }}"></i>{{ __('Edit Customer') }}
                        </a>
                        <button class="btn btn-outline-secondary" onclick="viewStatement()">
                            <i class="fas fa-file-alt {{ marginEnd('2') }}"></i>{{ __('Account Statement') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Payment History -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-credit-card {{ marginEnd('2') }}"></i>
                        {{ __('Payment History') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-3">
                        <div class="col-6">
                            <div class="border-end">
                                <h5 class="text-success">{{ formatCurrency(850000) }}</h5>
                                <small>{{ __('Total Paid') }}</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h5 class="text-muted">{{ formatCurrency(0) }}</h5>
                            <small>{{ __('Outstanding') }}</small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small>{{ __('Cash Payments') }}</small>
                            <small>70%</small>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-success" style="width: 70%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small>{{ __('Bank Transfer') }}</small>
                            <small>25%</small>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-info" style="width: 25%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small>{{ __('Credit Card') }}</small>
                            <small>5%</small>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-warning" style="width: 5%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Preferred Products -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-heart {{ marginEnd('2') }}"></i>
                        {{ __('Preferred Products') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <div>
                                <h6 class="mb-1">{{ __('Paracetamol 500mg') }}</h6>
                                <small class="text-muted">{{ __('Ordered 8 times') }}</small>
                            </div>
                            <span class="badge bg-primary rounded-pill">8</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <div>
                                <h6 class="mb-1">{{ __('Digital Thermometer') }}</h6>
                                <small class="text-muted">{{ __('Ordered 3 times') }}</small>
                            </div>
                            <span class="badge bg-primary rounded-pill">3</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <div>
                                <h6 class="mb-1">{{ __('Vitamin D3 Tablets') }}</h6>
                                <small class="text-muted">{{ __('Ordered 5 times') }}</small>
                            </div>
                            <span class="badge bg-primary rounded-pill">5</span>
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
.timeline {
    position: relative;
    padding-left: 2rem;
}

.timeline-item {
    position: relative;
    margin-bottom: 1.5rem;
}

.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: -1.5rem;
    top: 1.5rem;
    width: 2px;
    height: calc(100% + 0.5rem);
    background-color: #dee2e6;
}

.timeline-marker {
    position: absolute;
    left: -1.75rem;
    top: 0.25rem;
    width: 0.75rem;
    height: 0.75rem;
    border-radius: 50%;
    border: 2px solid white;
    box-shadow: 0 0 0 2px #dee2e6;
}

.timeline-content {
    background: #f8f9fa;
    border-radius: 0.375rem;
    padding: 1rem;
    border-left: 3px solid #0d6efd;
}

.timeline-title {
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
    font-weight: 600;
}

.timeline-text {
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
    color: #6c757d;
}

.progress {
    border-radius: 10px;
}

.list-group-item {
    border: none;
    border-bottom: 1px solid #dee2e6;
}

.list-group-item:last-child {
    border-bottom: none;
}
</style>
@endpush

@push('scripts')
<script>
function createOrder() {
    // Redirect to create order with customer pre-selected
    window.location.href = '{{ route("sales.create") }}?customer_id=1';
}

function sendMessage() {
    // Show message composer modal
    showNotification('{{ __("Opening message composer...") }}', 'info');
    // In real app, open modal or redirect to messaging system
}

function makeCall() {
    // Initiate call
    if (confirm('{{ __("Initiate call to +964 770 123 4567?") }}')) {
        showNotification('{{ __("Initiating call...") }}', 'success');
        // In real app, integrate with VoIP system
    }
}

function viewStatement() {
    // Generate customer statement
    showNotification('{{ __("Generating account statement...") }}', 'info');

    // Simulate statement generation
    setTimeout(() => {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("customers.statement", 1) }}';
        form.style.display = 'none';

        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);

        document.body.appendChild(form);
        form.submit();
    }, 1000);
}

function viewOrder(orderId) {
    // Redirect to order details
    window.location.href = '{{ route("sales.index") }}/' + orderId;
}

function printInvoice(orderId) {
    // Print invoice
    if (confirm('{{ __("Print invoice for order") }} ' + orderId + '?')) {
        showNotification('{{ __("Preparing invoice for printing...") }}', 'info');

        // Simulate invoice printing
        setTimeout(() => {
            window.open('{{ route("sales.invoice", "") }}/' + orderId, '_blank');
        }, 1000);
    }
}

// Helper function for notifications
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    document.body.appendChild(notification);

    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 5000);
}

// Event listeners for order action buttons
document.addEventListener('DOMContentLoaded', function() {
    // View order buttons
    document.querySelectorAll('.view-order-btn').forEach(button => {
        button.addEventListener('click', function() {
            const orderId = this.getAttribute('data-order-id');
            viewOrder(orderId);
        });
    });

    // Print invoice buttons
    document.querySelectorAll('.print-invoice-btn').forEach(button => {
        button.addEventListener('click', function() {
            const orderId = this.getAttribute('data-order-id');
            printInvoice(orderId);
        });
    });
});
</script>
@endpush
