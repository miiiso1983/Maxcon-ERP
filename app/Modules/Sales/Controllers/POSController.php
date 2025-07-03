<?php

namespace App\Modules\Sales\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Sales\Models\Sale;
use App\Modules\Customer\Models\Customer;
use App\Modules\Inventory\Models\Product;
use App\Modules\Inventory\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class POSController extends Controller
{
    public function index()
    {
        $customers = Customer::active()->get();
        $warehouses = Warehouse::active()->get();
        $categories = \App\Modules\Inventory\Models\Category::active()->get();
        
        // Get products with stock
        $products = Product::with(['category', 'brand', 'unit', 'stocks'])
            ->active()
            ->whereHas('stocks', function ($query) {
                $query->where('available_quantity', '>', 0);
            })
            ->get();

        return view('tenant.sales.pos.index', compact('customers', 'warehouses', 'categories', 'products'));
    }

    public function searchProducts(Request $request)
    {
        $search = $request->get('search');
        $categoryId = $request->get('category_id');
        
        $query = Product::with(['category', 'brand', 'unit', 'stocks'])
            ->active()
            ->whereHas('stocks', function ($q) {
                $q->where('available_quantity', '>', 0);
            });

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name->en', 'like', "%{$search}%")
                  ->orWhere('name->ar', 'like', "%{$search}%")
                  ->orWhere('name->ku', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $products = $query->limit(20)->get();

        return response()->json([
            'products' => $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'barcode' => $product->barcode,
                    'selling_price' => $product->selling_price,
                    'cost_price' => $product->cost_price,
                    'tax_rate' => $product->tax_rate,
                    'available_stock' => $product->available_stock,
                    'unit' => $product->unit->short_name ?? '',
                    'category' => $product->category->name ?? '',
                    'image' => $product->main_image,
                ];
            })
        ]);
    }

    public function getProductByBarcode(Request $request)
    {
        $barcode = $request->get('barcode');
        
        $product = Product::with(['category', 'brand', 'unit', 'stocks'])
            ->where('barcode', $barcode)
            ->active()
            ->whereHas('stocks', function ($q) {
                $q->where('available_quantity', '>', 0);
            })
            ->first();

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        return response()->json([
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'barcode' => $product->barcode,
                'selling_price' => $product->selling_price,
                'cost_price' => $product->cost_price,
                'tax_rate' => $product->tax_rate,
                'available_stock' => $product->available_stock,
                'unit' => $product->unit->short_name ?? '',
                'category' => $product->category->name ?? '',
                'image' => $product->main_image,
            ]
        ]);
    }

    public function processSale(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'payment_method' => 'required|in:cash,card,transfer,credit,mixed',
            'discount_amount' => 'nullable|numeric|min:0',
            'paid_amount' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Create sale
            $sale = Sale::create([
                'customer_id' => $validated['customer_id'],
                'user_id' => auth()->id(),
                'warehouse_id' => $validated['warehouse_id'],
                'sale_date' => now(),
                'payment_method' => $validated['payment_method'],
                'discount_amount' => $validated['discount_amount'] ?? 0,
                'status' => Sale::STATUS_CONFIRMED,
            ]);

            // Generate invoice number
            $sale->invoice_number = $sale->generateInvoiceNumber();
            $sale->save();

            // Add sale items
            foreach ($validated['items'] as $itemData) {
                $product = Product::find($itemData['product_id']);
                
                // Check stock availability
                if ($product->available_stock < $itemData['quantity']) {
                    throw new \Exception("Insufficient stock for product: {$product->name}");
                }
                
                $saleItem = $sale->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'cost_price' => $product->cost_price,
                    'tax_rate' => $product->tax_rate,
                ]);

                // Calculate item totals
                $saleItem->calculateTotals();
                $saleItem->save();

                // Update stock
                $saleItem->updateStock();
            }

            // Calculate sale totals
            $sale->calculateTotals();
            $sale->save();

            // Add payment
            $payment = $sale->addPayment(
                $validated['paid_amount'],
                $validated['payment_method'],
                "POS payment for invoice {$sale->invoice_number}"
            );

            // Calculate change
            if ($validated['paid_amount'] > $sale->total_amount) {
                $sale->change_amount = $validated['paid_amount'] - $sale->total_amount;
                $sale->save();
            }

            // Add loyalty points if customer exists
            if ($sale->customer) {
                $sale->customer->addLoyaltyPoints($sale->total_amount);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'sale_id' => $sale->id,
                'invoice_number' => $sale->invoice_number,
                'total_amount' => $sale->total_amount,
                'paid_amount' => $validated['paid_amount'],
                'change_amount' => $sale->change_amount,
                'print_url' => route('sales.print', $sale),
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 422);
        }
    }

    public function quickCustomer(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'customer_type' => 'required|in:individual,business,hospital,clinic,pharmacy',
        ]);

        $customer = Customer::create([
            'name' => ['en' => $validated['name']],
            'phone' => $validated['phone'],
            'customer_type' => $validated['customer_type'],
            'customer_code' => 'TEMP-' . time(),
        ]);

        // Generate proper customer code
        $customer->customer_code = $customer->generateCustomerCode();
        $customer->save();

        return response()->json([
            'success' => true,
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'customer_code' => $customer->customer_code,
                'customer_type' => $customer->customer_type,
            ]
        ]);
    }

    public function holdSale(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Create draft sale
            $sale = Sale::create([
                'customer_id' => $validated['customer_id'],
                'user_id' => auth()->id(),
                'warehouse_id' => $validated['warehouse_id'],
                'sale_date' => now(),
                'status' => Sale::STATUS_DRAFT,
                'notes' => $validated['notes'],
            ]);

            // Generate invoice number
            $sale->invoice_number = 'HOLD-' . $sale->generateInvoiceNumber();
            $sale->save();

            // Add sale items (without updating stock)
            foreach ($validated['items'] as $itemData) {
                $product = Product::find($itemData['product_id']);
                
                $saleItem = $sale->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'cost_price' => $product->cost_price,
                    'tax_rate' => $product->tax_rate,
                ]);

                $saleItem->calculateTotals();
                $saleItem->save();
            }

            // Calculate sale totals
            $sale->calculateTotals();
            $sale->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'sale_id' => $sale->id,
                'message' => 'Sale held successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 422);
        }
    }

    public function getHeldSales()
    {
        $heldSales = Sale::with(['customer', 'items'])
            ->where('status', Sale::STATUS_DRAFT)
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return response()->json([
            'sales' => $heldSales->map(function ($sale) {
                return [
                    'id' => $sale->id,
                    'invoice_number' => $sale->invoice_number,
                    'customer_name' => $sale->customer->name ?? 'Walk-in Customer',
                    'total_amount' => $sale->total_amount,
                    'items_count' => $sale->items->count(),
                    'created_at' => $sale->created_at->format('Y-m-d H:i'),
                ];
            })
        ]);
    }

    public function retrieveHeldSale(Sale $sale)
    {
        if ($sale->status !== Sale::STATUS_DRAFT || $sale->user_id !== auth()->id()) {
            return response()->json(['error' => 'Sale not found or access denied'], 404);
        }

        $sale->load(['customer', 'items.product']);

        return response()->json([
            'sale' => [
                'id' => $sale->id,
                'customer_id' => $sale->customer_id,
                'warehouse_id' => $sale->warehouse_id,
                'items' => $sale->items->map(function ($item) {
                    return [
                        'product_id' => $item->product_id,
                        'product_name' => $item->product_name,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'total_amount' => $item->total_amount,
                    ];
                }),
                'total_amount' => $sale->total_amount,
            ]
        ]);
    }
}
