<?php

namespace App\Modules\Purchase\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PurchaseOrderController extends Controller
{
    /**
     * Display a listing of purchase orders
     */
    public function index(Request $request)
    {
        // Sample purchase orders data
        $purchaseOrders = collect([
            [
                'id' => 1,
                'po_number' => 'PO-2024-001',
                'supplier_name' => 'Medical Supplies Co.',
                'supplier_id' => 1,
                'order_date' => now()->subDays(5)->format('Y-m-d'),
                'expected_date' => now()->addDays(10)->format('Y-m-d'),
                'status' => 'pending',
                'total_amount' => 1250000,
                'items_count' => 5,
                'created_by' => 'Admin User'
            ],
            [
                'id' => 2,
                'po_number' => 'PO-2024-002',
                'supplier_name' => 'Pharma Distribution Ltd.',
                'supplier_id' => 2,
                'order_date' => now()->subDays(3)->format('Y-m-d'),
                'expected_date' => now()->addDays(7)->format('Y-m-d'),
                'status' => 'approved',
                'total_amount' => 850000,
                'items_count' => 3,
                'created_by' => 'Purchase Manager'
            ],
            [
                'id' => 3,
                'po_number' => 'PO-2024-003',
                'supplier_name' => 'Equipment Solutions',
                'supplier_id' => 3,
                'order_date' => now()->subDays(1)->format('Y-m-d'),
                'expected_date' => now()->addDays(14)->format('Y-m-d'),
                'status' => 'received',
                'total_amount' => 2100000,
                'items_count' => 8,
                'created_by' => 'Admin User'
            ]
        ]);

        return view('tenant.purchase-orders.index', compact('purchaseOrders'));
    }

    /**
     * Show the form for creating a new purchase order
     */
    public function create()
    {
        // Sample suppliers data
        $suppliers = collect([
            ['id' => 1, 'name' => 'Medical Supplies Co.', 'contact' => '+964 1 234 5678'],
            ['id' => 2, 'name' => 'Pharma Distribution Ltd.', 'contact' => '+964 1 345 6789'],
            ['id' => 3, 'name' => 'Equipment Solutions', 'contact' => '+964 1 456 7890']
        ]);

        // Sample products data
        $products = collect([
            ['id' => 1, 'name' => 'Paracetamol 500mg', 'unit' => 'Box', 'price' => 25000],
            ['id' => 2, 'name' => 'Amoxicillin 250mg', 'unit' => 'Box', 'price' => 45000],
            ['id' => 3, 'name' => 'Blood Pressure Monitor', 'unit' => 'Unit', 'price' => 150000],
            ['id' => 4, 'name' => 'Surgical Gloves', 'unit' => 'Box', 'price' => 35000],
            ['id' => 5, 'name' => 'Thermometer Digital', 'unit' => 'Unit', 'price' => 25000]
        ]);

        return view('tenant.purchase-orders.create', compact('suppliers', 'products'));
    }

