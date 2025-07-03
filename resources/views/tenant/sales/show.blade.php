@extends('tenant.layouts.app')

@section('title', __('Sale Details'))
@section('page-title', __('Sale Details'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">{{ __('app.dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('sales.index') }}">{{ __('app.sales') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Sale Details') }}</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Sale Information -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-receipt {{ marginEnd('2') }}"></i>
                            {{ __('Sale') }} #SALE-{{ date('Ymd') }}-001
                        </h5>
                        <div>
                            <span class="badge bg-success fs-6">{{ __('Completed') }}</span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Sale Header Info -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>{{ __('Customer Information') }}</h6>
                            <div class="border rounded p-3 bg-light">
                                <h6 class="mb-1">{{ __('Ahmed Al-Rashid') }}</h6>
                                <p class="mb-1 text-muted">{{ __('Phone') }}: +964 770 123 4567</p>
                                <p class="mb-1 text-muted">{{ __('Email') }}: ahmed.rashid@email.com</p>
                                <p class="mb-0 text-muted">{{ __('Address') }}: {{ __('Baghdad, Al-Karrada') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6>{{ __('Sale Information') }}</h6>
                            <div class="border rounded p-3 bg-light">
                                <div class="row">
                                    <div class="col-6">
                                        <strong>{{ __('Sale Date') }}:</strong><br>
                                        <span class="text-muted">{{ date('M d, Y') }}</span>
                                    </div>
                                    <div class="col-6">
                                        <strong>{{ __('Reference') }}:</strong><br>
                                        <span class="text-muted">SALE-{{ date('Ymd') }}-001</span>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-6">
                                        <strong>{{ __('Payment Method') }}:</strong><br>
                                        <span class="text-muted">{{ __('Cash') }}</span>
                                    </div>
                                    <div class="col-6">
                                        <strong>{{ __('Payment Status') }}:</strong><br>
                                        <span class="badge bg-success">{{ __('Paid') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sale Items -->
                    <h6>{{ __('Sale Items') }}</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('Product') }}</th>
                                    <th class="text-center">{{ __('Quantity') }}</th>
                                    <th class="text-end">{{ __('Unit Price') }}</th>
                                    <th class="text-end">{{ __('Discount') }}</th>
                                    <th class="text-end">{{ __('Total') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div>
                                            <strong>{{ __('Paracetamol 500mg') }}</strong>
                                            <br>
                                            <small class="text-muted">SKU: PAR001</small>
                                        </div>
                                    </td>
                                    <td class="text-center">2</td>
                                    <td class="text-end">{{ formatCurrency(5000) }}</td>
                                    <td class="text-end">{{ formatCurrency(0) }}</td>
                                    <td class="text-end"><strong>{{ formatCurrency(10000) }}</strong></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div>
                                            <strong>{{ __('Digital Thermometer') }}</strong>
                                            <br>
                                            <small class="text-muted">SKU: THERM001</small>
                                        </div>
                                    </td>
                                    <td class="text-center">1</td>
                                    <td class="text-end">{{ formatCurrency(25000) }}</td>
                                    <td class="text-end">{{ formatCurrency(2000) }}</td>
                                    <td class="text-end"><strong>{{ formatCurrency(23000) }}</strong></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div>
                                            <strong>{{ __('Vitamin D3 Tablets') }}</strong>
                                            <br>
                                            <small class="text-muted">SKU: VIT001</small>
                                        </div>
                                    </td>
                                    <td class="text-center">3</td>
                                    <td class="text-end">{{ formatCurrency(8000) }}</td>
                                    <td class="text-end">{{ formatCurrency(0) }}</td>
                                    <td class="text-end"><strong>{{ formatCurrency(24000) }}</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Notes -->
                    <div class="mt-4">
                        <h6>{{ __('Notes') }}</h6>
                        <div class="border rounded p-3 bg-light">
                            <p class="mb-0 text-muted">{{ __('Customer requested express delivery. Items to be delivered by 3 PM today.') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sale Activities -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-history {{ marginEnd('2') }}"></i>
                        {{ __('Sale Activities') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">{{ __('Sale Completed') }}</h6>
                                <p class="timeline-text">{{ __('Payment received and sale completed successfully') }}</p>
                                <small class="text-muted">{{ date('M d, Y H:i') }}</small>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">{{ __('Payment Processed') }}</h6>
                                <p class="timeline-text">{{ __('Cash payment of') }} {{ formatCurrency(57000) }} {{ __('received') }}</p>
                                <small class="text-muted">{{ date('M d, Y H:i', strtotime('-5 minutes')) }}</small>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">{{ __('Sale Created') }}</h6>
                                <p class="timeline-text">{{ __('Sale order created with 3 items') }}</p>
                                <small class="text-muted">{{ date('M d, Y H:i', strtotime('-10 minutes')) }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Sale Summary -->
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-calculator {{ marginEnd('2') }}"></i>
                        {{ __('Sale Summary') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <tr>
                                <td>{{ __('Subtotal') }}:</td>
                                <td class="text-end">{{ formatCurrency(59000) }}</td>
                            </tr>
                            <tr>
                                <td>{{ __('Discount') }}:</td>
                                <td class="text-end">{{ formatCurrency(2000) }}</td>
                            </tr>
                            <tr>
                                <td>{{ __('Tax') }} (0%):</td>
                                <td class="text-end">{{ formatCurrency(0) }}</td>
                            </tr>
                            <tr class="table-primary">
                                <td><strong>{{ __('Total') }}:</strong></td>
                                <td class="text-end"><strong>{{ formatCurrency(57000) }}</strong></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Payment Information -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-credit-card {{ marginEnd('2') }}"></i>
                        {{ __('Payment Information') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h5 class="text-success">{{ formatCurrency(57000) }}</h5>
                                <small>{{ __('Amount Paid') }}</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h5 class="text-muted">{{ formatCurrency(0) }}</h5>
                            <small>{{ __('Balance Due') }}</small>
                        </div>
                    </div>
                    <hr>
                    <div class="text-center">
                        <span class="badge bg-success fs-6">{{ __('Fully Paid') }}</span>
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
                        <button class="btn btn-outline-primary" onclick="printInvoice()">
                            <i class="fas fa-print {{ marginEnd('2') }}"></i>{{ __('Print Invoice') }}
                        </button>
                        <button class="btn btn-outline-info" onclick="emailInvoice()">
                            <i class="fas fa-envelope {{ marginEnd('2') }}"></i>{{ __('Email Invoice') }}
                        </button>
                        <button class="btn btn-outline-success" onclick="duplicateSale()">
                            <i class="fas fa-copy {{ marginEnd('2') }}"></i>{{ __('Duplicate Sale') }}
                        </button>
                        <a href="{{ route('sales.qr-verify', $sale) }}" class="btn btn-outline-warning">
                            <i class="fas fa-qrcode {{ marginEnd('2') }}"></i>{{ __('QR Verification') }}
                        </a>
                        <a href="{{ route('sales.qr-download', $sale) }}" class="btn btn-outline-info">
                            <i class="fas fa-download {{ marginEnd('2') }}"></i>{{ __('Download QR Code') }}
                        </a>
                        <a href="{{ route('sales.edit', 1) }}" class="btn btn-outline-warning">
                            <i class="fas fa-edit {{ marginEnd('2') }}"></i>{{ __('Edit Sale') }}
                        </a>
                        <button class="btn btn-outline-danger" onclick="refundSale()">
                            <i class="fas fa-undo {{ marginEnd('2') }}"></i>{{ __('Process Refund') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Customer Information -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-user {{ marginEnd('2') }}"></i>
                        {{ __('Customer Details') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <img src="https://via.placeholder.com/80x80?text=AR" class="rounded-circle" alt="Customer">
                        <h6 class="mt-2 mb-0">{{ __('Ahmed Al-Rashid') }}</h6>
                        <small class="text-muted">{{ __('Regular Customer') }}</small>
                    </div>
                    <div class="row text-center">
                        <div class="col-4">
                            <h6 class="text-primary">15</h6>
                            <small>{{ __('Total Orders') }}</small>
                        </div>
                        <div class="col-4">
                            <h6 class="text-success">{{ formatCurrency(850000) }}</h6>
                            <small>{{ __('Total Spent') }}</small>
                        </div>
                        <div class="col-4">
                            <h6 class="text-info">{{ date('M Y') }}</h6>
                            <small>{{ __('Last Order') }}</small>
                        </div>
                    </div>
                    <hr>
                    <div class="d-grid">
                        <a href="{{ route('customers.show', 1) }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-eye {{ marginEnd('2') }}"></i>{{ __('View Customer') }}
                        </a>
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
</style>
@endpush

@push('scripts')
<script>
function printInvoice() {
    // Open the print invoice page in a new window
    window.open('{{ route("sales.print", $sale) }}', '_blank');
}

function emailInvoice() {
    // In a real application, this would send an email with the invoice
    alert('{{ __("Sending invoice via email...") }}');
}

function duplicateSale() {
    if (confirm('{{ __("Are you sure you want to duplicate this sale?") }}')) {
        // In a real application, this would create a new sale with the same items
        alert('{{ __("Sale duplicated successfully") }}');
    }
}

function refundSale() {
    if (confirm('{{ __("Are you sure you want to process a refund for this sale?") }}')) {
        // In a real application, this would open a refund form
        alert('{{ __("Refund process initiated") }}');
    }
}
</script>
@endpush
