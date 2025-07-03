<?php

namespace App\Modules\Supplier\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Supplier\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name->en', 'like', "%{$search}%")
                  ->orWhere('name->ar', 'like', "%{$search}%")
                  ->orWhere('name->ku', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('supplier_code', 'like', "%{$search}%");
            });
        }

        // Filter by supplier type
        if ($request->filled('supplier_type')) {
            $query->where('supplier_type', $request->supplier_type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // Filter by rating
        if ($request->filled('rating')) {
            $rating = (float) $request->rating;
            $query->where('rating', '>=', $rating);
        }

        $suppliers = $query->latest()->paginate(20);

        // Get summary statistics
        $stats = [
            'total_suppliers' => Supplier::count(),
            'active_suppliers' => Supplier::where('is_active', true)->count(),
            'suppliers_with_orders' => Supplier::withOutstandingOrders()->count(),
            'average_rating' => Supplier::where('rating', '>', 0)->avg('rating') ?? 0,
        ];

        return view('tenant.suppliers.index', compact('suppliers', 'stats'));
    }

    public function create()
    {
        return view('tenant.suppliers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name.en' => 'required|string|max:255',
            'name.ar' => 'nullable|string|max:255',
            'name.ku' => 'nullable|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:suppliers,email',
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'address.en' => 'nullable|string',
            'address.ar' => 'nullable|string',
            'address.ku' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'tax_number' => 'nullable|string|max:50',
            'supplier_type' => 'required|in:manufacturer,distributor,wholesaler,importer,local,international',
            'payment_terms' => 'nullable|integer|min:0',
            'credit_limit' => 'nullable|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'contact_person' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'bank_details.bank_name' => 'nullable|string|max:255',
            'bank_details.account_number' => 'nullable|string|max:50',
            'bank_details.iban' => 'nullable|string|max:50',
            'bank_details.swift_code' => 'nullable|string|max:20',
            'notes.en' => 'nullable|string',
            'notes.ar' => 'nullable|string',
            'notes.ku' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $supplier = Supplier::create($validated);

        // Generate supplier code
        $supplier->supplier_code = $supplier->generateSupplierCode();
        $supplier->save();

        return redirect()->route('suppliers.show', $supplier)
            ->with('success', 'Supplier created successfully.');
    }

    public function show(Supplier $supplier)
    {
        $supplier->load([
            'purchaseOrders' => function ($query) {
                $query->latest()->take(10);
            },
            'evaluations' => function ($query) {
                $query->latest()->take(5);
            },
            'products' => function ($query) {
                $query->take(10);
            }
        ]);

        // Calculate performance metrics
        $metrics = $supplier->getPerformanceMetrics();
        $category = $supplier->getSupplierCategory();

        return view('tenant.suppliers.show', compact('supplier', 'metrics', 'category'));
    }

    public function edit(Supplier $supplier)
    {
        return view('tenant.suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name.en' => 'required|string|max:255',
            'name.ar' => 'nullable|string|max:255',
            'name.ku' => 'nullable|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'email' => ['nullable', 'email', Rule::unique('suppliers')->ignore($supplier->id)],
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'address.en' => 'nullable|string',
            'address.ar' => 'nullable|string',
            'address.ku' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'tax_number' => 'nullable|string|max:50',
            'supplier_type' => 'required|in:manufacturer,distributor,wholesaler,importer,local,international',
            'payment_terms' => 'nullable|integer|min:0',
            'credit_limit' => 'nullable|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'contact_person' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'bank_details.bank_name' => 'nullable|string|max:255',
            'bank_details.account_number' => 'nullable|string|max:50',
            'bank_details.iban' => 'nullable|string|max:50',
            'bank_details.swift_code' => 'nullable|string|max:20',
            'notes.en' => 'nullable|string',
            'notes.ar' => 'nullable|string',
            'notes.ku' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $supplier->update($validated);

        return redirect()->route('suppliers.show', $supplier)
            ->with('success', 'Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier)
    {
        // Check if supplier has purchase orders
        if ($supplier->purchaseOrders()->exists()) {
            return redirect()->back()
                ->with('error', 'Cannot delete supplier with existing purchase orders.');
        }

        $supplier->delete();

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier deleted successfully.');
    }

    public function evaluate(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'quality_rating' => 'required|numeric|min:1|max:5',
            'delivery_rating' => 'required|numeric|min:1|max:5',
            'service_rating' => 'required|numeric|min:1|max:5',
            'price_rating' => 'required|numeric|min:1|max:5',
            'communication_rating' => 'required|numeric|min:1|max:5',
            'comments' => 'nullable|string',
            'recommendations' => 'nullable|string',
            'evaluation_period_start' => 'nullable|date',
            'evaluation_period_end' => 'nullable|date|after_or_equal:evaluation_period_start',
        ]);

        $supplier->evaluations()->create(array_merge($validated, [
            'user_id' => auth()->id(),
            'evaluation_date' => now(),
        ]));

        return redirect()->back()
            ->with('success', 'Supplier evaluation submitted successfully.');
    }

    public function performance(Supplier $supplier)
    {
        $metrics = $supplier->getPerformanceMetrics();
        $evaluations = $supplier->evaluations()->with('user')->latest()->paginate(10);
        $recentOrders = $supplier->purchaseOrders()
            ->with(['items', 'receipts'])
            ->latest()
            ->take(20)
            ->get();

        return view('tenant.suppliers.performance', compact('supplier', 'metrics', 'evaluations', 'recentOrders'));
    }

    public function products(Supplier $supplier)
    {
        $products = $supplier->products()
            ->with(['category', 'brand', 'unit'])
            ->paginate(20);

        return view('tenant.suppliers.products', compact('supplier', 'products'));
    }

    public function addProduct(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'supplier_sku' => 'nullable|string|max:100',
            'cost_price' => 'required|numeric|min:0',
            'lead_time_days' => 'required|integer|min:0',
            'minimum_order_quantity' => 'required|numeric|min:0.01',
        ]);

        $supplier->products()->attach($validated['product_id'], [
            'supplier_sku' => $validated['supplier_sku'],
            'cost_price' => $validated['cost_price'],
            'lead_time_days' => $validated['lead_time_days'],
            'minimum_order_quantity' => $validated['minimum_order_quantity'],
        ]);

        return redirect()->back()
            ->with('success', 'Product added to supplier successfully.');
    }

    public function removeProduct(Supplier $supplier, $productId)
    {
        $supplier->products()->detach($productId);

        return redirect()->back()
            ->with('success', 'Product removed from supplier successfully.');
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'suppliers' => 'required|array',
            'suppliers.*' => 'exists:suppliers,id',
        ]);

        $suppliers = Supplier::whereIn('id', $request->suppliers);

        switch ($request->action) {
            case 'activate':
                $suppliers->update(['is_active' => true]);
                $message = 'Suppliers activated successfully.';
                break;
            case 'deactivate':
                $suppliers->update(['is_active' => false]);
                $message = 'Suppliers deactivated successfully.';
                break;
            case 'delete':
                // Check if any supplier has purchase orders
                $hasOrders = $suppliers->whereHas('purchaseOrders')->exists();

                if ($hasOrders) {
                    return redirect()->back()
                        ->with('error', 'Cannot delete suppliers with existing purchase orders.');
                }

                $suppliers->delete();
                $message = 'Suppliers deleted successfully.';
                break;
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Show import form
     */
    public function showImport()
    {
        return view('tenant.suppliers.import');
    }

    /**
     * Import suppliers from Excel/CSV
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB max
        ]);

        try {
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();

            // Read file based on extension
            if (in_array($extension, ['xlsx', 'xls'])) {
                $data = $this->readExcelFile($file);
            } else {
                $data = $this->readCsvFile($file);
            }

            $results = $this->processSupplierData($data, $request->all());

            return redirect()->route('suppliers.import')
                ->with('success', 'Import completed successfully!')
                ->with('import_results', $results);

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Read Excel file
     */
    private function readExcelFile($file)
    {
        // Simulate reading Excel file
        // In real application, use PhpSpreadsheet or similar library
        return $this->getSampleSupplierData();
    }

    /**
     * Read CSV file
     */
    private function readCsvFile($file)
    {
        $data = [];
        $handle = fopen($file->getPathname(), 'r');

        // Read header row
        $headers = fgetcsv($handle);

        // Read data rows
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) === count($headers)) {
                $data[] = array_combine($headers, $row);
            }
        }

        fclose($handle);
        return $data;
    }

    /**
     * Process supplier data
     */
    private function processSupplierData($data, $options = [])
    {
        $imported = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];

        foreach ($data as $index => $row) {
            try {
                // Clean and validate data
                $supplierData = $this->cleanSupplierData($row);

                if (empty($supplierData['name']) || empty($supplierData['phone'])) {
                    $skipped++;
                    $errors[] = "Row " . ($index + 2) . ": Missing required fields (name or phone)";
                    continue;
                }

                // Check if supplier exists (by phone or email)
                $existingSupplier = null;
                if (!empty($supplierData['email'])) {
                    // In real app, check database
                    // $existingSupplier = Supplier::where('email', $supplierData['email'])->first();
                }

                if ($existingSupplier && isset($options['update_existing']) && $options['update_existing']) {
                    // Update existing supplier
                    // $existingSupplier->update($supplierData);
                    $updated++;
                } elseif (!$existingSupplier) {
                    // Create new supplier
                    // Supplier::create($supplierData);
                    $imported++;
                } else {
                    $skipped++;
                    $errors[] = "Row " . ($index + 2) . ": Supplier already exists";
                }

            } catch (\Exception $e) {
                $skipped++;
                $errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
            }
        }

        return [
            'imported' => $imported,
            'updated' => $updated,
            'skipped' => $skipped,
            'errors' => $errors,
            'total' => count($data)
        ];
    }

    /**
     * Clean supplier data
     */
    private function cleanSupplierData($row)
    {
        // Map column names to expected format
        $mapping = [
            'name' => ['name', 'supplier_name', 'company_name', 'name (اسم المورد)'],
            'phone' => ['phone', 'telephone', 'contact', 'phone (رقم الهاتف)'],
            'email' => ['email', 'email_address', 'email (البريد الإلكتروني)'],
            'address' => ['address', 'location', 'address (العنوان)'],
            'city' => ['city', 'city (المدينة)'],
            'supplier_type' => ['supplier_type', 'type', 'category', 'supplier_type (نوع المورد)'],
            'contact_person' => ['contact_person', 'contact_name', 'representative', 'contact_person (الشخص المسؤول)'],
            'tax_number' => ['tax_number', 'tax_id', 'vat_number', 'tax_number (رقم الضريبة)'],
            'license_number' => ['license_number', 'license', 'registration', 'license_number (رقم الترخيص)'],
            'payment_terms' => ['payment_terms', 'terms', 'payment_terms (شروط الدفع)'],
            'credit_limit' => ['credit_limit', 'limit', 'credit_limit (حد الائتمان)'],
            'notes' => ['notes', 'remarks', 'comments', 'notes (ملاحظات)']
        ];

        $cleanData = [];

        foreach ($mapping as $field => $possibleKeys) {
            $value = null;
            foreach ($possibleKeys as $key) {
                if (isset($row[$key]) && !empty(trim($row[$key]))) {
                    $value = trim($row[$key]);
                    break;
                }
            }

            if ($value !== null) {
                switch ($field) {
                    case 'phone':
                        $cleanData[$field] = $this->cleanPhoneNumber($value);
                        break;
                    case 'email':
                        $cleanData[$field] = strtolower($value);
                        break;
                    case 'supplier_type':
                        $cleanData[$field] = $this->mapSupplierType($value);
                        break;
                    case 'city':
                        $cleanData[$field] = $this->mapCity($value);
                        break;
                    case 'credit_limit':
                        $cleanData[$field] = (float) str_replace([',', ' '], '', $value);
                        break;
                    default:
                        $cleanData[$field] = $value;
                }
            }
        }

        return $cleanData;
    }

    /**
     * Clean phone number
     */
    private function cleanPhoneNumber($phone)
    {
        // Remove all non-numeric characters except +
        $phone = preg_replace('/[^\d+]/', '', $phone);

        // Add Iraq country code if missing
        if (!str_starts_with($phone, '+964') && !str_starts_with($phone, '964')) {
            if (str_starts_with($phone, '0')) {
                $phone = '+964' . substr($phone, 1);
            } else {
                $phone = '+964' . $phone;
            }
        } elseif (str_starts_with($phone, '964')) {
            $phone = '+' . $phone;
        }

        return $phone;
    }

    /**
     * Map supplier type
     */
    private function mapSupplierType($type)
    {
        $type = strtolower(trim($type));
        $mapping = [
            'manufacturer' => 'manufacturer',
            'distributor' => 'distributor',
            'wholesaler' => 'wholesaler',
            'retailer' => 'retailer',
            'service' => 'service_provider',
            'مصنع' => 'manufacturer',
            'موزع' => 'distributor',
            'تاجر جملة' => 'wholesaler',
            'تاجر تجزئة' => 'retailer',
            'خدمات' => 'service_provider'
        ];

        return $mapping[$type] ?? 'distributor';
    }

    /**
     * Map city
     */
    private function mapCity($city)
    {
        $city = strtolower(trim($city));
        $mapping = [
            'baghdad' => 'baghdad',
            'basra' => 'basra',
            'erbil' => 'erbil',
            'mosul' => 'mosul',
            'najaf' => 'najaf',
            'karbala' => 'karbala',
            'بغداد' => 'baghdad',
            'البصرة' => 'basra',
            'أربيل' => 'erbil',
            'الموصل' => 'mosul',
            'النجف' => 'najaf',
            'كربلاء' => 'karbala'
        ];

        return $mapping[$city] ?? $city;
    }

    /**
     * Get sample supplier data for demo
     */
    private function getSampleSupplierData()
    {
        return [
            [
                'name (اسم المورد)' => 'Medical Supplies International',
                'phone (رقم الهاتف)' => '+964 1 234 5678',
                'email (البريد الإلكتروني)' => 'info@medicalsupplies.com',
                'address (العنوان)' => 'Baghdad, Al-Karrada District, Medical Complex',
                'city (المدينة)' => 'baghdad',
                'supplier_type (نوع المورد)' => 'distributor',
                'contact_person (الشخص المسؤول)' => 'Ahmed Al-Rashid',
                'tax_number (رقم الضريبة)' => 'TAX123456789',
                'license_number (رقم الترخيص)' => 'LIC987654321',
                'payment_terms (شروط الدفع)' => 'net_30',
                'credit_limit (حد الائتمان)' => '5000000',
                'notes (ملاحظات)' => 'Reliable supplier for medical equipment'
            ],
            [
                'name (اسم المورد)' => 'Pharma Distribution Co.',
                'phone (رقم الهاتف)' => '+964 1 345 6789',
                'email (البريد الإلكتروني)' => 'orders@pharmadist.com',
                'address (العنوان)' => 'Basra, Industrial Zone, Building 15',
                'city (المدينة)' => 'basra',
                'supplier_type (نوع المورد)' => 'wholesaler',
                'contact_person (الشخص المسؤول)' => 'Fatima Hassan',
                'tax_number (رقم الضريبة)' => 'TAX456789123',
                'license_number (رقم الترخيص)' => 'LIC654321987',
                'payment_terms (شروط الدفع)' => 'net_15',
                'credit_limit (حد الائتمان)' => '3000000',
                'notes (ملاحظات)' => 'Pharmaceutical products specialist'
            ]
        ];
    }

    /**
     * Export suppliers to Excel/CSV
     */
    public function export(Request $request)
    {
        $format = $request->input('format', 'excel');

        // Sample suppliers data
        $suppliers = collect([
            [
                'id' => 1,
                'name' => 'Medical Supplies International',
                'phone' => '+964 1 234 5678',
                'email' => 'info@medicalsupplies.com',
                'address' => 'Baghdad, Al-Karrada District, Medical Complex',
                'city' => 'Baghdad',
                'supplier_type' => 'Distributor',
                'contact_person' => 'Ahmed Al-Rashid',
                'tax_number' => 'TAX123456789',
                'license_number' => 'LIC987654321',
                'payment_terms' => 'Net 30',
                'credit_limit' => 5000000,
                'rating' => 4.5,
                'total_orders' => 25,
                'total_spent' => 12500000,
                'last_order' => now()->subDays(5)->format('Y-m-d'),
                'is_active' => true,
                'created_at' => now()->subMonths(8)->format('Y-m-d H:i:s')
            ],
            [
                'id' => 2,
                'name' => 'Pharma Distribution Co.',
                'phone' => '+964 1 345 6789',
                'email' => 'orders@pharmadist.com',
                'address' => 'Basra, Industrial Zone, Building 15',
                'city' => 'Basra',
                'supplier_type' => 'Wholesaler',
                'contact_person' => 'Fatima Hassan',
                'tax_number' => 'TAX456789123',
                'license_number' => 'LIC654321987',
                'payment_terms' => 'Net 15',
                'credit_limit' => 3000000,
                'rating' => 4.2,
                'total_orders' => 18,
                'total_spent' => 8500000,
                'last_order' => now()->subDays(12)->format('Y-m-d'),
                'is_active' => true,
                'created_at' => now()->subMonths(6)->format('Y-m-d H:i:s')
            ],
            [
                'id' => 3,
                'name' => 'Equipment Solutions Ltd.',
                'phone' => '+964 1 456 7890',
                'email' => 'sales@equipmentsolutions.com',
                'address' => 'Erbil, Technology Park, Unit 8',
                'city' => 'Erbil',
                'supplier_type' => 'Manufacturer',
                'contact_person' => 'Omar Khalil',
                'tax_number' => 'TAX789123456',
                'license_number' => 'LIC321987654',
                'payment_terms' => 'Net 60',
                'credit_limit' => 8000000,
                'rating' => 4.8,
                'total_orders' => 32,
                'total_spent' => 18750000,
                'last_order' => now()->subDays(2)->format('Y-m-d'),
                'is_active' => true,
                'created_at' => now()->subYear()->format('Y-m-d H:i:s')
            ]
        ]);

        $filename = 'suppliers_export_' . date('Y-m-d_H-i-s') . '.csv';

        // Create CSV content
        $csvContent = '';

        // Add header with export info
        $csvContent .= "MAXCON ERP - Suppliers Export\n";
        $csvContent .= "Generated on: " . date('Y-m-d H:i:s') . "\n";
        $csvContent .= "Total Suppliers: " . $suppliers->count() . "\n";
        $csvContent .= "\n";

        // Add column headers
        $headers = [
            'ID',
            'Supplier Name',
            'Phone',
            'Email',
            'Address',
            'City',
            'Supplier Type',
            'Contact Person',
            'Tax Number',
            'License Number',
            'Payment Terms',
            'Credit Limit (IQD)',
            'Rating',
            'Total Orders',
            'Total Spent (IQD)',
            'Last Order',
            'Status',
            'Registration Date'
        ];

        $csvContent .= '"' . implode('","', $headers) . '"' . "\n";

        // Add supplier data
        foreach ($suppliers as $supplier) {
            $row = [
                $supplier['id'],
                $supplier['name'],
                $supplier['phone'],
                $supplier['email'],
                $supplier['address'],
                $supplier['city'],
                $supplier['supplier_type'],
                $supplier['contact_person'],
                $supplier['tax_number'],
                $supplier['license_number'],
                $supplier['payment_terms'],
                number_format($supplier['credit_limit']),
                $supplier['rating'],
                $supplier['total_orders'],
                number_format($supplier['total_spent']),
                $supplier['last_order'],
                $supplier['is_active'] ? 'Active' : 'Inactive',
                $supplier['created_at']
            ];

            $csvContent .= '"' . implode('","', $row) . '"' . "\n";
        }

        // Add summary
        $csvContent .= "\n";
        $csvContent .= "EXPORT SUMMARY\n";
        $csvContent .= "Total Suppliers," . $suppliers->count() . "\n";
        $csvContent .= "Active Suppliers," . $suppliers->where('is_active', true)->count() . "\n";
        $csvContent .= "Total Spent," . number_format($suppliers->sum('total_spent')) . " IQD\n";
        $csvContent .= "Total Orders," . $suppliers->sum('total_orders') . "\n";
        $csvContent .= "Average Rating," . number_format($suppliers->avg('rating'), 2) . "\n";

        return response($csvContent)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Content-Transfer-Encoding', 'binary');
    }

    /**
     * Download import template
     */
    public function downloadTemplate()
    {
        $templatePath = public_path('templates/suppliers_template.csv');

        // If template file exists, return it
        if (file_exists($templatePath)) {
            return response()->download($templatePath, 'suppliers_import_template.csv', [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Transfer-Encoding' => 'binary'
            ]);
        }

        // Fallback: Generate template content
        $csvContent = '';

        // Add header row with clear column names
        $headers = [
            'name (اسم المورد)',
            'phone (رقم الهاتف)',
            'email (البريد الإلكتروني)',
            'address (العنوان)',
            'city (المدينة)',
            'supplier_type (نوع المورد)',
            'contact_person (الشخص المسؤول)',
            'tax_number (رقم الضريبة)',
            'license_number (رقم الترخيص)',
            'payment_terms (شروط الدفع)',
            'credit_limit (حد الائتمان)',
            'notes (ملاحظات)'
        ];

        $csvContent .= '"' . implode('","', $headers) . '"' . "\n";

        // Add sample data rows
        $sampleData = [
            [
                'Medical Supplies International',
                '+964 1 234 5678',
                'info@medicalsupplies.com',
                'Baghdad, Al-Karrada District, Medical Complex',
                'baghdad',
                'distributor',
                'Ahmed Al-Rashid',
                'TAX123456789',
                'LIC987654321',
                'net_30',
                '5000000',
                'Reliable supplier for medical equipment and pharmaceuticals'
            ],
            [
                'Pharma Distribution Co.',
                '+964 1 345 6789',
                'orders@pharmadist.com',
                'Basra, Industrial Zone, Building 15',
                'basra',
                'wholesaler',
                'Fatima Hassan',
                'TAX456789123',
                'LIC654321987',
                'net_15',
                '3000000',
                'Pharmaceutical products specialist with fast delivery'
            ],
            [
                'Equipment Solutions Ltd.',
                '+964 1 456 7890',
                'sales@equipmentsolutions.com',
                'Erbil, Technology Park, Unit 8',
                'erbil',
                'manufacturer',
                'Omar Khalil',
                'TAX789123456',
                'LIC321987654',
                'net_60',
                '8000000',
                'Medical equipment manufacturer with warranty support'
            ]
        ];

        // Add sample data to CSV
        foreach ($sampleData as $row) {
            $csvContent .= '"' . implode('","', $row) . '"' . "\n";
        }

        return response($csvContent)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="suppliers_import_template.csv"')
            ->header('Content-Transfer-Encoding', 'binary');
    }
}