    /**
     * Store a newly created purchase order
     */
    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|integer',
            'expected_date' => 'required|date|after:today',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0'
        ]);

        // In a real application, save to database
        return redirect()->route('purchase-orders.index')
            ->with('success', 'Purchase order created successfully.');
    }

    /**
     * Display the specified purchase order
     */
    public function show($id)
    {
        // Sample purchase order data
        $purchaseOrder = [
            'id' => $id,
            'po_number' => 'PO-2024-' . str_pad($id, 3, '0', STR_PAD_LEFT),
            'supplier' => [
                'name' => 'Medical Supplies Co.',
                'contact' => '+964 1 234 5678',
                'email' => 'orders@medicalsupplies.com',
                'address' => 'Baghdad, Al-Karrada District'
            ],
            'order_date' => now()->subDays(5)->format('Y-m-d'),
            'expected_date' => now()->addDays(10)->format('Y-m-d'),
            'status' => 'pending',
            'created_by' => 'Admin User',
            'notes' => 'Urgent order for hospital supplies',
            'items' => [
                [
                    'product_name' => 'Paracetamol 500mg',
                    'quantity' => 50,
                    'unit' => 'Box',
                    'unit_price' => 25000,
                    'total' => 1250000
                ],
                [
                    'product_name' => 'Surgical Gloves',
                    'quantity' => 20,
                    'unit' => 'Box',
                    'unit_price' => 35000,
                    'total' => 700000
                ]
            ],
            'subtotal' => 1950000,
            'tax_amount' => 0,
            'total_amount' => 1950000
        ];

        return view('tenant.purchase-orders.show', compact('purchaseOrder'));
    }

    /**
     * Show the form for editing the specified purchase order
     */
    public function edit($id)
    {
        // Sample data for editing
        $purchaseOrder = [
            'id' => $id,
            'po_number' => 'PO-2024-' . str_pad($id, 3, '0', STR_PAD_LEFT),
            'supplier_id' => 1,
            'expected_date' => now()->addDays(10)->format('Y-m-d'),
            'status' => 'pending',
            'notes' => 'Urgent order for hospital supplies'
        ];

        $suppliers = collect([
            ['id' => 1, 'name' => 'Medical Supplies Co.'],
            ['id' => 2, 'name' => 'Pharma Distribution Ltd.'],
            ['id' => 3, 'name' => 'Equipment Solutions']
        ]);

        $products = collect([
            ['id' => 1, 'name' => 'Paracetamol 500mg', 'unit' => 'Box', 'price' => 25000],
            ['id' => 2, 'name' => 'Amoxicillin 250mg', 'unit' => 'Box', 'price' => 45000],
            ['id' => 3, 'name' => 'Blood Pressure Monitor', 'unit' => 'Unit', 'price' => 150000]
        ]);

        return view('tenant.purchase-orders.edit', compact('purchaseOrder', 'suppliers', 'products'));
    }

    /**
     * Update the specified purchase order
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'supplier_id' => 'required|integer',
            'expected_date' => 'required|date',
            'items' => 'required|array|min:1'
        ]);

        // In a real application, update in database
        return redirect()->route('purchase-orders.show', $id)
            ->with('success', 'Purchase order updated successfully.');
    }

    /**
     * Remove the specified purchase order
     */
    public function destroy($id)
    {
        // In a real application, delete from database
        return redirect()->route('purchase-orders.index')
            ->with('success', 'Purchase order deleted successfully.');
    }

    /**
     * Approve purchase order
     */
    public function approve($id)
    {
        // In a real application, update status in database
        return redirect()->route('purchase-orders.show', $id)
            ->with('success', 'Purchase order approved successfully.');
    }

    /**
     * Mark purchase order as received
     */
    public function receive($id)
    {
        // In a real application, update status and inventory
        return redirect()->route('purchase-orders.show', $id)
            ->with('success', 'Purchase order marked as received.');
    }

    /**
     * Print purchase order
     */
    public function print($id)
    {
        $purchaseOrder = [
            'id' => $id,
            'po_number' => 'PO-2024-' . str_pad($id, 3, '0', STR_PAD_LEFT),
            'supplier' => [
                'name' => 'Medical Supplies Co.',
                'contact' => '+964 1 234 5678',
                'email' => 'orders@medicalsupplies.com',
                'address' => 'Baghdad, Al-Karrada District'
            ],
            'order_date' => now()->subDays(5)->format('Y-m-d'),
            'expected_date' => now()->addDays(10)->format('Y-m-d'),
            'items' => [
                [
                    'product_name' => 'Paracetamol 500mg',
                    'quantity' => 50,
                    'unit' => 'Box',
                    'unit_price' => 25000,
                    'total' => 1250000
                ]
            ],
            'total_amount' => 1250000
        ];

        return view('tenant.purchase-orders.print', compact('purchaseOrder'));
    }
}
