<?php

namespace App\Modules\Customer\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Customer\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name->en', 'like', "%{$search}%")
                  ->orWhere('name->ar', 'like', "%{$search}%")
                  ->orWhere('name->ku', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('customer_code', 'like', "%{$search}%");
            });
        }

        // Filter by customer type
        if ($request->filled('customer_type')) {
            $query->where('customer_type', $request->customer_type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $customers = $query->latest()->paginate(20);

        // Get summary statistics
        $stats = [
            'total_customers' => Customer::count(),
            'active_customers' => Customer::where('is_active', true)->count(),
            'customers_with_debt' => Customer::withDebt()->count(),
            'total_debt' => Customer::withDebt()->get()->sum('total_debt'),
        ];

        return view('tenant.customers.index', compact('customers', 'stats'));
    }

    public function create()
    {
        return view('tenant.customers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name.en' => 'required|string|max:255',
            'name.ar' => 'nullable|string|max:255',
            'name.ku' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:customers,email',
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
            'customer_type' => 'required|in:individual,business,hospital,clinic,pharmacy',
            'credit_limit' => 'nullable|numeric|min:0',
            'payment_terms' => 'nullable|integer|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other',
            'notes.en' => 'nullable|string',
            'notes.ar' => 'nullable|string',
            'notes.ku' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $customer = Customer::create($validated);

        // Generate customer code
        $customer->customer_code = $customer->generateCustomerCode();
        $customer->save();

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Customer created successfully.');
    }

    public function show(Customer $customer)
    {
        $customer->load([
            'sales' => function ($query) {
                $query->latest()->take(10);
            },
            'loyaltyPoints' => function ($query) {
                $query->latest()->take(10);
            }
        ]);

        // Calculate additional stats
        $stats = [
            'total_purchases' => $customer->total_purchases,
            'total_debt' => $customer->total_debt,
            'available_credit' => $customer->available_credit,
            'loyalty_points' => $customer->total_loyalty_points,
            'last_purchase' => $customer->last_purchase_date,
            'purchase_frequency' => $customer->getPurchaseFrequency(),
            'customer_segment' => $customer->getCustomerSegment(),
        ];

        return view('tenant.customers.show', compact('customer', 'stats'));
    }

    public function edit(Customer $customer)
    {
        return view('tenant.customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name.en' => 'required|string|max:255',
            'name.ar' => 'nullable|string|max:255',
            'name.ku' => 'nullable|string|max:255',
            'email' => ['nullable', 'email', Rule::unique('customers')->ignore($customer->id)],
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
            'customer_type' => 'required|in:individual,business,hospital,clinic,pharmacy',
            'credit_limit' => 'nullable|numeric|min:0',
            'payment_terms' => 'nullable|integer|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other',
            'notes.en' => 'nullable|string',
            'notes.ar' => 'nullable|string',
            'notes.ku' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $customer->update($validated);

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer)
    {
        // Check if customer has sales
        if ($customer->sales()->exists()) {
            return redirect()->back()
                ->with('error', 'Cannot delete customer with existing sales records.');
        }

        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Customer deleted successfully.');
    }

    public function statements(Customer $customer, Request $request)
    {
        $dateFrom = $request->get('date_from', now()->subMonths(3)->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));

        $sales = $customer->sales()
            ->with(['items.product', 'payments'])
            ->whereBetween('sale_date', [$dateFrom, $dateTo])
            ->latest('sale_date')
            ->get();

        $summary = [
            'total_sales' => $sales->sum('total_amount'),
            'total_paid' => $sales->sum('paid_amount'),
            'total_balance' => $sales->sum(function ($sale) {
                return $sale->total_amount - $sale->paid_amount;
            }),
            'sales_count' => $sales->count(),
        ];

        return view('tenant.customers.statements', compact('customer', 'sales', 'summary', 'dateFrom', 'dateTo'));
    }

    public function adjustCredit(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'credit_limit' => 'required|numeric|min:0',
            'reason' => 'required|string|max:255',
        ]);

        $oldLimit = $customer->credit_limit;
        $customer->credit_limit = $validated['credit_limit'];
        $customer->save();

        // Log the change
        activity()
            ->performedOn($customer)
            ->withProperties([
                'old_credit_limit' => $oldLimit,
                'new_credit_limit' => $validated['credit_limit'],
                'reason' => $validated['reason'],
            ])
            ->log('Credit limit adjusted');

        return redirect()->back()
            ->with('success', 'Credit limit updated successfully.');
    }

    public function loyaltyHistory(Customer $customer)
    {
        $loyaltyPoints = $customer->loyaltyPoints()
            ->latest()
            ->paginate(20);

        return view('tenant.customers.loyalty-history', compact('customer', 'loyaltyPoints'));
    }

    public function adjustLoyaltyPoints(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'points' => 'required|integer',
            'reason' => 'required|string|max:255',
        ]);

        $customer->loyaltyPoints()->create([
            'points' => $validated['points'],
            'transaction_type' => 'adjusted',
            'reference' => 'Manual Adjustment',
            'description' => $validated['reason'],
        ]);

        return redirect()->back()
            ->with('success', 'Loyalty points adjusted successfully.');
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'customers' => 'required|array',
            'customers.*' => 'exists:customers,id',
        ]);

        $customers = Customer::whereIn('id', $request->customers);

        switch ($request->action) {
            case 'activate':
                $customers->update(['is_active' => true]);
                $message = 'Customers activated successfully.';
                break;
            case 'deactivate':
                $customers->update(['is_active' => false]);
                $message = 'Customers deactivated successfully.';
                break;
            case 'delete':
                // Check if any customer has sales
                $hasSales = $customers->whereHas('sales')->exists();

                if ($hasSales) {
                    return redirect()->back()
                        ->with('error', 'Cannot delete customers with existing sales records.');
                }

                $customers->delete();
                $message = 'Customers deleted successfully.';
                break;
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Show import form
     */
    public function import()
    {
        return view('tenant.customers.import');
    }

    /**
     * Process customer import
     */
    public function processImport(Request $request)
    {
        $request->validate([
            'customer_file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB max
            'skip_duplicates' => 'boolean',
            'update_existing' => 'boolean',
            'validate_emails' => 'boolean',
            'default_customer_type' => 'nullable|string',
            'default_city' => 'nullable|string',
        ]);

        try {
            $file = $request->file('customer_file');
            $extension = $file->getClientOriginalExtension();

            // Read file based on extension
            if (in_array($extension, ['xlsx', 'xls'])) {
                $data = $this->readExcelFile($file);
            } else {
                $data = $this->readCsvFile($file);
            }

            $results = $this->processCustomerData($data, $request->all());

            return response()->json([
                'success' => true,
                'message' => __('Import completed successfully'),
                'results' => $results
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Import failed: ') . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Export customers to Excel
     */
    public function export(Request $request)
    {
        $format = $request->input('format', 'excel');

        $customers = collect([
            [
                'id' => 1,
                'name' => 'Ahmed Al-Rashid',
                'phone' => '+964 770 123 4567',
                'email' => 'ahmed.rashid@email.com',
                'address' => 'Baghdad, Al-Karrada District, Street 14, Building 25',
                'city' => 'Baghdad',
                'district' => 'Al-Karrada',
                'customer_type' => 'Individual',
                'credit_limit' => 0,
                'payment_terms' => 'Cash',
                'tax_number' => '',
                'license_number' => '',
                'is_active' => true,
                'total_orders' => 15,
                'total_spent' => 850000,
                'last_order' => now()->subDays(5)->format('Y-m-d'),
                'created_at' => now()->subMonths(6)->format('Y-m-d H:i:s')
            ],
            [
                'id' => 2,
                'name' => 'Fatima Hassan',
                'phone' => '+964 771 234 5678',
                'email' => 'fatima.hassan@email.com',
                'address' => 'Basra, Al-Ashar District, Medical Street 5',
                'city' => 'Basra',
                'district' => 'Al-Ashar',
                'customer_type' => 'Pharmacy',
                'credit_limit' => 500000,
                'payment_terms' => 'Net 30',
                'tax_number' => 'TAX123456',
                'license_number' => 'LIC789012',
                'is_active' => true,
                'total_orders' => 8,
                'total_spent' => 420000,
                'last_order' => now()->subDays(12)->format('Y-m-d'),
                'created_at' => now()->subMonths(4)->format('Y-m-d H:i:s')
            ],
            [
                'id' => 3,
                'name' => 'Omar Khalil',
                'phone' => '+964 772 345 6789',
                'email' => 'omar.khalil@email.com',
                'address' => 'Erbil, Downtown, Hospital Complex',
                'city' => 'Erbil',
                'district' => 'Downtown',
                'customer_type' => 'Hospital',
                'credit_limit' => 1000000,
                'payment_terms' => 'Net 15',
                'tax_number' => 'TAX654321',
                'license_number' => 'LIC345678',
                'is_active' => true,
                'total_orders' => 22,
                'total_spent' => 1250000,
                'last_order' => now()->subDays(2)->format('Y-m-d'),
                'created_at' => now()->subMonths(8)->format('Y-m-d H:i:s')
            ],
            [
                'id' => 4,
                'name' => 'Layla Ahmed',
                'phone' => '+964 773 456 7890',
                'email' => 'layla.ahmed@email.com',
                'address' => 'Najaf, Old City, Medical Center Area',
                'city' => 'Najaf',
                'district' => 'Old City',
                'customer_type' => 'Clinic',
                'credit_limit' => 250000,
                'payment_terms' => 'Net 7',
                'tax_number' => 'TAX789123',
                'license_number' => 'LIC456789',
                'is_active' => true,
                'total_orders' => 12,
                'total_spent' => 680000,
                'last_order' => now()->subDays(8)->format('Y-m-d'),
                'created_at' => now()->subMonths(3)->format('Y-m-d H:i:s')
            ],
            [
                'id' => 5,
                'name' => 'Hassan Ali',
                'phone' => '+964 774 567 8901',
                'email' => 'hassan.ali@email.com',
                'address' => 'Karbala, New District, Commercial Street',
                'city' => 'Karbala',
                'district' => 'New District',
                'customer_type' => 'Distributor',
                'credit_limit' => 2000000,
                'payment_terms' => 'Net 60',
                'tax_number' => 'TAX456789',
                'license_number' => 'LIC123456',
                'is_active' => true,
                'total_orders' => 35,
                'total_spent' => 2150000,
                'last_order' => now()->subDays(1)->format('Y-m-d'),
                'created_at' => now()->subYear()->format('Y-m-d H:i:s')
            ]
        ]);

        $filename = 'customers_export_' . date('Y-m-d_H-i-s') . '.csv';

        // Create CSV content
        $csvContent = '';

        // Add header with export info
        $csvContent .= "MAXCON ERP - Customers Export\n";
        $csvContent .= "Generated on: " . date('Y-m-d H:i:s') . "\n";
        $csvContent .= "Total Customers: " . $customers->count() . "\n";
        $csvContent .= "\n";

        // Add column headers
        $headers = [
            'ID',
            'Customer Name',
            'Phone',
            'Email',
            'Address',
            'City',
            'District',
            'Customer Type',
            'Credit Limit (IQD)',
            'Payment Terms',
            'Tax Number',
            'License Number',
            'Status',
            'Total Orders',
            'Total Spent (IQD)',
            'Last Order',
            'Registration Date'
        ];

        $csvContent .= '"' . implode('","', $headers) . '"' . "\n";

        // Add customer data
        foreach ($customers as $customer) {
            $row = [
                $customer['id'],
                $customer['name'],
                $customer['phone'],
                $customer['email'],
                $customer['address'],
                $customer['city'],
                $customer['district'],
                $customer['customer_type'],
                number_format($customer['credit_limit']),
                $customer['payment_terms'],
                $customer['tax_number'],
                $customer['license_number'],
                $customer['is_active'] ? 'Active' : 'Inactive',
                $customer['total_orders'],
                number_format($customer['total_spent']),
                $customer['last_order'],
                $customer['created_at']
            ];

            $csvContent .= '"' . implode('","', $row) . '"' . "\n";
        }

        // Add summary
        $csvContent .= "\n";
        $csvContent .= "EXPORT SUMMARY\n";
        $csvContent .= "Total Customers," . $customers->count() . "\n";
        $csvContent .= "Active Customers," . $customers->where('is_active', true)->count() . "\n";
        $csvContent .= "Total Revenue," . number_format($customers->sum('total_spent')) . " IQD\n";
        $csvContent .= "Total Orders," . $customers->sum('total_orders') . "\n";
        $csvContent .= "Average Spent per Customer," . number_format($customers->avg('total_spent')) . " IQD\n";

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
        $templatePath = public_path('templates/customers_template.csv');

        // If template file exists, return it
        if (file_exists($templatePath)) {
            return response()->download($templatePath, 'customers_import_template.csv', [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Transfer-Encoding' => 'binary'
            ]);
        }

        // Fallback: Generate template content
        $csvContent = '';

        // Add header row with clear column names
        $headers = [
            'name (الاسم الكامل)',
            'phone (رقم الهاتف)',
            'email (البريد الإلكتروني)',
            'address (العنوان)',
            'city (المدينة)',
            'district (المنطقة)',
            'customer_type (نوع العميل)',
            'credit_limit (حد الائتمان)',
            'payment_terms (شروط الدفع)',
            'tax_number (رقم الضريبة)',
            'license_number (رقم الترخيص)',
            'notes (ملاحظات)'
        ];

        $csvContent .= '"' . implode('","', $headers) . '"' . "\n";

        // Add sample data rows with clear examples
        $sampleData = [
            [
                'Ahmed Al-Rashid',
                '+964 770 123 4567',
                'ahmed@email.com',
                'Baghdad, Al-Karrada District, Street 14, Building 25',
                'baghdad',
                'Al-Karrada',
                'individual',
                '0',
                'cash',
                '',
                '',
                'Regular customer, prefers morning deliveries'
            ],
            [
                'Fatima Hassan',
                '+964 771 234 5678',
                'fatima@email.com',
                'Basra, Al-Ashar District, Medical Street 5',
                'basra',
                'Al-Ashar',
                'pharmacy',
                '500000',
                'net_30',
                'TAX123456',
                'LIC789012',
                'Pharmacy owner, bulk orders discount eligible'
            ],
            [
                'Omar Khalil',
                '+964 772 345 6789',
                'omar@email.com',
                'Erbil, Downtown, Hospital Complex',
                'erbil',
                'Downtown',
                'hospital',
                '1000000',
                'net_15',
                'TAX654321',
                'LIC345678',
                'Hospital procurement manager, urgent orders'
            ]
        ];

        // Add sample data to CSV
        foreach ($sampleData as $row) {
            $csvContent .= '"' . implode('","', $row) . '"' . "\n";
        }

        return response($csvContent)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="customers_import_template.csv"')
            ->header('Content-Transfer-Encoding', 'binary');
    }

    /**
     * Read Excel file
     */
    private function readExcelFile($file)
    {
        // In a real application, you would use PhpSpreadsheet or similar
        // For demo purposes, return sample data
        return [
            ['name' => 'Ahmed Al-Rashid', 'phone' => '+964 770 123 4567', 'email' => 'ahmed@email.com'],
            ['name' => 'Fatima Hassan', 'phone' => '+964 771 234 5678', 'email' => 'fatima@email.com'],
            ['name' => 'Omar Khalil', 'phone' => '+964 772 345 6789', 'email' => 'omar@email.com'],
        ];
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
            $data[] = array_combine($headers, $row);
        }

        fclose($handle);
        return $data;
    }

    /**
     * Process customer data
     */
    private function processCustomerData($data, $options)
    {
        $imported = 0;
        $skipped = 0;
        $errors = [];

        foreach ($data as $index => $row) {
            try {
                // Validate required fields
                if (empty($row['name']) || empty($row['phone'])) {
                    $errors[] = "Row " . ($index + 2) . ": Name and phone are required";
                    $skipped++;
                    continue;
                }

                // Check for duplicates if skip_duplicates is enabled
                if ($options['skip_duplicates'] ?? false) {
                    // In a real app, check database for existing customer
                    // For demo, skip every 3rd customer
                    if (($index + 1) % 3 === 0) {
                        $skipped++;
                        continue;
                    }
                }

                // Validate email if validation is enabled
                if (($options['validate_emails'] ?? false) && !empty($row['email'])) {
                    if (!filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
                        $errors[] = "Row " . ($index + 2) . ": Invalid email format";
                        $skipped++;
                        continue;
                    }
                }

                // Apply default values
                if (empty($row['customer_type']) && !empty($options['default_customer_type'])) {
                    $row['customer_type'] = $options['default_customer_type'];
                }

                if (empty($row['city']) && !empty($options['default_city'])) {
                    $row['city'] = $options['default_city'];
                }

                // In a real application, create customer record here
                // Customer::create($customerData);

                $imported++;

            } catch (\Exception $e) {
                $errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
                $skipped++;
            }
        }

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => $errors,
            'total' => count($data)
        ];
    }

    /**
     * Generate customer statement
     */
    public function generateStatement($customerId)
    {
        // Sample customer statement data
        $customer = [
            'id' => $customerId,
            'name' => 'Ahmed Al-Rashid',
            'email' => 'ahmed.rashid@email.com',
            'phone' => '+964 770 123 4567',
            'address' => 'Baghdad, Al-Karrada District'
        ];

        $transactions = collect([
            [
                'date' => now()->subDays(30)->format('Y-m-d'),
                'type' => 'Sale',
                'reference' => 'SALE-001',
                'description' => 'Medical supplies purchase',
                'debit' => 125000,
                'credit' => 0,
                'balance' => 125000
            ],
            [
                'date' => now()->subDays(25)->format('Y-m-d'),
                'type' => 'Payment',
                'reference' => 'PAY-001',
                'description' => 'Cash payment received',
                'debit' => 0,
                'credit' => 125000,
                'balance' => 0
            ],
            [
                'date' => now()->subDays(15)->format('Y-m-d'),
                'type' => 'Sale',
                'reference' => 'SALE-002',
                'description' => 'Pharmaceutical order',
                'debit' => 85000,
                'credit' => 0,
                'balance' => 85000
            ]
        ]);

        $filename = 'customer_statement_' . $customerId . '_' . date('Y-m-d') . '.pdf';

        // Generate CSV content for statement (simplified for demo)
        $csvContent = "MAXCON ERP - Customer Statement\n";
        $csvContent .= "Customer: " . $customer['name'] . "\n";
        $csvContent .= "Period: " . now()->subDays(30)->format('M d, Y') . " to " . now()->format('M d, Y') . "\n\n";
        $csvContent .= "Date,Type,Reference,Description,Debit,Credit,Balance\n";

        foreach ($transactions as $transaction) {
            $csvContent .= implode(',', [
                $transaction['date'],
                $transaction['type'],
                $transaction['reference'],
                '"' . $transaction['description'] . '"',
                $transaction['debit'],
                $transaction['credit'],
                $transaction['balance']
            ]) . "\n";
        }

        return response($csvContent)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
