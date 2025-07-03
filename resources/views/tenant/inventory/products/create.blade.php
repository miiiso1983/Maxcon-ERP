@extends('tenant.layouts.app')

@section('title', __('Add New Product'))
@section('page-title', __('Add New Product'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">{{ __('app.dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('inventory.index') }}">{{ __('app.inventory') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('inventory.products.index') }}">{{ __('Products') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Add New Product') }}</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-plus {{ marginEnd('2') }}"></i>
                        {{ __('Add New Product') }}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('inventory.products.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
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
                                                           id="name" name="name" value="{{ old('name') }}" required>
                                                    @error('name')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="sku" class="form-label">{{ __('SKU') }} <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control @error('sku') is-invalid @enderror" 
                                                           id="sku" name="sku" value="{{ old('sku') }}" required>
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
                                                           id="barcode" name="barcode" value="{{ old('barcode') }}">
                                                    @error('barcode')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="category_id" class="form-label">{{ __('Category') }}</label>
                                                    <select class="form-select @error('category_id') is-invalid @enderror"
                                                            id="category_id" name="category_id"
                                                            data-placeholder="{{ __('Select Category') }}">
                                                        <option value="">{{ __('Select Category') }}</option>
                                                        <option value="medicines">{{ __('Medicines') }}</option>
                                                        <option value="medical-devices">{{ __('Medical Devices') }}</option>
                                                        <option value="supplements">{{ __('Supplements') }}</option>
                                                        <option value="equipment">{{ __('Medical Equipment') }}</option>
                                                        <option value="consumables">{{ __('Medical Consumables') }}</option>
                                                        <option value="laboratory">{{ __('Laboratory Supplies') }}</option>
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
                                                      id="description" name="description" rows="3">{{ old('description') }}</textarea>
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
                                                               id="cost_price" name="cost_price" value="{{ old('cost_price') }}" 
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
                                                               id="selling_price" name="selling_price" value="{{ old('selling_price') }}" 
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
                                                           id="tax_rate" name="tax_rate" value="{{ old('tax_rate', 0) }}" 
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
                                                    <label for="quantity" class="form-label">{{ __('Initial Quantity') }}</label>
                                                    <input type="number" class="form-control @error('quantity') is-invalid @enderror" 
                                                           id="quantity" name="quantity" value="{{ old('quantity', 0) }}" 
                                                           min="0">
                                                    @error('quantity')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label for="min_quantity" class="form-label">{{ __('Minimum Quantity') }}</label>
                                                    <input type="number" class="form-control @error('min_quantity') is-invalid @enderror" 
                                                           id="min_quantity" name="min_quantity" value="{{ old('min_quantity', 0) }}" 
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
                                                            id="unit" name="unit"
                                                            data-placeholder="{{ __('Select Unit') }}">
                                                        <option value="piece" {{ old('unit') == 'piece' ? 'selected' : '' }}>{{ __('Piece') }}</option>
                                                        <option value="box" {{ old('unit') == 'box' ? 'selected' : '' }}>{{ __('Box') }}</option>
                                                        <option value="bottle" {{ old('unit') == 'bottle' ? 'selected' : '' }}>{{ __('Bottle') }}</option>
                                                        <option value="vial" {{ old('unit') == 'vial' ? 'selected' : '' }}>{{ __('Vial') }}</option>
                                                        <option value="pack" {{ old('unit') == 'pack' ? 'selected' : '' }}>{{ __('Pack') }}</option>
                                                        <option value="tablet" {{ old('unit') == 'tablet' ? 'selected' : '' }}>{{ __('Tablet') }}</option>
                                                        <option value="capsule" {{ old('unit') == 'capsule' ? 'selected' : '' }}>{{ __('Capsule') }}</option>
                                                        <option value="ml" {{ old('unit') == 'ml' ? 'selected' : '' }}>{{ __('Milliliter') }}</option>
                                                        <option value="gram" {{ old('unit') == 'gram' ? 'selected' : '' }}>{{ __('Gram') }}</option>
                                                        <option value="kg" {{ old('unit') == 'kg' ? 'selected' : '' }}>{{ __('Kilogram') }}</option>
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
                                            <img id="image-preview" src="https://via.placeholder.com/200x200?text=No+Image" 
                                                 class="img-fluid rounded" style="max-height: 200px;">
                                        </div>
                                        <div class="mb-3">
                                            <input type="file" class="form-control @error('image') is-invalid @enderror" 
                                                   id="image" name="image" accept="image/*" onchange="previewImage(this)">
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
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-end">
                                    <a href="{{ route('inventory.products.index') }}" class="btn btn-outline-secondary {{ marginEnd('2') }}">
                                        <i class="fas fa-times {{ marginEnd('2') }}"></i>{{ __('Cancel') }}
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save {{ marginEnd('2') }}"></i>{{ __('Save Product') }}
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

// Auto-generate SKU based on product name
document.getElementById('name').addEventListener('input', function() {
    const name = this.value;
    const sku = name.toUpperCase().replace(/[^A-Z0-9]/g, '').substring(0, 10);
    document.getElementById('sku').value = sku;
});
</script>
@endpush
