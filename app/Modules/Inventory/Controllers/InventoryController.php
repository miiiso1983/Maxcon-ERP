<?php

namespace App\Modules\Inventory\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Models\Product;
use App\Modules\Inventory\Models\Category;
use App\Modules\Inventory\Models\Brand;
use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Inventory\Models\Stock;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'brand', 'unit', 'stocks.warehouse']);

        // Search functionality
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

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by brand
        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        // Filter by stock status
        if ($request->filled('stock_status')) {
            switch ($request->stock_status) {
                case 'low_stock':
                    $query->whereHas('stocks', function ($q) {
                        $q->whereRaw('quantity <= reorder_level');
                    });
                    break;
                case 'out_of_stock':
                    $query->whereHas('stocks', function ($q) {
                        $q->where('quantity', '<=', 0);
                    });
                    break;
                case 'in_stock':
                    $query->whereHas('stocks', function ($q) {
                        $q->where('quantity', '>', 0);
                    });
                    break;
            }
        }

        $products = $query->active()->paginate(20);

        // Get filter options
        $categories = Category::active()->get();
        $brands = Brand::active()->get();
        $warehouses = Warehouse::active()->get();

        // Get summary statistics
        $stats = [
            'total_products' => Product::active()->count(),
            'low_stock_products' => Product::active()->whereHas('stocks', function ($q) {
                $q->whereRaw('quantity <= reorder_level');
            })->count(),
            'out_of_stock_products' => Product::active()->whereHas('stocks', function ($q) {
                $q->where('quantity', '<=', 0);
            })->count(),
            'total_stock_value' => Stock::join('products', 'stocks.product_id', '=', 'products.id')
                ->selectRaw('SUM(stocks.quantity * products.cost_price) as total_value')
                ->value('total_value') ?? 0,
        ];

        return view('tenant.inventory.index', compact(
            'products', 
            'categories', 
            'brands', 
            'warehouses', 
            'stats'
        ));
    }

    public function show(Product $product)
    {
        $product->load([
            'category', 
            'brand', 
            'unit', 
            'stocks.warehouse',
            'stockMovements' => function ($query) {
                $query->latest()->take(20);
            }
        ]);

        return view('tenant.inventory.show', compact('product'));
    }

    public function lowStock(Request $request)
    {
        $query = Product::with(['category', 'brand', 'unit', 'stocks.warehouse'])
            ->whereHas('stocks', function ($q) {
                $q->whereRaw('quantity <= reorder_level');
            });

        if ($request->filled('warehouse_id')) {
            $query->whereHas('stocks', function ($q) use ($request) {
                $q->where('warehouse_id', $request->warehouse_id);
            });
        }

        $products = $query->active()->paginate(20);
        $warehouses = Warehouse::active()->get();

        return view('tenant.inventory.low-stock', compact('products', 'warehouses'));
    }

    public function expiring(Request $request)
    {
        $days = $request->get('days', 30);
        
        $query = Stock::with(['product.category', 'product.brand', 'warehouse'])
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', now()->addDays($days))
            ->where('expiry_date', '>', now())
            ->where('quantity', '>', 0);

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        $stocks = $query->orderBy('expiry_date')->paginate(20);
        $warehouses = Warehouse::active()->get();

        return view('tenant.inventory.expiring', compact('stocks', 'warehouses', 'days'));
    }

    public function stockMovements(Request $request)
    {
        $query = \App\Modules\Inventory\Models\StockMovement::with(['product', 'warehouse', 'user'])
            ->latest();

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $movements = $query->paginate(50);
        $products = Product::active()->get();
        $warehouses = Warehouse::active()->get();

        return view('tenant.inventory.movements', compact(
            'movements', 
            'products', 
            'warehouses'
        ));
    }

    public function adjustStock(Request $request, Product $product)
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|numeric|min:0',
            'reason' => 'required|string|max:255',
        ]);

        $product->updateStock(
            $request->warehouse_id,
            $request->quantity,
            'adjustment',
            $request->reason
        );

        return redirect()->back()->with('success', 'Stock adjusted successfully.');
    }
}
