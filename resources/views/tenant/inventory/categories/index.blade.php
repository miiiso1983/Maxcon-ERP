@extends('tenant.layouts.app')

@section('title', __('Product Categories'))

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">{{ __('app.dashboard') }}</a></li>
<li class="breadcrumb-item"><a href="{{ route('inventory.index') }}">{{ __('app.inventory') }}</a></li>
<li class="breadcrumb-item active">{{ __('Categories') }}</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-tags text-primary {{ marginEnd('2') }}"></i>
                        {{ __('Product Categories') }}
                    </h5>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                        <i class="fas fa-plus {{ marginEnd('1') }}"></i>
                        {{ __('Add Category') }}
                    </button>
                </div>
                <div class="card-body">
                    <!-- Categories Table -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    <th>{{ __('Products Count') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Sample Categories -->
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="category-color me-2" style="width: 20px; height: 20px; background: #007bff; border-radius: 3px;"></div>
                                            <strong>Electronics</strong>
                                        </div>
                                    </td>
                                    <td>Electronic devices and accessories</td>
                                    <td><span class="badge bg-info">45</span></td>
                                    <td><span class="badge bg-success">Active</span></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-outline-primary" title="{{ __('Edit') }}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" title="{{ __('Delete') }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="category-color me-2" style="width: 20px; height: 20px; background: #28a745; border-radius: 3px;"></div>
                                            <strong>Clothing</strong>
                                        </div>
                                    </td>
                                    <td>Apparel and fashion items</td>
                                    <td><span class="badge bg-info">32</span></td>
                                    <td><span class="badge bg-success">Active</span></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-outline-primary" title="{{ __('Edit') }}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" title="{{ __('Delete') }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="category-color me-2" style="width: 20px; height: 20px; background: #ffc107; border-radius: 3px;"></div>
                                            <strong>Home & Garden</strong>
                                        </div>
                                    </td>
                                    <td>Home improvement and garden supplies</td>
                                    <td><span class="badge bg-info">28</span></td>
                                    <td><span class="badge bg-success">Active</span></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-outline-primary" title="{{ __('Edit') }}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" title="{{ __('Delete') }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Add New Category') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="categoryName" class="form-label">{{ __('Category Name') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="categoryName" required>
                    </div>
                    <div class="mb-3">
                        <label for="categoryDescription" class="form-label">{{ __('Description') }}</label>
                        <textarea class="form-control" id="categoryDescription" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="categoryColor" class="form-label">{{ __('Color') }}</label>
                        <input type="color" class="form-control form-control-color" id="categoryColor" value="#007bff">
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="categoryActive" checked>
                            <label class="form-check-label" for="categoryActive">
                                {{ __('Active') }}
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Add Category') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add category form submission
    document.querySelector('#addCategoryModal form').addEventListener('submit', function(e) {
        e.preventDefault();
        // Here you would normally submit the form via AJAX
        alert('{{ __("Category functionality will be implemented in the backend") }}');
        bootstrap.Modal.getInstance(document.getElementById('addCategoryModal')).hide();
    });
    
    // Delete confirmation
    document.querySelectorAll('.btn-outline-danger').forEach(btn => {
        btn.addEventListener('click', function() {
            if (confirm('{{ __("Are you sure you want to delete this category?") }}')) {
                // Here you would normally delete via AJAX
                alert('{{ __("Delete functionality will be implemented in the backend") }}');
            }
        });
    });
});
</script>
@endpush
