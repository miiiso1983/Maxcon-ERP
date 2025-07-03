@extends('tenant.layouts.app')

@section('title', __('Products'))
@section('page-title', __('Products Management'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">{{ __('app.dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('inventory.index') }}">{{ __('app.inventory') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Products') }}</li>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ formatNumber(150) }}</h4>
                            <p class="mb-0">{{ __('Total Products') }}</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-boxes fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ formatNumber(125) }}</h4>
                            <p class="mb-0">{{ __('Active Products') }}</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ formatNumber(15) }}</h4>
                            <p class="mb-0">{{ __('Low Stock') }}</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ formatNumber(5) }}</h4>
                            <p class="mb-0">{{ __('Out of Stock') }}</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-times-circle fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Products Table -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-boxes {{ marginEnd('2') }}"></i>
                    {{ __('Products List') }}
                </h5>
                <div>
                    <div class="btn-group" role="group">
                        <a href="{{ route('inventory.products.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus {{ marginEnd('2') }}"></i>{{ __('Add Product') }}
                        </a>
                        <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown">
                            <span class="visually-hidden">{{ __('Toggle Dropdown') }}</span>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('inventory.products.import') }}">
                                <i class="fas fa-upload {{ marginEnd('2') }}"></i>{{ __('Import from Excel') }}
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('inventory.products.download-template') }}">
                                <i class="fas fa-download {{ marginEnd('2') }}"></i>{{ __('Download Template') }}
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#">
                                <i class="fas fa-file-export {{ marginEnd('2') }}"></i>{{ __('Export Products') }}
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="card-body border-bottom">
            <form method="GET" action="{{ route('inventory.products.index') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="search" class="form-label">{{ __('Search') }}</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="{{ request('search') }}" placeholder="{{ __('Product name, SKU, or barcode') }}">
                    </div>
                    <div class="col-md-2">
                        <label for="category" class="form-label">{{ __('Category') }}</label>
                        <select class="form-select" id="category" name="category"
                                data-placeholder="{{ __('All Categories') }}">
                            <option value="">{{ __('All Categories') }}</option>
                            <option value="medicines" {{ request('category') == 'medicines' ? 'selected' : '' }}>{{ __('Medicines') }}</option>
                            <option value="medical-devices" {{ request('category') == 'medical-devices' ? 'selected' : '' }}>{{ __('Medical Devices') }}</option>
                            <option value="supplements" {{ request('category') == 'supplements' ? 'selected' : '' }}>{{ __('Supplements') }}</option>
                            <option value="equipment" {{ request('category') == 'equipment' ? 'selected' : '' }}>{{ __('Medical Equipment') }}</option>
                            <option value="consumables" {{ request('category') == 'consumables' ? 'selected' : '' }}>{{ __('Medical Consumables') }}</option>
                            <option value="laboratory" {{ request('category') == 'laboratory' ? 'selected' : '' }}>{{ __('Laboratory Supplies') }}</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="status" class="form-label">{{ __('Status') }}</label>
                        <select class="form-select" id="status" name="status"
                                data-placeholder="{{ __('All Status') }}">
                            <option value="">{{ __('All Status') }}</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="stock_status" class="form-label">{{ __('Stock Status') }}</label>
                        <select class="form-select" id="stock_status" name="stock_status"
                                data-placeholder="{{ __('All Stock') }}">
                            <option value="">{{ __('All Stock') }}</option>
                            <option value="in_stock" {{ request('stock_status') == 'in_stock' ? 'selected' : '' }}>{{ __('In Stock') }}</option>
                            <option value="low_stock" {{ request('stock_status') == 'low_stock' ? 'selected' : '' }}>{{ __('Low Stock') }}</option>
                            <option value="out_of_stock" {{ request('stock_status') == 'out_of_stock' ? 'selected' : '' }}>{{ __('Out of Stock') }}</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search {{ marginEnd('2') }}"></i>{{ __('Search') }}
                            </button>
                            <a href="{{ route('inventory.products.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times {{ marginEnd('2') }}"></i>{{ __('Clear') }}
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>{{ __('Image') }}</th>
                            <th>{{ __('Product') }}</th>
                            <th>{{ __('SKU') }}</th>
                            <th>{{ __('Category') }}</th>
                            <th>{{ __('Price') }}</th>
                            <th>{{ __('Stock') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Sample Data -->
                        @for($i = 1; $i <= 10; $i++)
                        <tr>
                            <td>
                                <img src="https://via.placeholder.com/50x50?text=P{{ $i }}" 
                                     class="rounded" width="50" height="50" alt="Product {{ $i }}">
                            </td>
                            <td>
                                <div>
                                    <strong>{{ __('Medical Product') }} {{ $i }}</strong>
                                    <br>
                                    <small class="text-muted">{{ __('Sample product description') }}</small>
                                </div>
                            </td>
                            <td><code>MED{{ str_pad($i, 3, '0', STR_PAD_LEFT) }}</code></td>
                            <td>
                                <span class="badge bg-info">{{ __('Medicines') }}</span>
                            </td>
                            <td>
                                <strong>{{ formatCurrency(rand(1000, 50000)) }}</strong>
                            </td>
                            <td>
                                @php
                                    $stock = rand(0, 100);
                                    $stockClass = $stock == 0 ? 'danger' : ($stock < 10 ? 'warning' : 'success');
                                    $stockText = $stock == 0 ? __('Out of Stock') : ($stock < 10 ? __('Low Stock') : __('In Stock'));
                                @endphp
                                <div>
                                    <strong>{{ formatNumber($stock) }}</strong>
                                    <br>
                                    <span class="badge bg-{{ $stockClass }}">{{ $stockText }}</span>
                                </div>
                            </td>
                            <td>
                                @if(rand(0, 1))
                                    <span class="badge bg-success">{{ __('Active') }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ __('Inactive') }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('inventory.products.show', $i) }}" 
                                       class="btn btn-sm btn-outline-info" title="{{ __('View') }}">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('inventory.products.edit', $i) }}" 
                                       class="btn btn-sm btn-outline-primary" title="{{ __('Edit') }}">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                            onclick="deleteProduct({{ $i }})" title="{{ __('Delete') }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endfor
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    <small class="text-muted">{{ __('Showing 1 to 10 of 150 results') }}</small>
                </div>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item disabled">
                            <span class="page-link">{{ __('Previous') }}</span>
                        </li>
                        <li class="page-item active">
                            <span class="page-link">1</span>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="#">2</a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="#">3</a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="#">{{ __('Next') }}</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Confirm Delete') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>{{ __('Are you sure you want to delete this product? This action cannot be undone.') }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">{{ __('Delete') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let productToDelete = null;

function deleteProduct(id) {
    productToDelete = id;
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

document.getElementById('confirmDelete').addEventListener('click', function() {
    if (productToDelete) {
        // Here you would normally send a DELETE request
        console.log('Deleting product:', productToDelete);
        
        // For demo purposes, just close the modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('deleteModal'));
        modal.hide();
        
        // Show success message
        alert('{{ __("Product deleted successfully") }}');
        
        productToDelete = null;
    }
});
</script>
@endpush
