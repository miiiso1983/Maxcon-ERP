@extends('tenant.layouts.app')

@section('title', __('Edit Product'))
@section('page-title', __('Edit Product'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">{{ __('app.dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('inventory.index') }}">{{ __('app.inventory') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('inventory.products.index') }}">{{ __('Products') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Edit Product') }}</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-edit {{ marginEnd('2') }}"></i>
                        {{ __('Edit Product') }}: {{ __('Medical Product Sample') }}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('inventory.products.update', 1) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <!-- Basic Information -->
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">{{ __('Basic Information') }}</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="name" class="form-label">{{ __('Product Name') }} <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                                           id="name" name="name" value="{{ old('name', 'Medical Product Sample') }}" required>
                                                    @error('name')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="sku" class="form-label">{{ __('SKU') }} <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control @error('sku') is-invalid @enderror" 
                                                           id="sku" name="sku" value="{{ old('sku', 'MED001') }}" required>
                                                    @error('sku')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="barcode" class="form-label">{{ __('Barcode') }}</label>
                                                    <input type="text" class="form-control @error('barcode') is-invalid @enderror" 
                                                           id="barcode" name="barcode" value="{{ old('barcode', '1234567890123') }}">
                                                    @error('barcode')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="category_id" class="form-label">{{ __('Category') }}</label>
                                                    <select class="form-select @error('category_id') is-invalid @enderror" 
                                                            id="category_id" name="category_id">
                                                        <option value="">{{ __('Select Category') }}</option>
                                                        <option value="1" selected>{{ __('Medicines') }}</option>
                                                        <option value="2">{{ __('Medical Devices') }}</option>
                                                        <option value="3">{{ __('Supplements') }}</option>
                                                    </select>
                                                    @error('category_id')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="description" class="form-label">{{ __('Description') }}</label>
                                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                                      id="description" name="description" rows="3">{{ old('description', 'This is a sample medical product description. It contains detailed information about the product, its uses, and specifications.') }}</textarea>
                                            @error('description')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Pricing -->
                                <div class="card mt-3">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">{{ __('Pricing') }}</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label for="cost_price" class="form-label">{{ __('Cost Price') }}</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">د.ع</span>
                                                        <input type="number" class="form-control @error('cost_price') is-invalid @enderror" 
                                                               id="cost_price" name="cost_price" value="{{ old('cost_price', '15000') }}" 
                                                               step="0.01" min="0">
                                                    </div>
                                                    @error('cost_price')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label for="selling_price" class="form-label">{{ __('Selling Price') }} <span class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">د.ع</span>
                                                        <input type="number" class="form-control @error('selling_price') is-invalid @enderror" 
                                                               id="selling_price" name="selling_price" value="{{ old('selling_price', '25000') }}" 
                                                               step="0.01" min="0" required>
                                                    </div>
                                                    @error('selling_price')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label for="tax_rate" class="form-label">{{ __('Tax Rate') }} (%)</label>
                                                    <input type="number" class="form-control @error('tax_rate') is-invalid @enderror" 
                                                           id="tax_rate" name="tax_rate" value="{{ old('tax_rate', '0') }}" 
                                                           step="0.01" min="0" max="100">
                                                    @error('tax_rate')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Inventory -->
                                <div class="card mt-3">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">{{ __('Inventory') }}</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label for="quantity" class="form-label">{{ __('Current Quantity') }}</label>
                                                    <input type="number" class="form-control" 
                                                           id="current_quantity" value="45" readonly>
                                                    <small class="text-muted">{{ __('Use stock adjustment to change quantity') }}</small>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label for="min_quantity" class="form-label">{{ __('Minimum Quantity') }}</label>
                                                    <input type="number" class="form-control @error('min_quantity') is-invalid @enderror" 
                                                           id="min_quantity" name="min_quantity" value="{{ old('min_quantity', '10') }}" 
                                                           min="0">
                                                    @error('min_quantity')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label for="unit" class="form-label">{{ __('Unit') }}</label>
                                                    <select class="form-select @error('unit') is-invalid @enderror" 
                                                            id="unit" name="unit">
                                                        <option value="piece" {{ old('unit', 'box') == 'piece' ? 'selected' : '' }}>{{ __('Piece') }}</option>
                                                        <option value="box" {{ old('unit', 'box') == 'box' ? 'selected' : '' }}>{{ __('Box') }}</option>
                                                        <option value="bottle" {{ old('unit', 'box') == 'bottle' ? 'selected' : '' }}>{{ __('Bottle') }}</option>
                                                        <option value="vial" {{ old('unit', 'box') == 'vial' ? 'selected' : '' }}>{{ __('Vial') }}</option>
                                                        <option value="pack" {{ old('unit', 'box') == 'pack' ? 'selected' : '' }}>{{ __('Pack') }}</option>
                                                    </select>
                                                    @error('unit')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Product Image & Status -->
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">{{ __('Product Image') }}</h6>
                                    </div>
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <img id="image-preview" src="https://via.placeholder.com/200x200?text=Current+Image" 
                                                 class="img-fluid rounded" style="max-height: 200px;">
                                        </div>
                                        <div class="mb-3">
                                            <input type="file" class="form-control @error('image') is-invalid @enderror" 
                                                   id="image" name="image" accept="image/*" onchange="previewImage(this)">
                                            <small class="text-muted">{{ __('Leave empty to keep current image') }}</small>
                                            @error('image')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="card mt-3">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">{{ __('Status') }}</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="is_active" 
                                                       name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_active">
                                                    {{ __('Active') }}
                                                </label>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="track_quantity" 
                                                       name="track_quantity" value="1" {{ old('track_quantity', true) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="track_quantity">
                                                    {{ __('Track Quantity') }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Quick Stock Adjustment -->
                                <div class="card mt-3">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">{{ __('Quick Stock Adjustment') }}</h6>
                                    </div>
                                    <div class="card-body">
                                        <button type="button" class="btn btn-outline-primary btn-sm w-100 mb-2" 
                                                onclick="adjustStock()">
                                            <i class="fas fa-plus-minus {{ marginEnd('2') }}"></i>{{ __('Adjust Stock') }}
                                        </button>
                                        <small class="text-muted">{{ __('Current stock: 45 units') }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-end">
                                    <a href="{{ route('inventory.products.show', 1) }}" class="btn btn-outline-info {{ marginEnd('2') }}">
                                        <i class="fas fa-eye {{ marginEnd('2') }}"></i>{{ __('View Product') }}
                                    </a>
                                    <a href="{{ route('inventory.products.index') }}" class="btn btn-outline-secondary {{ marginEnd('2') }}">
                                        <i class="fas fa-times {{ marginEnd('2') }}"></i>{{ __('Cancel') }}
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save {{ marginEnd('2') }}"></i>{{ __('Update Product') }}
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
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('image-preview').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

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
</script>
@endpush
