@extends('tenant.layouts.app')

@section('title', __('app.inventory'))
@section('page-title', __('app.inventory'))

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">{{ __('app.dashboard') }}</a></li>
<li class="breadcrumb-item active">{{ __('app.inventory') }}</li>
@endsection

@section('content')
<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stats-card">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col {{ marginEnd('2') }}">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            {{ __('Total Products') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ $stats['total_products'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-boxes fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stats-card-warning">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col {{ marginEnd('2') }}">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            {{ __('Low Stock') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ $stats['low_stock_products'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stats-card-danger">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col {{ marginEnd('2') }}">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            {{ __('Out of Stock') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ $stats['out_of_stock_products'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-times-circle fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stats-card-success">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col {{ marginEnd('2') }}">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            {{ __('Stock Value') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ formatCurrency($stats['total_stock_value']) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-dollar-sign fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2 mb-2">
                        <a href="{{ route('inventory.products.create') }}" class="btn btn-primary w-100">
                            <i class="fas fa-plus {{ marginEnd('2') }}"></i>{{ __('Add Product') }}
                        </a>
                    </div>
                    <div class="col-md-2 mb-2">
                        <a href="{{ route('inventory.low-stock') }}" class="btn btn-warning w-100">
                            <i class="fas fa-exclamation-triangle {{ marginEnd('2') }}"></i>{{ __('Low Stock') }}
                        </a>
                    </div>
                    <div class="col-md-2 mb-2">
                        <a href="{{ route('inventory.expiring') }}" class="btn btn-danger w-100">
                            <i class="fas fa-clock {{ marginEnd('2') }}"></i>{{ __('Expiring') }}
                        </a>
                    </div>
                    <div class="col-md-2 mb-2">
                        <a href="{{ route('inventory.movements') }}" class="btn btn-info w-100">
                            <i class="fas fa-exchange-alt {{ marginEnd('2') }}"></i>{{ __('Movements') }}
                        </a>
                    </div>
                    <div class="col-md-2 mb-2">
                        <a href="{{ route('inventory.categories.index') }}" class="btn btn-secondary w-100">
                            <i class="fas fa-tags {{ marginEnd('2') }}"></i>{{ __('Categories') }}
                        </a>
                    </div>
                    <div class="col-md-2 mb-2">
                        <a href="{{ route('inventory.warehouses.index') }}" class="btn btn-dark w-100">
                            <i class="fas fa-warehouse {{ marginEnd('2') }}"></i>{{ __('Warehouses') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters and Search -->
<div class="card mb-4">
    <div class="card-header">
        <h6 class="m-0">{{ __('app.search') }} & {{ __('app.filter') }}</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('inventory.index') }}">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label for="search" class="form-label">{{ __('app.search') }}</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="{{ request('search') }}" placeholder="{{ __('Search products...') }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="category_id" class="form-label">{{ __('app.category') }}</label>
                    <select class="form-select" id="category_id" name="category_id">
                        <option value="">{{ __('All Categories') }}</option>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="brand_id" class="form-label">{{ __('app.brand') }}</label>
                    <select class="form-select" id="brand_id" name="brand_id">
                        <option value="">{{ __('All Brands') }}</option>
                        @foreach($brands as $brand)
                        <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>
                            {{ $brand->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="stock_status" class="form-label">{{ __('Stock Status') }}</label>
                    <select class="form-select" id="stock_status" name="stock_status">
                        <option value="">{{ __('All Status') }}</option>
                        <option value="in_stock" {{ request('stock_status') == 'in_stock' ? 'selected' : '' }}>{{ __('In Stock') }}</option>
                        <option value="low_stock" {{ request('stock_status') == 'low_stock' ? 'selected' : '' }}>{{ __('Low Stock') }}</option>
                        <option value="out_of_stock" {{ request('stock_status') == 'out_of_stock' ? 'selected' : '' }}>{{ __('Out of Stock') }}</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search {{ marginEnd('2') }}"></i>{{ __('app.search') }}
                    </button>
                    <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary {{ marginStart('2') }}">
                        <i class="fas fa-times {{ marginEnd('2') }}"></i>{{ __('Clear') }}
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Products Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="m-0">{{ __('Products') }}</h6>
        <div>
            <span class="text-muted">{{ $products->total() }} {{ __('products found') }}</span>
        </div>
    </div>
    <div class="card-body p-0">
        @if($products->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('Product') }}</th>
                        <th>{{ __('app.category') }}</th>
                        <th>{{ __('app.brand') }}</th>
                        <th>{{ __('app.price') }}</th>
                        <th>{{ __('Stock') }}</th>
                        <th>{{ __('app.status') }}</th>
                        <th width="120">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm bg-light rounded {{ marginEnd('3') }}">
                                    @if($product->main_image)
                                    <img src="{{ $product->main_image }}" alt="{{ $product->name }}" class="img-fluid rounded">
                                    @else
                                    <i class="fas fa-box text-muted"></i>
                                    @endif
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $product->name }}</h6>
                                    <small class="text-muted">{{ $product->sku }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-info">{{ $product->category->name ?? 'N/A' }}</span>
                        </td>
                        <td>{{ $product->brand->name ?? 'N/A' }}</td>
                        <td>
                            <div>
                                <strong>{{ formatCurrency($product->selling_price) }}</strong>
                                <br>
                                <small class="text-muted">{{ __('Cost') }}: {{ formatCurrency($product->cost_price) }}</small>
                            </div>
                        </td>
                        <td>
                            @php
                                $totalStock = $product->total_stock;
                                $stockStatus = $product->getStockStatus();
                            @endphp
                            <div>
                                <span class="badge bg-{{ $stockStatus === 'out_of_stock' ? 'danger' : ($stockStatus === 'low_stock' ? 'warning' : 'success') }}">
                                    {{ formatNumber($totalStock) }} {{ $product->unit->short_name ?? '' }}
                                </span>
                                @if($stockStatus === 'low_stock')
                                <br><small class="text-warning">{{ __('Low Stock') }}</small>
                                @elseif($stockStatus === 'out_of_stock')
                                <br><small class="text-danger">{{ __('Out of Stock') }}</small>
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-{{ $product->is_active ? 'success' : 'secondary' }}">
                                {{ $product->is_active ? __('app.active') : __('app.inactive') }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('inventory.products.show', $product) }}" 
                                   class="btn btn-outline-info" title="{{ __('View') }}">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('inventory.products.edit', $product) }}" 
                                   class="btn btn-outline-primary" title="{{ __('app.edit') }}">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-outline-success" 
                                        onclick="adjustStock('{{ $product->id }}')" title="{{ __('Adjust Stock') }}">
                                    <i class="fas fa-plus-minus"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        @if($products->hasPages())
        <div class="card-footer">
            {{ $products->appends(request()->query())->links() }}
        </div>
        @endif
        @else
        <div class="text-center py-5">
            <i class="fas fa-boxes fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">{{ __('No products found') }}</h5>
            <p class="text-muted">{{ __('Try adjusting your search criteria or add new products.') }}</p>
            <a href="{{ route('inventory.products.create') }}" class="btn btn-primary">
                <i class="fas fa-plus {{ marginEnd('2') }}"></i>{{ __('Add Product') }}
            </a>
        </div>
        @endif
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
            <form id="stockAdjustmentForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="warehouse_id" class="form-label">{{ __('app.warehouse') }}</label>
                        <select class="form-select" id="warehouse_id" name="warehouse_id" required>
                            @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="quantity" class="form-label">{{ __('New Quantity') }}</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" step="0.01" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label for="reason" class="form-label">{{ __('Reason') }}</label>
                        <input type="text" class="form-control" id="reason" name="reason" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Adjust Stock') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function adjustStock(productId) {
    const form = document.getElementById('stockAdjustmentForm');
    form.action = `/inventory/products/${productId}/adjust-stock`;
    new bootstrap.Modal(document.getElementById('stockAdjustmentModal')).show();
}
</script>
@endpush
