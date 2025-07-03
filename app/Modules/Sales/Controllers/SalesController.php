<?php

namespace App\Modules\Sales\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Sales\Models\Sale;
use App\Modules\Customer\Models\Customer;
use App\Modules\Inventory\Models\Product;
use App\Modules\Inventory\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesController extends Controller
{
    public function index(Request $request)
    {
        $query = Sale::with(['customer', 'user', 'warehouse']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($customerQuery) use ($search) {
                      $customerQuery->where('name->en', 'like', "%{$search}%")
                                   ->orWhere('name->ar', 'like', "%{$search}%")
                                   ->orWhere('name->ku', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by payment status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('sale_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('sale_date', '<=', $request->date_to);
        }

        $sales = $query->latest('sale_date')->paginate(20);

        // Get summary statistics
        $stats = [
            'total_sales' => Sale::sum('total_amount'),
            'today_sales' => Sale::whereDate('sale_date', today())->sum('total_amount'),
            'pending_payments' => Sale::where('payment_status', '!=', Sale::PAYMENT_STATUS_PAID)->sum(\DB::raw('total_amount - paid_amount')),
            'total_transactions' => Sale::count(),
        ];

        return view('tenant.sales.index', compact('sales', 'stats'));
    }

    public function create()
    {
        $customers = Customer::active()->get();
        $warehouses = Warehouse::active()->get();
        $products = Product::with(['category', 'brand', 'unit', 'stocks'])
            ->active()
            ->get();

        return view('tenant.sales.create', compact('customers', 'warehouses', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'sale_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:sale_date',
            'payment_method' => 'required|in:cash,card,transfer,credit,mixed',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Create sale
            $sale = Sale::create([
                'customer_id' => $validated['customer_id'],
                'user_id' => auth()->id(),
                'warehouse_id' => $validated['warehouse_id'],
                'sale_date' => $validated['sale_date'],
                'due_date' => $validated['due_date'],
                'payment_method' => $validated['payment_method'],
                'discount_amount' => $validated['discount_amount'] ?? 0,
                'notes' => $validated['notes'],
                'status' => Sale::STATUS_CONFIRMED,
            ]);

            // Generate invoice number
            $sale->invoice_number = $sale->generateInvoiceNumber();
            $sale->save();

            // Add sale items
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
                    'discount_amount' => $itemData['discount_amount'] ?? 0,
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

            // Add payment if provided
            if (!empty($validated['paid_amount']) && $validated['paid_amount'] > 0) {
                $sale->addPayment(
                    $validated['paid_amount'],
                    $validated['payment_method'],
                    "Initial payment for invoice {$sale->invoice_number}"
                );
            }

            // Add loyalty points if customer exists
            if ($sale->customer) {
                $sale->customer->addLoyaltyPoints($sale->total_amount);
            }

            DB::commit();

            return redirect()->route('sales.show', $sale)
                ->with('success', 'Sale created successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating sale: ' . $e->getMessage());
        }
    }

    public function show(Sale $sale)
    {
        $sale->load([
            'customer',
            'user',
            'warehouse',
            'items.product',
            'payments.user'
        ]);

        return view('tenant.sales.show', compact('sale'));
    }

    public function edit(Sale $sale)
    {
        if (!$sale->canBeEdited()) {
            return redirect()->back()
                ->with('error', 'This sale cannot be edited.');
        }

        $customers = Customer::active()->get();
        $warehouses = Warehouse::active()->get();
        $products = Product::with(['category', 'brand', 'unit', 'stocks'])
            ->active()
            ->get();

        $sale->load(['items.product']);

        return view('tenant.sales.edit', compact('sale', 'customers', 'warehouses', 'products'));
    }

    public function destroy(Sale $sale)
    {
        if (!$sale->canBeCancelled()) {
            return redirect()->back()
                ->with('error', 'This sale cannot be cancelled.');
        }

        DB::beginTransaction();
        try {
            // Restore stock for all items
            foreach ($sale->items as $item) {
                $item->restoreStock();
            }

            // Update sale status
            $sale->status = Sale::STATUS_CANCELLED;
            $sale->save();

            DB::commit();

            return redirect()->route('sales.index')
                ->with('success', 'Sale cancelled successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Error cancelling sale: ' . $e->getMessage());
        }
    }

    public function pos()
    {
        $customers = Customer::active()->get();
        $warehouses = Warehouse::active()->get();
        $products = Product::with(['category', 'brand', 'unit', 'stocks'])
            ->active()
            ->whereHas('stocks', function ($query) {
                $query->where('available_quantity', '>', 0);
            })
            ->get();

        return view('tenant.sales.pos', compact('customers', 'warehouses', 'products'));
    }

    public function addPayment(Request $request, Sale $sale)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $sale->balance_amount,
            'payment_method' => 'required|in:cash,card,transfer,credit,cheque,digital_wallet',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $payment = $sale->addPayment(
            $validated['amount'],
            $validated['payment_method'],
            $validated['reference']
        );

        if (!empty($validated['notes'])) {
            $payment->notes = $validated['notes'];
            $payment->save();
        }

        return redirect()->back()
            ->with('success', 'Payment added successfully.');
    }

    public function printInvoice(Sale $sale)
    {
        $sale->load([
            'customer',
            'user',
            'warehouse',
            'items.product'
        ]);

        // Generate QR code data
        $qrData = $this->generateQRCodeData($sale);
        $compactQrData = $this->generateCompactQRData($sale);

        return view('tenant.sales.invoice', compact('sale', 'qrData', 'compactQrData'));
    }

    /**
     * Display QR code verification data
     */
    public function qrVerification(Sale $sale)
    {
        $sale->load([
            'customer',
            'user',
            'warehouse',
            'items.product'
        ]);

        $qrData = $this->generateQRCodeData($sale);

        // Check if request wants JSON (for API access)
        if (request()->wantsJson() || request()->has('format') && request('format') === 'json') {
            return response()->json([
                'success' => true,
                'message' => 'Invoice data retrieved successfully',
                'data' => $qrData
            ]);
        }

        // Return view for web access
        return view('tenant.sales.qr-verify', compact('sale', 'qrData'));
    }

    /**
     * Generate and download QR code image
     */
    public function downloadQRCode(Sale $sale)
    {
        $sale->load(['customer', 'items.product']);
        $compactQrData = $this->generateCompactQRData($sale);

        $qrCode = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')
            ->size(400)
            ->margin(2)
            ->errorCorrection('M')
            ->generate($compactQrData);

        $filename = "invoice-qr-{$sale->invoice_number}.png";

        return response($qrCode)
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Generate QR code data for the sale
     */
    private function generateQRCodeData(Sale $sale): array
    {
        return [
            'type' => 'MAXCON_INVOICE',
            'version' => '1.0',
            'invoice_number' => $sale->invoice_number,
            'customer' => [
                'name' => $sale->customer->name,
                'phone' => $sale->customer->phone,
                'email' => $sale->customer->email,
            ],
            'sale_date' => $sale->sale_date->format('Y-m-d'),
            'due_date' => $sale->due_date?->format('Y-m-d'),
            'payment_method' => $sale->payment_method,
            'payment_status' => $sale->payment_status,
            'currency' => $sale->currency ?? 'IQD',
            'totals' => [
                'subtotal' => (float) $sale->subtotal,
                'discount' => (float) $sale->discount_amount,
                'tax' => (float) $sale->tax_amount,
                'total' => (float) $sale->total_amount,
                'paid' => (float) $sale->paid_amount,
                'balance' => (float) ($sale->total_amount - $sale->paid_amount),
            ],
            'items' => $sale->items->map(function ($item) {
                return [
                    'name' => $item->product_name,
                    'sku' => $item->product_sku,
                    'qty' => (float) $item->quantity,
                    'price' => (float) $item->unit_price,
                    'total' => (float) $item->total_amount,
                ];
            })->toArray(),
            'verification_url' => route('sales.qr-verify', $sale),
            'invoice_url' => route('sales.show', $sale),
            'generated_at' => now()->toISOString(),
        ];
    }

    /**
     * Generate compact QR code data for better scanning
     */
    private function generateCompactQRData(Sale $sale): string
    {
        // Create a more compact format for QR code
        $compactData = [
            'inv' => $sale->invoice_number,
            'cust' => $sale->customer->name,
            'date' => $sale->sale_date->format('Y-m-d'),
            'total' => (float) $sale->total_amount,
            'curr' => $sale->currency ?? 'IQD',
            'status' => $sale->payment_status,
            'items' => $sale->items->count(),
            'verify' => route('sales.qr-verify', $sale),
        ];

        return json_encode($compactData, JSON_UNESCAPED_SLASHES);
    }

    /**
     * Decode and verify compact QR data
     */
    public function decodeQRData(Request $request)
    {
        $qrData = $request->input('qr_data');

        if (!$qrData) {
            return response()->json([
                'success' => false,
                'message' => 'No QR data provided'
            ], 400);
        }

        try {
            $decodedData = json_decode($qrData, true);

            if (!$decodedData || !isset($decodedData['inv'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid QR data format'
                ], 400);
            }

            // Find the sale by invoice number
            $sale = Sale::where('invoice_number', $decodedData['inv'])->first();

            if (!$sale) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice not found'
                ], 404);
            }

            // Return expanded data
            $sale->load(['customer', 'items.product']);
            $fullData = $this->generateQRCodeData($sale);

            return response()->json([
                'success' => true,
                'message' => 'QR data decoded successfully',
                'compact_data' => $decodedData,
                'full_data' => $fullData,
                'verification_url' => route('sales.qr-verify', $sale)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to decode QR data: ' . $e->getMessage()
            ], 400);
        }
    }
}
