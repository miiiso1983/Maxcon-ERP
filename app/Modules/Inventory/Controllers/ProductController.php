<?php

namespace App\Modules\Inventory\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Models\Product;
use App\Modules\Inventory\Models\Category;
use App\Modules\Inventory\Models\Brand;
use App\Modules\Inventory\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'brand', 'unit']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name->en', 'like', "%{$search}%")
                  ->orWhere('name->ar', 'like', "%{$search}%")
                  ->orWhere('name->ku', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $products = $query->latest()->paginate(20);
        $categories = Category::active()->get();
        $brands = Brand::active()->get();

        return view('tenant.inventory.products.index', compact(
            'products', 
            'categories', 
            'brands'
        ));
    }

    public function create()
    {
        $categories = Category::active()->get();
        $brands = Brand::active()->get();
        $units = Unit::active()->get();

        return view('tenant.inventory.products.create', compact(
            'categories', 
            'brands', 
            'units'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name.en' => 'required|string|max:255',
            'name.ar' => 'nullable|string|max:255',
            'name.ku' => 'nullable|string|max:255',
            'description.en' => 'nullable|string',
            'description.ar' => 'nullable|string',
            'description.ku' => 'nullable|string',
            'sku' => 'required|string|unique:products,sku',
            'barcode' => 'nullable|string|unique:products,barcode',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'unit_id' => 'required|exists:units,id',
            'type' => 'required|in:simple,variable,service',
            'status' => 'required|in:active,inactive,discontinued',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'min_stock_level' => 'required|numeric|min:0',
            'max_stock_level' => 'required|numeric|min:0',
            'reorder_level' => 'required|numeric|min:0',
            'weight' => 'nullable|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'is_active' => 'boolean',
            'is_trackable' => 'boolean',
            'has_expiry' => 'boolean',
            'has_batch' => 'boolean',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle image uploads
        if ($request->hasFile('images')) {
            $images = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                $images[] = $path;
            }
            $validated['images'] = $images;
        }

        $product = Product::create($validated);

        return redirect()->route('tenant.inventory.products.show', $product)
            ->with('success', 'Product created successfully.');
    }

    public function show(Product $product)
    {
        $product->load([
            'category', 
            'brand', 
            'unit', 
            'stocks.warehouse',
            'stockMovements' => function ($query) {
                $query->latest()->take(10);
            }
        ]);

        return view('tenant.inventory.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = Category::active()->get();
        $brands = Brand::active()->get();
        $units = Unit::active()->get();

        return view('tenant.inventory.products.edit', compact(
            'product',
            'categories', 
            'brands', 
            'units'
        ));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name.en' => 'required|string|max:255',
            'name.ar' => 'nullable|string|max:255',
            'name.ku' => 'nullable|string|max:255',
            'description.en' => 'nullable|string',
            'description.ar' => 'nullable|string',
            'description.ku' => 'nullable|string',
            'sku' => ['required', 'string', Rule::unique('products')->ignore($product->id)],
            'barcode' => ['nullable', 'string', Rule::unique('products')->ignore($product->id)],
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'unit_id' => 'required|exists:units,id',
            'type' => 'required|in:simple,variable,service',
            'status' => 'required|in:active,inactive,discontinued',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'min_stock_level' => 'required|numeric|min:0',
            'max_stock_level' => 'required|numeric|min:0',
            'reorder_level' => 'required|numeric|min:0',
            'weight' => 'nullable|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'is_active' => 'boolean',
            'is_trackable' => 'boolean',
            'has_expiry' => 'boolean',
            'has_batch' => 'boolean',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle image uploads
        if ($request->hasFile('images')) {
            $images = $product->images ?? [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                $images[] = $path;
            }
            $validated['images'] = $images;
        }

        $product->update($validated);

        return redirect()->route('tenant.inventory.products.show', $product)
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        // Check if product has stock
        if ($product->stocks()->where('quantity', '>', 0)->exists()) {
            return redirect()->back()
                ->with('error', 'Cannot delete product with existing stock.');
        }

        $product->delete();

        return redirect()->route('tenant.inventory.products.index')
            ->with('success', 'Product deleted successfully.');
    }

    public function duplicate(Product $product)
    {
        $newProduct = $product->replicate();
        $newProduct->sku = $product->sku . '-COPY';
        $newProduct->barcode = null;
        $newProduct->name = [
            'en' => $product->name['en'] . ' (Copy)',
            'ar' => ($product->name['ar'] ?? '') . ' (نسخة)',
            'ku' => ($product->name['ku'] ?? '') . ' (کۆپی)',
        ];
        $newProduct->save();

        return redirect()->route('tenant.inventory.products.edit', $newProduct)
            ->with('success', 'Product duplicated successfully.');
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'products' => 'required|array',
            'products.*' => 'exists:products,id',
        ]);

        $products = Product::whereIn('id', $request->products);

        switch ($request->action) {
            case 'activate':
                $products->update(['is_active' => true]);
                $message = 'Products activated successfully.';
                break;
            case 'deactivate':
                $products->update(['is_active' => false]);
                $message = 'Products deactivated successfully.';
                break;
            case 'delete':
                // Check if any product has stock
                $hasStock = $products->whereHas('stocks', function ($q) {
                    $q->where('quantity', '>', 0);
                })->exists();

                if ($hasStock) {
                    return redirect()->back()
                        ->with('error', 'Cannot delete products with existing stock.');
                }

                $products->delete();
                $message = 'Products deleted successfully.';
                break;
        }

        return redirect()->back()->with('success', $message);
    }
}
