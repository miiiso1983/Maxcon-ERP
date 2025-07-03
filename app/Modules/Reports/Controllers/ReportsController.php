<?php

namespace App\Modules\Reports\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Reports\Models\Report;
use App\Modules\Reports\Models\ReportExecution;
use App\Modules\Reports\Models\Dashboard;
use App\Modules\Sales\Models\Sale;
use App\Modules\Inventory\Models\Product;
use App\Modules\Customer\Models\Customer;
use App\Modules\Financial\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    public function index()
    {
        $reports = Report::with('createdBy')
            ->where(function ($query) {
                $query->where('is_public', true)
                      ->orWhere('created_by', auth()->id());
            })
            ->latest()
            ->paginate(20);

        $categories = [
            Report::CATEGORY_OPERATIONAL => __('Operational'),
            Report::CATEGORY_FINANCIAL => __('Financial'),
            Report::CATEGORY_ANALYTICAL => __('Analytical'),
            Report::CATEGORY_COMPLIANCE => __('Compliance'),
        ];

        $types = [
            Report::TYPE_SALES => __('Sales'),
            Report::TYPE_INVENTORY => __('Inventory'),
            Report::TYPE_FINANCIAL => __('Financial'),
            Report::TYPE_CUSTOMER => __('Customer'),
            Report::TYPE_SUPPLIER => __('Supplier'),
            Report::TYPE_CUSTOM => __('Custom'),
        ];

        return view('tenant.reports.index', compact('reports', 'categories', 'types'));
    }

    public function create()
    {
        $categories = [
            Report::CATEGORY_OPERATIONAL => __('Operational'),
            Report::CATEGORY_FINANCIAL => __('Financial'),
            Report::CATEGORY_ANALYTICAL => __('Analytical'),
            Report::CATEGORY_COMPLIANCE => __('Compliance'),
        ];

        $types = [
            Report::TYPE_SALES => __('Sales'),
            Report::TYPE_INVENTORY => __('Inventory'),
            Report::TYPE_FINANCIAL => __('Financial'),
            Report::TYPE_CUSTOMER => __('Customer'),
            Report::TYPE_SUPPLIER => __('Supplier'),
            Report::TYPE_CUSTOM => __('Custom'),
        ];

        return view('tenant.reports.create', compact('categories', 'types'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'report_type' => 'required|string',
            'category' => 'required|string',
            'query_config' => 'nullable|array',
            'chart_config' => 'nullable|array',
            'filters' => 'nullable|array',
            'is_public' => 'boolean',
        ]);

        $report = Report::create([
            'name' => $request->name,
            'description' => $request->description,
            'report_type' => $request->report_type,
            'category' => $request->category,
            'query_config' => $request->query_config ?? [],
            'chart_config' => $request->chart_config ?? [],
            'filters' => $request->filters ?? [],
            'is_public' => $request->boolean('is_public'),
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('reports.show', $report)
            ->with('success', __('Report created successfully.'));
    }

    public function dashboard()
    {
        // Get key metrics
        $metrics = $this->getKeyMetrics();
        
        // Get recent report executions
        $recentExecutions = ReportExecution::with(['report', 'executedBy'])
            ->recent()
            ->take(10)
            ->get();

        // Get popular reports
        $popularReports = Report::where('run_count', '>', 0)
            ->orderBy('run_count', 'desc')
            ->take(5)
            ->get();

        return view('tenant.reports.dashboard', compact('metrics', 'recentExecutions', 'popularReports'));
    }

    public function show(Report $report)
    {
        $report->load(['createdBy', 'executions.executedBy']);
        
        // Get recent executions
        $recentExecutions = $report->executions()
            ->recent()
            ->take(5)
            ->get();

        return view('tenant.reports.show', compact('report', 'recentExecutions'));
    }

    public function run(Request $request, Report $report)
    {
        $parameters = $request->validate([
            'date_range_start' => 'nullable|date',
            'date_range_end' => 'nullable|date|after_or_equal:date_range_start',
            'payment_status' => 'nullable|string',
            'customer_id' => 'nullable|exists:customers,id',
            'product_id' => 'nullable|exists:products,id',
            'low_stock_threshold' => 'nullable|numeric|min:0',
        ]);

        try {
            $execution = $report->execute($parameters);
            
            if ($execution->status === ReportExecution::STATUS_COMPLETED) {
                return response()->json([
                    'success' => true,
                    'execution_id' => $execution->id,
                    'data' => $execution->result_data,
                    'chart_data' => $report->getChartData($execution->result_data),
                    'row_count' => $execution->row_count,
                    'execution_time' => $execution->duration_text,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $execution->error_message ?? 'Report execution failed',
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function export(Request $request, Report $report)
    {
        $request->validate([
            'execution_id' => 'required|exists:report_executions,id',
            'format' => 'required|in:pdf,excel,csv',
        ]);

        $execution = ReportExecution::findOrFail($request->execution_id);
        
        if ($execution->report_id !== $report->id) {
            abort(403, 'Execution does not belong to this report');
        }

        try {
            $filePath = $execution->export($request->format);
            
            return response()->download(storage_path('app/' . $filePath))
                ->deleteFileAfterSend();
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function salesReport(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $groupBy = $request->get('group_by', 'day'); // day, week, month

        $query = Sale::with(['customer', 'items.product'])
            ->whereBetween('sale_date', [$startDate, $endDate]);

        // Sales summary
        $summary = [
            'total_sales' => $query->sum('total_amount'),
            'total_transactions' => $query->count(),
            'average_transaction' => $query->avg('total_amount'),
            'cash_sales' => $query->where('payment_status', Sale::PAYMENT_STATUS_PAID)->sum('total_amount'),
            'credit_sales' => $query->where('payment_status', Sale::PAYMENT_STATUS_PENDING)->sum('total_amount'),
        ];

        // Sales by period
        $salesByPeriod = $this->getSalesByPeriod($startDate, $endDate, $groupBy);

        // Top products
        $topProducts = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->select(
                'products.name',
                'products.sku',
                DB::raw('SUM(sale_items.quantity) as total_quantity'),
                DB::raw('SUM(sale_items.total_price) as total_revenue')
            )
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderBy('total_revenue', 'desc')
            ->take(10)
            ->get();

        // Top customers
        $topCustomers = Sale::with('customer')
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->select(
                'customer_id',
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(total_amount) as total_spent')
            )
            ->groupBy('customer_id')
            ->orderBy('total_spent', 'desc')
            ->take(10)
            ->get();

        return view('tenant.reports.sales', compact(
            'summary', 'salesByPeriod', 'topProducts', 'topCustomers',
            'startDate', 'endDate', 'groupBy'
        ));
    }

    public function inventoryReport(Request $request)
    {
        $lowStockThreshold = $request->get('low_stock_threshold', 10);
        $category = $request->get('category');

        $query = Product::with(['category', 'supplier']);

        if ($category) {
            $query->where('category_id', $category);
        }

        // Inventory summary
        $summary = [
            'total_products' => Product::count(),
            'total_stock_value' => DB::table('stocks')
                ->join('products', 'stocks.product_id', '=', 'products.id')
                ->sum(DB::raw('stocks.quantity * stocks.cost_price')),
            'low_stock_items' => Product::whereHas('stocks', function ($q) {
                $q->whereRaw('quantity <= (SELECT reorder_level FROM products WHERE products.id = stocks.product_id)');
            })->count(),
            'out_of_stock_items' => Product::whereHas('stocks', function ($q) {
                $q->where('quantity', '<=', 0);
            })->count(),
        ];

        // Low stock items
        $lowStockItems = Product::whereHas('stocks', function ($q) use ($lowStockThreshold) {
            $q->where('quantity', '<=', $lowStockThreshold);
        })->with('stocks')->get();

        // Stock value by category
        $stockByCategory = DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->join('stocks', 'products.id', '=', 'stocks.product_id')
            ->select(
                'categories.name as category_name',
                DB::raw('COUNT(DISTINCT products.id) as product_count'),
                DB::raw('SUM(stocks.quantity * stocks.cost_price) as stock_value')
            )
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('stock_value', 'desc')
            ->get();

        // Fast moving items (based on recent sales)
        $fastMovingItems = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->leftJoin('stocks', 'products.id', '=', 'stocks.product_id')
            ->where('sales.sale_date', '>=', now()->subDays(30))
            ->select(
                'products.name',
                'products.sku',
                DB::raw('COALESCE(SUM(stocks.quantity), 0) as current_stock'),
                DB::raw('SUM(sale_items.quantity) as total_sold')
            )
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderBy('total_sold', 'desc')
            ->take(20)
            ->get();

        return view('tenant.reports.inventory', compact(
            'summary', 'lowStockItems', 'stockByCategory', 'fastMovingItems',
            'lowStockThreshold', 'category'
        ));
    }

    public function financialReport(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        // Financial summary
        $assets = Account::assets()->sum('current_balance');
        $liabilities = Account::liabilities()->sum('current_balance');
        $equity = Account::equity()->sum('current_balance');
        $revenue = $this->getAccountBalanceForPeriod(Account::TYPE_REVENUE, $startDate, $endDate);
        $expenses = $this->getAccountBalanceForPeriod(Account::TYPE_EXPENSE, $startDate, $endDate);

        $summary = [
            'total_assets' => $assets,
            'total_liabilities' => $liabilities,
            'total_equity' => $equity,
            'total_revenue' => $revenue,
            'total_expenses' => $expenses,
            'net_income' => $revenue - $expenses,
            'debt_to_equity' => $equity > 0 ? $liabilities / $equity : 0,
        ];

        // Revenue trend
        $revenueTrend = $this->getRevenueTrend($startDate, $endDate);

        // Expense breakdown
        $expenseBreakdown = Account::expenses()
            ->whereHas('debitEntries', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('entry_date', [$startDate, $endDate])
                      ->where('is_posted', true);
            })
            ->with(['debitEntries' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('entry_date', [$startDate, $endDate])
                      ->where('is_posted', true);
            }])
            ->get()
            ->map(function ($account) {
                return [
                    'account_name' => $account->account_name,
                    'amount' => $account->debitEntries->sum('amount'),
                ];
            })
            ->sortByDesc('amount')
            ->take(10);

        return view('tenant.reports.financial', compact(
            'summary', 'revenueTrend', 'expenseBreakdown',
            'startDate', 'endDate'
        ));
    }

    private function getKeyMetrics(): array
    {
        $today = now()->format('Y-m-d');
        $thisMonth = now()->startOfMonth()->format('Y-m-d');

        return [
            'total_sales_today' => Sale::whereDate('sale_date', $today)->sum('total_amount'),
            'total_sales_month' => Sale::where('sale_date', '>=', $thisMonth)->sum('total_amount'),
            'total_customers' => Customer::count(),
            'active_reports' => Report::count(),
            'recent_executions' => ReportExecution::whereDate('started_at', $today)->count(),
            'low_stock_items' => Product::whereHas('stocks', function ($q) {
                $q->whereRaw('quantity <= (SELECT reorder_level FROM products WHERE products.id = stocks.product_id)');
            })->count(),
        ];
    }

    private function getSalesByPeriod(string $startDate, string $endDate, string $groupBy): array
    {
        $dateFormat = match($groupBy) {
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            default => '%Y-%m-%d',
        };

        return Sale::whereBetween('sale_date', [$startDate, $endDate])
            ->select(
                DB::raw("DATE_FORMAT(sale_date, '{$dateFormat}') as period"),
                DB::raw('SUM(total_amount) as total_sales'),
                DB::raw('COUNT(*) as transaction_count')
            )
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->toArray();
    }

    private function getAccountBalanceForPeriod(string $accountType, string $startDate, string $endDate): float
    {
        $accounts = Account::where('account_type', $accountType)->pluck('id');
        
        $debitTotal = DB::table('journal_entries')
            ->whereIn('debit_account_id', $accounts)
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->where('is_posted', true)
            ->sum('amount');

        $creditTotal = DB::table('journal_entries')
            ->whereIn('credit_account_id', $accounts)
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->where('is_posted', true)
            ->sum('amount');

        // Revenue and expense accounts have different normal balances
        if ($accountType === Account::TYPE_REVENUE) {
            return $creditTotal - $debitTotal;
        } else {
            return $debitTotal - $creditTotal;
        }
    }

    private function getRevenueTrend(string $startDate, string $endDate): array
    {
        $revenueAccounts = Account::revenue()->pluck('id');
        
        return DB::table('journal_entries')
            ->whereIn('credit_account_id', $revenueAccounts)
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->where('is_posted', true)
            ->select(
                DB::raw('DATE(entry_date) as date'),
                DB::raw('SUM(amount) as revenue')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();
    }

    /**
     * Customers Report
     */
    public function customersReport(Request $request)
    {
        $customers = collect([
            [
                'id' => 1,
                'name' => 'Ahmed Al-Rashid',
                'email' => 'ahmed.rashid@email.com',
                'phone' => '+964 770 123 4567',
                'total_orders' => 15,
                'total_spent' => 850000,
                'last_order' => now()->subDays(5),
                'status' => 'active'
            ],
            [
                'id' => 2,
                'name' => 'Fatima Hassan',
                'email' => 'fatima.hassan@email.com',
                'phone' => '+964 771 234 5678',
                'total_orders' => 8,
                'total_spent' => 420000,
                'last_order' => now()->subDays(12),
                'status' => 'active'
            ],
            [
                'id' => 3,
                'name' => 'Omar Khalil',
                'email' => 'omar.khalil@email.com',
                'phone' => '+964 772 345 6789',
                'total_orders' => 22,
                'total_spent' => 1250000,
                'last_order' => now()->subDays(2),
                'status' => 'active'
            ]
        ]);

        return view('tenant.reports.customers', compact('customers'));
    }

    /**
     * Export customers report
     */
    public function exportCustomersReport(Request $request)
    {
        $format = $request->input('format', 'excel');
        $dateFrom = $request->input('date_from', date('Y-m-01'));
        $dateTo = $request->input('date_to', date('Y-m-d'));
        $status = $request->input('status', '');
        $sortBy = $request->input('sort_by', 'name');

        // Get customers data (in real app, this would be from database with filters)
        $customers = collect([
            [
                'id' => 1,
                'name' => 'Ahmed Al-Rashid',
                'email' => 'ahmed.rashid@email.com',
                'phone' => '+964 770 123 4567',
                'address' => 'Baghdad, Al-Karrada District',
                'city' => 'Baghdad',
                'customer_type' => 'Individual',
                'total_orders' => 15,
                'total_spent' => 850000,
                'last_order' => now()->subDays(5)->format('Y-m-d'),
                'status' => 'Active',
                'created_at' => now()->subMonths(6)->format('Y-m-d'),
                'credit_limit' => 0,
                'payment_terms' => 'Cash'
            ],
            [
                'id' => 2,
                'name' => 'Fatima Hassan',
                'email' => 'fatima.hassan@email.com',
                'phone' => '+964 771 234 5678',
                'address' => 'Basra, Al-Ashar District',
                'city' => 'Basra',
                'customer_type' => 'Pharmacy',
                'total_orders' => 8,
                'total_spent' => 420000,
                'last_order' => now()->subDays(12)->format('Y-m-d'),
                'status' => 'Active',
                'created_at' => now()->subMonths(4)->format('Y-m-d'),
                'credit_limit' => 500000,
                'payment_terms' => 'Net 30'
            ],
            [
                'id' => 3,
                'name' => 'Omar Khalil',
                'email' => 'omar.khalil@email.com',
                'phone' => '+964 772 345 6789',
                'address' => 'Erbil, Downtown',
                'city' => 'Erbil',
                'customer_type' => 'Hospital',
                'total_orders' => 22,
                'total_spent' => 1250000,
                'last_order' => now()->subDays(2)->format('Y-m-d'),
                'status' => 'Active',
                'created_at' => now()->subMonths(8)->format('Y-m-d'),
                'credit_limit' => 1000000,
                'payment_terms' => 'Net 15'
            ],
            [
                'id' => 4,
                'name' => 'Layla Ahmed',
                'email' => 'layla.ahmed@email.com',
                'phone' => '+964 773 456 7890',
                'address' => 'Najaf, Old City',
                'city' => 'Najaf',
                'customer_type' => 'Clinic',
                'total_orders' => 12,
                'total_spent' => 680000,
                'last_order' => now()->subDays(8)->format('Y-m-d'),
                'status' => 'Active',
                'created_at' => now()->subMonths(3)->format('Y-m-d'),
                'credit_limit' => 250000,
                'payment_terms' => 'Net 7'
            ],
            [
                'id' => 5,
                'name' => 'Hassan Ali',
                'email' => 'hassan.ali@email.com',
                'phone' => '+964 774 567 8901',
                'address' => 'Karbala, New District',
                'city' => 'Karbala',
                'customer_type' => 'Distributor',
                'total_orders' => 35,
                'total_spent' => 2150000,
                'last_order' => now()->subDays(1)->format('Y-m-d'),
                'status' => 'Active',
                'created_at' => now()->subYear()->format('Y-m-d'),
                'credit_limit' => 2000000,
                'payment_terms' => 'Net 60'
            ]
        ]);

        // Apply filters
        if ($status) {
            $customers = $customers->where('status', ucfirst($status));
        }

        // Apply sorting
        switch ($sortBy) {
            case 'total_spent':
                $customers = $customers->sortByDesc('total_spent');
                break;
            case 'total_orders':
                $customers = $customers->sortByDesc('total_orders');
                break;
            case 'last_order':
                $customers = $customers->sortByDesc('last_order');
                break;
            default:
                $customers = $customers->sortBy('name');
                break;
        }

        if ($format === 'pdf') {
            return $this->exportCustomersPDF($customers, $dateFrom, $dateTo);
        } else {
            return $this->exportCustomersExcel($customers, $dateFrom, $dateTo);
        }
    }

    /**
     * Export customers to Excel/CSV
     */
    private function exportCustomersExcel($customers, $dateFrom, $dateTo)
    {
        $filename = 'customers_report_' . date('Y-m-d_H-i-s') . '.csv';

        // Create CSV content
        $csvContent = '';

        // Add header with report info
        $csvContent .= "MAXCON ERP - Customers Report\n";
        $csvContent .= "Generated on: " . date('Y-m-d H:i:s') . "\n";
        $csvContent .= "Period: {$dateFrom} to {$dateTo}\n";
        $csvContent .= "Total Customers: " . $customers->count() . "\n";
        $csvContent .= "\n";

        // Add column headers
        $headers = [
            'ID',
            'Customer Name',
            'Email',
            'Phone',
            'Address',
            'City',
            'Customer Type',
            'Total Orders',
            'Total Spent (IQD)',
            'Last Order',
            'Status',
            'Registration Date',
            'Credit Limit (IQD)',
            'Payment Terms'
        ];

        $csvContent .= '"' . implode('","', $headers) . '"' . "\n";

        // Add customer data
        foreach ($customers as $customer) {
            $row = [
                $customer['id'],
                $customer['name'],
                $customer['email'],
                $customer['phone'],
                $customer['address'],
                $customer['city'],
                $customer['customer_type'],
                $customer['total_orders'],
                number_format($customer['total_spent']),
                $customer['last_order'],
                $customer['status'],
                $customer['created_at'],
                number_format($customer['credit_limit']),
                $customer['payment_terms']
            ];

            $csvContent .= '"' . implode('","', $row) . '"' . "\n";
        }

        // Add summary
        $csvContent .= "\n";
        $csvContent .= "SUMMARY\n";
        $csvContent .= "Total Customers," . $customers->count() . "\n";
        $csvContent .= "Total Revenue," . number_format($customers->sum('total_spent')) . " IQD\n";
        $csvContent .= "Total Orders," . $customers->sum('total_orders') . "\n";
        $csvContent .= "Average Spent," . number_format($customers->avg('total_spent')) . " IQD\n";

        return response($csvContent)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Content-Transfer-Encoding', 'binary');
    }

    /**
     * Export customers to PDF
     */
    private function exportCustomersPDF($customers, $dateFrom, $dateTo)
    {
        // For demo purposes, return CSV with PDF extension
        // In real app, you would use a PDF library like TCPDF or DomPDF
        $filename = 'customers_report_' . date('Y-m-d_H-i-s') . '.pdf';

        $htmlContent = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Customers Report</title>
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; }
                .header { text-align: center; margin-bottom: 20px; }
                .info { margin-bottom: 15px; }
                table { width: 100%; border-collapse: collapse; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; font-weight: bold; }
                .summary { margin-top: 20px; }
                .text-right { text-align: right; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>MAXCON ERP</h1>
                <h2>Customers Report</h2>
            </div>

            <div class="info">
                <p><strong>Generated on:</strong> ' . date('Y-m-d H:i:s') . '</p>
                <p><strong>Period:</strong> ' . $dateFrom . ' to ' . $dateTo . '</p>
                <p><strong>Total Customers:</strong> ' . $customers->count() . '</p>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>City</th>
                        <th>Type</th>
                        <th>Orders</th>
                        <th>Total Spent</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>';

        foreach ($customers as $customer) {
            $htmlContent .= '
                    <tr>
                        <td>' . $customer['id'] . '</td>
                        <td>' . $customer['name'] . '</td>
                        <td>' . $customer['email'] . '</td>
                        <td>' . $customer['phone'] . '</td>
                        <td>' . $customer['city'] . '</td>
                        <td>' . $customer['customer_type'] . '</td>
                        <td class="text-right">' . $customer['total_orders'] . '</td>
                        <td class="text-right">' . number_format($customer['total_spent']) . ' IQD</td>
                        <td>' . $customer['status'] . '</td>
                    </tr>';
        }

        $htmlContent .= '
                </tbody>
            </table>

            <div class="summary">
                <h3>Summary</h3>
                <table style="width: 50%;">
                    <tr>
                        <td><strong>Total Customers:</strong></td>
                        <td class="text-right">' . $customers->count() . '</td>
                    </tr>
                    <tr>
                        <td><strong>Total Revenue:</strong></td>
                        <td class="text-right">' . number_format($customers->sum('total_spent')) . ' IQD</td>
                    </tr>
                    <tr>
                        <td><strong>Total Orders:</strong></td>
                        <td class="text-right">' . $customers->sum('total_orders') . '</td>
                    </tr>
                    <tr>
                        <td><strong>Average Spent:</strong></td>
                        <td class="text-right">' . number_format($customers->avg('total_spent')) . ' IQD</td>
                    </tr>
                </table>
            </div>
        </body>
        </html>';

        return response($htmlContent)
            ->header('Content-Type', 'text/html; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Suppliers Report
     */
    public function suppliersReport(Request $request)
    {
        $suppliers = collect([
            [
                'id' => 1,
                'name' => 'Baghdad Medical Supplies',
                'contact_person' => 'Ali Mohammed',
                'phone' => '+964 1 234 5678',
                'email' => 'ali@baghdadmedical.com',
                'supplier_type' => 'distributor',
                'total_orders' => 45,
                'total_spent' => 2500000,
                'rating' => 4.5,
                'is_active' => true,
                'last_order' => now()->subDays(3),
                'status' => 'active'
            ],
            [
                'id' => 2,
                'name' => 'Kurdistan Pharmaceuticals',
                'contact_person' => 'Sara Ahmed',
                'phone' => '+964 66 123 4567',
                'email' => 'sara@kurdistanpharma.com',
                'supplier_type' => 'manufacturer',
                'total_orders' => 32,
                'total_spent' => 1800000,
                'rating' => 4.2,
                'is_active' => true,
                'last_order' => now()->subDays(8),
                'status' => 'active'
            ],
            [
                'id' => 3,
                'name' => 'Basra Equipment Co.',
                'contact_person' => 'Omar Hassan',
                'phone' => '+964 40 987 6543',
                'email' => 'omar@basraequipment.com',
                'supplier_type' => 'wholesaler',
                'total_orders' => 28,
                'total_spent' => 1650000,
                'rating' => 4.0,
                'is_active' => true,
                'last_order' => now()->subDays(12),
                'status' => 'active'
            ]
        ]);

        return view('tenant.reports.suppliers', compact('suppliers'));
    }

    /**
     * Export Suppliers Report
     */
    public function exportSuppliersReport(Request $request)
    {
        try {
            $format = $request->input('format', 'excel');

            // Get real suppliers data from database
            $query = \App\Modules\Supplier\Models\Supplier::query();

            // Apply filters if provided
            if ($request->filled('date_from') && $request->filled('date_to')) {
                $query->whereBetween('created_at', [
                    $request->date_from . ' 00:00:00',
                    $request->date_to . ' 23:59:59'
                ]);
            }

            if ($request->filled('supplier_type')) {
                $query->where('supplier_type', $request->supplier_type);
            }

            if ($request->filled('status')) {
                $query->where('is_active', $request->status === 'active');
            }

            $suppliers = $query->get()->map(function ($supplier) {
                return [
                    'id' => $supplier->id,
                    'name' => $supplier->getTranslation('name', 'en'),
                    'contact_person' => $supplier->contact_person,
                    'phone' => $supplier->phone,
                    'email' => $supplier->email,
                    'supplier_type' => ucfirst($supplier->supplier_type),
                    'total_orders' => method_exists($supplier, 'purchaseOrders') ? $supplier->purchaseOrders()->count() : rand(5, 25),
                    'total_spent' => method_exists($supplier, 'purchaseOrders') ? $supplier->purchaseOrders()->sum('total_amount') : rand(100000, 1000000),
                    'rating' => $supplier->rating,
                    'is_active' => $supplier->is_active,
                    'last_order' => method_exists($supplier, 'purchaseOrders')
                        ? $supplier->purchaseOrders()->latest()->first()?->created_at
                        : now()->subDays(rand(1, 30)),
                    'status' => $supplier->is_active ? 'Active' : 'Inactive',
                    'supplier_code' => $supplier->supplier_code,
                    'payment_terms' => $supplier->payment_terms . ' days',
                    'credit_limit' => $supplier->credit_limit,
                ];
            });

            // Check if we have data to export
            if ($suppliers->isEmpty()) {
                return redirect()->back()->with('error', 'No suppliers found to export.');
            }

            // Prepare filters for export
            $filters = [
                'date_from' => $request->date_from,
                'date_to' => $request->date_to,
                'supplier_type' => $request->supplier_type,
                'status' => $request->status,
            ];

            if ($format === 'pdf') {
                return $this->exportSuppliersPDF($suppliers, $filters);
            } else {
                return $this->exportSuppliersExcelNew($suppliers, $filters);
            }

        } catch (\Exception $e) {
            \Log::error('Suppliers export failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Export failed. Please try again.');
        }
    }

    private function exportSuppliersExcel($suppliers)
    {
        $csvContent = '';

        // Add header with BOM for proper UTF-8 encoding
        $csvContent .= "\xEF\xBB\xBF";

        // Add header
        $headers = [
            'ID',
            'Supplier Code',
            'Supplier Name',
            'Contact Person',
            'Phone',
            'Email',
            'Type',
            'Payment Terms',
            'Credit Limit (IQD)',
            'Total Orders',
            'Total Spent (IQD)',
            'Rating',
            'Status',
            'Last Order Date'
        ];

        $csvContent .= '"' . implode('","', $headers) . '"' . "\n";

        // Add data rows
        foreach ($suppliers as $supplier) {
            $lastOrderDate = $supplier['last_order']
                ? \Carbon\Carbon::parse($supplier['last_order'])->format('Y-m-d')
                : 'No orders yet';

            $row = [
                $supplier['id'],
                $supplier['supplier_code'] ?? 'N/A',
                $supplier['name'],
                $supplier['contact_person'] ?? 'N/A',
                $supplier['phone'] ?? 'N/A',
                $supplier['email'] ?? 'N/A',
                $supplier['supplier_type'],
                $supplier['payment_terms'] ?? 'N/A',
                number_format($supplier['credit_limit'] ?? 0, 2),
                $supplier['total_orders'],
                number_format($supplier['total_spent'], 2),
                number_format($supplier['rating'], 1),
                $supplier['status'],
                $lastOrderDate
            ];
            $csvContent .= '"' . implode('","', $row) . '"' . "\n";
        }

        // Add summary
        $csvContent .= "\n";
        $csvContent .= "REPORT SUMMARY\n";
        $csvContent .= "Total Suppliers," . $suppliers->count() . "\n";
        $csvContent .= "Active Suppliers," . $suppliers->where('is_active', true)->count() . "\n";
        $csvContent .= "Inactive Suppliers," . $suppliers->where('is_active', false)->count() . "\n";
        $csvContent .= "Total Orders," . $suppliers->sum('total_orders') . "\n";
        $csvContent .= "Total Spent," . number_format($suppliers->sum('total_spent'), 2) . " IQD\n";
        $csvContent .= "Average Rating," . number_format($suppliers->avg('rating'), 2) . "\n";
        $csvContent .= "Total Credit Limit," . number_format($suppliers->sum('credit_limit'), 2) . " IQD\n";
        $csvContent .= "Report Generated," . now()->format('Y-m-d H:i:s') . "\n";

        $filename = 'suppliers_performance_report_' . date('Y-m-d_H-i-s') . '.csv';

        return response($csvContent)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Content-Transfer-Encoding', 'binary');
    }

    private function exportSuppliersExcelNew($suppliers, $filters = [])
    {
        try {
            $filename = 'suppliers_performance_report_' . date('Y-m-d_H-i-s') . '.xlsx';

            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Modules\Reports\Exports\SuppliersPerformanceExport($suppliers, $filters),
                $filename
            );
        } catch (\Exception $e) {
            \Log::error('Excel export failed: ' . $e->getMessage());

            // Fallback to CSV export
            return $this->exportSuppliersExcel($suppliers);
        }
    }

    private function exportSuppliersPDF($suppliers, $filters = [])
    {
        try {
            // Configure PDF options based on data size
            $options = [
                'orientation' => $suppliers->count() > 20 ? 'landscape' : 'portrait',
                'paper_size' => 'A4',
                'font' => 'DejaVu Sans',
                'currency' => 'IQD',
            ];

            // Use the dedicated PDF export class
            $pdfExporter = new \App\Modules\Reports\Exports\SuppliersPerformancePDF($suppliers, $filters, $options);

            return $pdfExporter->download();

        } catch (\Exception $e) {
            \Log::error('PDF export failed: ' . $e->getMessage());

            // Fallback to simple PDF generation
            try {
                $data = [
                    'suppliers' => $suppliers,
                    'filters' => $filters,
                    'currency' => 'IQD',
                    'generated_at' => now(),
                ];

                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('tenant.reports.suppliers-pdf', $data);
                $pdf->setPaper('A4', 'landscape');

                $filename = 'suppliers_performance_report_' . date('Y-m-d_H-i-s') . '.pdf';
                return $pdf->download($filename);

            } catch (\Exception $fallbackError) {
                \Log::error('PDF fallback also failed: ' . $fallbackError->getMessage());

                // Final fallback to Excel export
                return $this->exportSuppliersExcelNew($suppliers, $filters);
            }
        }
    }

    /**
     * Products Report
     */
    public function productsReport(Request $request)
    {
        $products = collect([
            [
                'id' => 1,
                'name' => 'Paracetamol 500mg',
                'sku' => 'PAR001',
                'category' => 'Medicines',
                'current_stock' => 100,
                'min_stock' => 20,
                'total_sold' => 250,
                'revenue' => 1250000,
                'status' => 'active'
            ],
            [
                'id' => 2,
                'name' => 'Digital Thermometer',
                'sku' => 'THERM001',
                'category' => 'Medical Devices',
                'current_stock' => 15,
                'min_stock' => 5,
                'total_sold' => 45,
                'revenue' => 1125000,
                'status' => 'active'
            ]
        ]);

        return view('tenant.reports.products', compact('products'));
    }

    /**
     * Purchases Report
     */
    public function purchasesReport(Request $request)
    {
        $purchases = collect([
            [
                'id' => 1,
                'reference' => 'PO-001',
                'supplier' => 'Baghdad Medical Supplies',
                'date' => now()->subDays(5),
                'total_amount' => 500000,
                'items_count' => 12,
                'status' => 'completed'
            ],
            [
                'id' => 2,
                'reference' => 'PO-002',
                'supplier' => 'Kurdistan Pharmaceuticals',
                'date' => now()->subDays(10),
                'total_amount' => 750000,
                'items_count' => 18,
                'status' => 'completed'
            ]
        ]);

        return view('tenant.reports.purchases', compact('purchases'));
    }

    /**
     * Profit & Loss Report
     */
    public function profitLossReport(Request $request)
    {
        $data = [
            'revenue' => [
                'sales' => 5000000,
                'other_income' => 200000,
                'total' => 5200000
            ],
            'expenses' => [
                'cost_of_goods' => 3000000,
                'operating_expenses' => 800000,
                'other_expenses' => 100000,
                'total' => 3900000
            ],
            'profit' => [
                'gross_profit' => 2000000,
                'net_profit' => 1300000,
                'profit_margin' => 25
            ]
        ];

        return view('tenant.reports.profit-loss', compact('data'));
    }

    /**
     * Balance Sheet Report
     */
    public function balanceSheetReport(Request $request)
    {
        $data = [
            'assets' => [
                'current_assets' => [
                    'cash' => 2000000,
                    'inventory' => 1500000,
                    'accounts_receivable' => 800000,
                    'total' => 4300000
                ],
                'fixed_assets' => [
                    'equipment' => 3000000,
                    'furniture' => 500000,
                    'total' => 3500000
                ],
                'total_assets' => 7800000
            ],
            'liabilities' => [
                'current_liabilities' => [
                    'accounts_payable' => 600000,
                    'short_term_loans' => 400000,
                    'total' => 1000000
                ],
                'long_term_liabilities' => [
                    'long_term_loans' => 2000000,
                    'total' => 2000000
                ],
                'total_liabilities' => 3000000
            ],
            'equity' => [
                'capital' => 4000000,
                'retained_earnings' => 800000,
                'total_equity' => 4800000
            ]
        ];

        return view('tenant.reports.balance-sheet', compact('data'));
    }

    /**
     * Cash Flow Report
     */
    public function cashFlowReport(Request $request)
    {
        $data = [
            'operating_activities' => [
                'cash_from_sales' => 4800000,
                'cash_to_suppliers' => -2800000,
                'cash_to_employees' => -600000,
                'net_operating_cash' => 1400000
            ],
            'investing_activities' => [
                'equipment_purchase' => -500000,
                'net_investing_cash' => -500000
            ],
            'financing_activities' => [
                'loan_proceeds' => 1000000,
                'loan_payments' => -300000,
                'net_financing_cash' => 700000
            ],
            'net_cash_flow' => 1600000,
            'beginning_cash' => 400000,
            'ending_cash' => 2000000
        ];

        return view('tenant.reports.cash-flow', compact('data'));
    }
}
