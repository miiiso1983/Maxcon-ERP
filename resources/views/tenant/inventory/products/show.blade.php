@extends('tenant.layouts.app')

@section('title', __('Product Details'))
@section('page-title', __('Product Details'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">{{ __('app.dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('inventory.index') }}">{{ __('app.inventory') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('inventory.products.index') }}">{{ __('Products') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Product Details') }}</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Product Information -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-box {{ marginEnd('2') }}"></i>
                            {{ __('Medical Product Sample') }}
                        </h5>
                        <div>
                            <a href="{{ route('inventory.products.edit', 1) }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-edit {{ marginEnd('2') }}"></i>{{ __('Edit') }}
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <img src="https://via.placeholder.com/300x300?text=Product+Image" 
                                 class="img-fluid rounded" alt="Product Image">
                        </div>
                        <div class="col-md-8">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>{{ __('SKU') }}:</strong></td>
                                    <td><code>MED001</code></td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('Barcode') }}:</strong></td>
                                    <td><code>1234567890123</code></td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('Category') }}:</strong></td>
                                    <td><span class="badge bg-info">{{ __('Medicines') }}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('Unit') }}:</strong></td>
                                    <td>{{ __('Box') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('Status') }}:</strong></td>
                                    <td><span class="badge bg-success">{{ __('Active') }}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('Track Quantity') }}:</strong></td>
                                    <td><span class="badge bg-primary">{{ __('Yes') }}</span></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <h6>{{ __('Description') }}</h6>
                        <p class="text-muted">
                            {{ __('This is a sample medical product description. It contains detailed information about the product, its uses, and specifications.') }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Stock Movement History -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-history {{ marginEnd('2') }}"></i>
                        {{ __('Stock Movement History') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Quantity') }}</th>
                                    <th>{{ __('Reference') }}</th>
                                    <th>{{ __('Notes') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>{{ date('Y-m-d H:i') }}</td>
                                    <td><span class="badge bg-success">{{ __('Stock In') }}</span></td>
                                    <td>+50</td>
                                    <td>PO-001</td>
                                    <td>{{ __('Initial stock') }}</td>
                                </tr>
                                <tr>
                                    <td>{{ date('Y-m-d H:i', strtotime('-1 day')) }}</td>
                                    <td><span class="badge bg-danger">{{ __('Stock Out') }}</span></td>
                                    <td>-5</td>
                                    <td>SO-001</td>
                                    <td>{{ __('Sale to customer') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Pricing Information -->
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-dollar-sign {{ marginEnd('2') }}"></i>
                        {{ __('Pricing') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h4 class="text-muted">{{ formatCurrency(15000) }}</h4>
                                <small>{{ __('Cost Price') }}</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-success">{{ formatCurrency(25000) }}</h4>
                            <small>{{ __('Selling Price') }}</small>
                        </div>
                    </div>
                    <hr>
                    <div class="text-center">
                        <h5 class="text-primary">{{ formatCurrency(10000) }}</h5>
                        <small>{{ __('Profit Margin') }} (40%)</small>
                    </div>
                </div>
            </div>

            <!-- Stock Information -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-warehouse {{ marginEnd('2') }}"></i>
                        {{ __('Stock Information') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <h4 class="text-success">{{ formatNumber(45) }}</h4>
                            <small>{{ __('Current Stock') }}</small>
                        </div>
                        <div class="col-4">
                            <h4 class="text-warning">{{ formatNumber(10) }}</h4>
                            <small>{{ __('Min. Stock') }}</small>
                        </div>
                        <div class="col-4">
                            <h4 class="text-info">{{ formatNumber(100) }}</h4>
                            <small>{{ __('Max. Stock') }}</small>
                        </div>
                    </div>
                    <hr>
                    <div class="text-center">
                        <span class="badge bg-success fs-6">{{ __('In Stock') }}</span>
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
                        <button class="btn btn-outline-primary" onclick="adjustStock()">
                            <i class="fas fa-plus-minus {{ marginEnd('2') }}"></i>{{ __('Adjust Stock') }}
                        </button>
                        <button class="btn btn-outline-info" onclick="viewMovements()">
                            <i class="fas fa-history {{ marginEnd('2') }}"></i>{{ __('View Movements') }}
                        </button>
                        <button class="btn btn-outline-success" onclick="generateBarcode()">
                            <i class="fas fa-barcode {{ marginEnd('2') }}"></i>{{ __('Generate Barcode') }}
                        </button>
                        <button class="btn btn-outline-warning" onclick="duplicateProduct()">
                            <i class="fas fa-copy {{ marginEnd('2') }}"></i>{{ __('Duplicate Product') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Sales Statistics -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-chart-line {{ marginEnd('2') }}"></i>
                        {{ __('Sales Statistics') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h5 class="text-primary">{{ formatNumber(25) }}</h5>
                                <small>{{ __('This Month') }}</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h5 class="text-success">{{ formatNumber(150) }}</h5>
                            <small>{{ __('Total Sold') }}</small>
                        </div>
                    </div>
                    <hr>
                    <div class="text-center">
                        <h6 class="text-info">{{ formatCurrency(3750000) }}</h6>
                        <small>{{ __('Total Revenue') }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stock Adjustment Modal -->
<div class="modal fade" id="stockAdjustmentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Adjust Stock') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="stockAdjustmentForm">
                    <div class="mb-3">
                        <label for="adjustment_type" class="form-label">{{ __('Adjustment Type') }}</label>
                        <select class="form-select" id="adjustment_type" required>
                            <option value="add">{{ __('Add Stock') }}</option>
                            <option value="remove">{{ __('Remove Stock') }}</option>
                            <option value="set">{{ __('Set Stock') }}</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="quantity" class="form-label">{{ __('Quantity') }}</label>
                        <input type="number" class="form-control" id="quantity" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label for="reason" class="form-label">{{ __('Reason') }}</label>
                        <textarea class="form-control" id="reason" rows="3" placeholder="{{ __('Enter reason for adjustment') }}"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-primary" onclick="submitStockAdjustment()">{{ __('Adjust Stock') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function adjustStock() {
    const modal = new bootstrap.Modal(document.getElementById('stockAdjustmentModal'));
    modal.show();
}

function submitStockAdjustment() {
    const type = document.getElementById('adjustment_type').value;
    const quantity = document.getElementById('quantity').value;
    const reason = document.getElementById('reason').value;
    
    if (!quantity) {
        alert('{{ __("Please enter quantity") }}');
        return;
    }
    
    // Here you would normally send an AJAX request
    console.log('Stock adjustment:', { type, quantity, reason });
    
    // Close modal and show success message
    const modal = bootstrap.Modal.getInstance(document.getElementById('stockAdjustmentModal'));
    modal.hide();
    
    alert('{{ __("Stock adjusted successfully") }}');
    location.reload();
}

function viewMovements() {
    // Scroll to movements section
    document.querySelector('.card:nth-child(2)').scrollIntoView({ behavior: 'smooth' });
}

function generateBarcode() {
    alert('{{ __("Barcode generation feature coming soon") }}');
}

function duplicateProduct() {
    if (confirm('{{ __("Are you sure you want to duplicate this product?") }}')) {
        alert('{{ __("Product duplicated successfully") }}');
    }
}
</script>
@endpush
