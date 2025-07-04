<?php

namespace App\Modules\Reports\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Sales\Models\Sale;
use App\Modules\Customer\Models\Customer;
use App\Modules\Inventory\Models\Product;
use App\Modules\Financial\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function dashboard()
    {
        $analytics = [
            'sales_analytics' => $this->getSalesAnalytics(),
            'customer_analytics' => $this->getCustomerAnalytics(),
            'product_analytics' => $this->getProductAnalytics(),
            'financial_analytics' => $this->getFinancialAnalytics(),
        ];

        return view('tenant.reports.analytics.dashboard', compact('analytics'));
    }

    public function salesAnalytics(Request $request)
    {
        $period = $request->get('period', '30'); // days
        $startDate = now()->subDays($period)->format('Y-m-d');
        $endDate = now()->format('Y-m-d');

        $analytics = [
            'sales_trend' => $this->getSalesTrend($startDate, $endDate),
            'sales_by_hour' => $this->getSalesByHour($startDate, $endDate),
            'sales_by_day_of_week' => $this->getSalesByDayOfWeek($startDate, $endDate),
            'payment_method_distribution' => $this->getPaymentMethodDistribution($startDate, $endDate),
            'average_order_value' => $this->getAverageOrderValue($startDate, $endDate),
            'conversion_metrics' => $this->getConversionMetrics($startDate, $endDate),
            'seasonal_patterns' => $this->getSeasonalPatterns(),
        ];

        return view('tenant.reports.analytics.sales', compact('analytics', 'period'));
    }

    public function customerAnalytics(Request $request)
    {
        $period = $request->get('period', '90'); // days
        $startDate = now()->subDays($period)->format('Y-m-d');
        $endDate = now()->format('Y-m-d');

        $analytics = [
            'customer_segmentation' => $this->getCustomerSegmentation(),
            'customer_lifetime_value' => $this->getCustomerLifetimeValue(),
            'customer_acquisition' => $this->getCustomerAcquisition($startDate, $endDate),
            'customer_retention' => $this->getCustomerRetention(),
            'purchase_frequency' => $this->getPurchaseFrequency($startDate, $endDate),
            'geographic_distribution' => $this->getGeographicDistribution(),
        ];

        return view('tenant.reports.analytics.customers', compact('analytics', 'period'));
    }

    public function productAnalytics(Request $request)
    {
        $period = $request->get('period', '30'); // days
        $startDate = now()->subDays($period)->format('Y-m-d');
        $endDate = now()->format('Y-m-d');

        $analytics = [
            'product_performance' => $this->getProductPerformance($startDate, $endDate),
            'category_analysis' => $this->getCategoryAnalysis($startDate, $endDate),
            'inventory_turnover' => $this->getInventoryTurnover($startDate, $endDate),
            'price_elasticity' => $this->getPriceElasticity($startDate, $endDate),
            'cross_selling' => $this->getCrossSellingAnalysis($startDate, $endDate),
            'stock_optimization' => $this->getStockOptimization(),
        ];

        return view('tenant.reports.analytics.products', compact('analytics', 'period'));
    }

    public function profitabilityAnalysis(Request $request)
    {
        $period = $request->get('period', '30'); // days
        $startDate = now()->subDays($period)->format('Y-m-d');
        $endDate = now()->format('Y-m-d');

        $analytics = [
            'gross_margin_analysis' => $this->getGrossMarginAnalysis($startDate, $endDate),
            'profit_by_product' => $this->getProfitByProduct($startDate, $endDate),
            'profit_by_customer' => $this->getProfitByCustomer($startDate, $endDate),
            'cost_analysis' => $this->getCostAnalysis($startDate, $endDate),
            'break_even_analysis' => $this->getBreakEvenAnalysis($startDate, $endDate),
        ];

        return view('tenant.reports.analytics.profitability', compact('analytics', 'period'));
    }

    private function getSalesAnalytics(): array
    {
        $today = now()->format('Y-m-d');
        $yesterday = now()->subDay()->format('Y-m-d');
        $thisMonth = now()->startOfMonth()->format('Y-m-d');
        $lastMonth = now()->subMonth()->startOfMonth()->format('Y-m-d');

        return [
            'today_sales' => Sale::whereDate('sale_date', $today)->sum('total_amount'),
            'yesterday_sales' => Sale::whereDate('sale_date', $yesterday)->sum('total_amount'),
            'month_sales' => Sale::where('sale_date', '>=', $thisMonth)->sum('total_amount'),
            'last_month_sales' => Sale::whereBetween('sale_date', [$lastMonth, $thisMonth])->sum('total_amount'),
            'growth_rate' => $this->calculateGrowthRate('sales', 30),
        ];
    }

    private function getCustomerAnalytics(): array
    {
        return [
            'total_customers' => Customer::count(),
            'active_customers' => Customer::whereHas('sales', function ($query) {
                $query->where('sale_date', '>=', now()->subDays(30));
            })->count(),
            'new_customers_this_month' => Customer::where('created_at', '>=', now()->startOfMonth())->count(),
            'average_customer_value' => $this->getAverageCustomerValue(),
        ];
    }

    private function getProductAnalytics(): array
    {
        return [
            'total_products' => Product::count(),
            'active_products' => Product::whereHas('saleItems', function ($query) {
                $query->whereHas('sale', function ($q) {
                    $q->where('sale_date', '>=', now()->subDays(30));
                });
            })->count(),
            'low_stock_products' => Product::whereHas('stocks', function ($q) {
                $q->whereRaw('quantity <= (SELECT reorder_level FROM products WHERE products.id = stocks.product_id)');
            })->count(),
            'top_selling_product' => $this->getTopSellingProduct(),
        ];
    }

    private function getFinancialAnalytics(): array
    {
        $revenue = Account::revenue()->sum('current_balance');
        $expenses = Account::expenses()->sum('current_balance');

        return [
            'total_revenue' => $revenue,
            'total_expenses' => $expenses,
            'net_profit' => $revenue - $expenses,
            'profit_margin' => $revenue > 0 ? (($revenue - $expenses) / $revenue) * 100 : 0,
        ];
    }

    private function getSalesTrend(string $startDate, string $endDate): array
    {
        return Sale::whereBetween('sale_date', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(sale_date) as date'),
                DB::raw('SUM(total_amount) as total_sales'),
                DB::raw('COUNT(*) as transaction_count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();
    }

    private function getSalesByHour(string $startDate, string $endDate): array
    {
        return Sale::whereBetween('sale_date', [$startDate, $endDate])
            ->select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('SUM(total_amount) as total_sales'),
                DB::raw('COUNT(*) as transaction_count')
            )
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->toArray();
    }

    private function getSalesByDayOfWeek(string $startDate, string $endDate): array
    {
        return Sale::whereBetween('sale_date', [$startDate, $endDate])
            ->select(
                DB::raw('DAYOFWEEK(sale_date) as day_of_week'),
                DB::raw('SUM(total_amount) as total_sales'),
                DB::raw('COUNT(*) as transaction_count')
            )
            ->groupBy('day_of_week')
            ->orderBy('day_of_week')
            ->get()
            ->map(function ($item) {
                $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                $item['day_name'] = $days[$item['day_of_week'] - 1];
                return $item;
            })
            ->toArray();
    }

    private function getPaymentMethodDistribution(string $startDate, string $endDate): array
    {
        return Sale::whereBetween('sale_date', [$startDate, $endDate])
            ->select(
                'payment_method',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total_amount) as total_amount')
            )
            ->groupBy('payment_method')
            ->get()
            ->toArray();
    }

    private function getCustomerSegmentation(): array
    {
        // RFM Analysis: Recency, Frequency, Monetary
        $customers = Customer::with('sales')
            ->get()
            ->map(function ($customer) {
                $sales = $customer->sales;
                $lastSale = $sales->max('sale_date');
                $recency = $lastSale ? Carbon::parse($lastSale)->diffInDays(now()) : 999;
                $frequency = $sales->count();
                $monetary = $sales->sum('total_amount');

                return [
                    'customer_id' => $customer->id,
                    'customer_name' => $customer->name,
                    'recency' => $recency,
                    'frequency' => $frequency,
                    'monetary' => $monetary,
                    'segment' => $this->determineCustomerSegment($recency, $frequency, $monetary),
                ];
            });

        return $customers->groupBy('segment')->map->count()->toArray();
    }

    private function determineCustomerSegment(int $recency, int $frequency, float $monetary): string
    {
        if ($recency <= 30 && $frequency >= 5 && $monetary >= 1000) {
            return 'Champions';
        } elseif ($recency <= 60 && $frequency >= 3 && $monetary >= 500) {
            return 'Loyal Customers';
        } elseif ($recency <= 90 && $frequency >= 2) {
            return 'Potential Loyalists';
        } elseif ($recency <= 30 && $frequency < 2) {
            return 'New Customers';
        } elseif ($recency > 90 && $frequency >= 3) {
            return 'At Risk';
        } elseif ($recency > 180) {
            return 'Lost';
        } else {
            return 'Others';
        }
    }

    private function getProductPerformance(string $startDate, string $endDate): array
    {
        return DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->select(
                'products.id',
                'products.name',
                'products.sku',
                DB::raw('SUM(sale_items.quantity) as total_quantity'),
                DB::raw('SUM(sale_items.total_price) as total_revenue'),
                DB::raw('AVG(sale_items.unit_price) as avg_price'),
                DB::raw('COUNT(DISTINCT sales.id) as transaction_count')
            )
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderBy('total_revenue', 'desc')
            ->take(20)
            ->get()
            ->toArray();
    }

    private function calculateGrowthRate(string $metric, int $days): float
    {
        $currentPeriod = now()->subDays($days)->format('Y-m-d');
        $previousPeriod = now()->subDays($days * 2)->format('Y-m-d');

        $current = Sale::where('sale_date', '>=', $currentPeriod)->sum('total_amount');
        $previous = Sale::whereBetween('sale_date', [$previousPeriod, $currentPeriod])->sum('total_amount');

        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }

        return (($current - $previous) / $previous) * 100;
    }

    private function getAverageCustomerValue(): float
    {
        // Alternative approach using subquery to avoid withSum issues
        $customerValues = Customer::whereHas('sales')
            ->with('sales')
            ->get()
            ->map(function ($customer) {
                return $customer->sales->sum('total_amount');
            })
            ->filter(function ($value) {
                return $value > 0;
            });

        return $customerValues->count() > 0 ? $customerValues->avg() : 0;
    }

    private function getTopSellingProduct(): ?array
    {
        $product = DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->select(
                'products.name',
                DB::raw('SUM(sale_items.quantity) as total_sold')
            )
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_sold', 'desc')
            ->first();

        return $product ? (array) $product : null;
    }

    private function getAverageOrderValue(string $startDate, string $endDate): array
    {
        return Sale::whereBetween('sale_date', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(sale_date) as date'),
                DB::raw('AVG(total_amount) as avg_order_value')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();
    }

    private function getConversionMetrics(string $startDate, string $endDate): array
    {
        // This would require visitor tracking - simplified for demo
        return [
            'total_visitors' => 1000, // Mock data
            'total_sales' => Sale::whereBetween('sale_date', [$startDate, $endDate])->count(),
            'conversion_rate' => 5.2, // Mock percentage
        ];
    }

    private function getSeasonalPatterns(): array
    {
        return Sale::select(
                DB::raw('MONTH(sale_date) as month'),
                DB::raw('SUM(total_amount) as total_sales')
            )
            ->where('sale_date', '>=', now()->subYear())
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(function ($item) {
                $months = [
                    1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                    5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                    9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                ];
                $item['month_name'] = $months[$item['month']];
                return $item;
            })
            ->toArray();
    }

    private function getCustomerLifetimeValue(): array
    {
        return Customer::with('sales')
            ->get()
            ->map(function ($customer) {
                $sales = $customer->sales;
                $totalValue = $sales->sum('total_amount');
                $firstSale = $sales->min('sale_date');
                $lastSale = $sales->max('sale_date');
                
                $lifetimeDays = $firstSale && $lastSale ? 
                    Carbon::parse($firstSale)->diffInDays(Carbon::parse($lastSale)) + 1 : 1;
                
                return [
                    'customer_id' => $customer->id,
                    'customer_name' => $customer->name,
                    'lifetime_value' => $totalValue,
                    'lifetime_days' => $lifetimeDays,
                    'avg_daily_value' => $totalValue / $lifetimeDays,
                ];
            })
            ->sortByDesc('lifetime_value')
            ->take(20)
            ->values()
            ->toArray();
    }

    private function getCustomerAcquisition(string $startDate, string $endDate): array
    {
        return Customer::whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as new_customers')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();
    }

    private function getCustomerRetention(): array
    {
        // Simplified retention calculation
        $totalCustomers = Customer::count();
        $activeCustomers = Customer::whereHas('sales', function ($query) {
            $query->where('sale_date', '>=', now()->subDays(90));
        })->count();

        return [
            'total_customers' => $totalCustomers,
            'active_customers' => $activeCustomers,
            'retention_rate' => $totalCustomers > 0 ? ($activeCustomers / $totalCustomers) * 100 : 0,
        ];
    }

    private function getPurchaseFrequency(string $startDate, string $endDate): array
    {
        return Customer::whereHas('sales', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('sale_date', [$startDate, $endDate]);
            })
            ->withCount(['sales' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('sale_date', [$startDate, $endDate]);
            }])
            ->get()
            ->groupBy('sales_count')
            ->map(function ($customers) {
                return $customers->count();
            })
            ->toArray();
    }

    private function getGeographicDistribution(): array
    {
        return Customer::select(
                'city',
                DB::raw('COUNT(*) as customer_count'),
                DB::raw('SUM((SELECT SUM(total_amount) FROM sales WHERE sales.customer_id = customers.id)) as total_sales')
            )
            ->whereNotNull('city')
            ->groupBy('city')
            ->orderBy('total_sales', 'desc')
            ->take(10)
            ->get()
            ->toArray();
    }

    private function getCategoryAnalysis(string $startDate, string $endDate): array
    {
        return DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->select(
                'categories.name as category_name',
                DB::raw('SUM(sale_items.quantity) as total_quantity'),
                DB::raw('SUM(sale_items.total_price) as total_revenue')
            )
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('total_revenue', 'desc')
            ->get()
            ->toArray();
    }

    private function getInventoryTurnover(string $startDate, string $endDate): array
    {
        return DB::table('products')
            ->leftJoin('sale_items', 'products.id', '=', 'sale_items.product_id')
            ->leftJoin('sales', function ($join) use ($startDate, $endDate) {
                $join->on('sale_items.sale_id', '=', 'sales.id')
                     ->whereBetween('sales.sale_date', [$startDate, $endDate]);
            })
            ->select(
                'products.id',
                'products.name',
                'products.current_stock',
                'products.cost_price',
                DB::raw('COALESCE(SUM(sale_items.quantity), 0) as quantity_sold'),
                DB::raw('CASE WHEN products.current_stock > 0 THEN COALESCE(SUM(sale_items.quantity), 0) / products.current_stock ELSE 0 END as turnover_ratio')
            )
            ->groupBy('products.id', 'products.name', 'products.current_stock', 'products.cost_price')
            ->orderBy('turnover_ratio', 'desc')
            ->take(20)
            ->get()
            ->toArray();
    }

    private function getPriceElasticity(string $startDate, string $endDate): array
    {
        // Simplified price elasticity analysis
        return DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->select(
                'products.name',
                DB::raw('AVG(sale_items.unit_price) as avg_price'),
                DB::raw('SUM(sale_items.quantity) as total_quantity'),
                DB::raw('MIN(sale_items.unit_price) as min_price'),
                DB::raw('MAX(sale_items.unit_price) as max_price')
            )
            ->groupBy('products.id', 'products.name')
            ->having('min_price', '<', DB::raw('max_price'))
            ->orderBy('total_quantity', 'desc')
            ->take(10)
            ->get()
            ->toArray();
    }

    private function getCrossSellingAnalysis(string $startDate, string $endDate): array
    {
        // Find products frequently bought together
        return DB::table('sale_items as si1')
            ->join('sale_items as si2', 'si1.sale_id', '=', 'si2.sale_id')
            ->join('products as p1', 'si1.product_id', '=', 'p1.id')
            ->join('products as p2', 'si2.product_id', '=', 'p2.id')
            ->join('sales', 'si1.sale_id', '=', 'sales.id')
            ->where('si1.product_id', '<', 'si2.product_id')
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->select(
                'p1.name as product1',
                'p2.name as product2',
                DB::raw('COUNT(*) as frequency')
            )
            ->groupBy('si1.product_id', 'si2.product_id', 'p1.name', 'p2.name')
            ->orderBy('frequency', 'desc')
            ->take(10)
            ->get()
            ->toArray();
    }

    private function getStockOptimization(): array
    {
        return Product::select(
                'products.id',
                'products.name',
                'products.reorder_level as minimum_stock',
                'products.cost_price',
                DB::raw('COALESCE(SUM(stocks.quantity), 0) as current_stock'),
                DB::raw('(SELECT COALESCE(SUM(quantity), 0) FROM sale_items WHERE product_id = products.id AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as sold_last_30_days'),
                DB::raw('CASE WHEN COALESCE(SUM(stocks.quantity), 0) > 0 THEN (SELECT COALESCE(SUM(quantity), 0) FROM sale_items WHERE product_id = products.id AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) / COALESCE(SUM(stocks.quantity), 0) ELSE 0 END as velocity')
            )
            ->leftJoin('stocks', 'products.id', '=', 'stocks.product_id')
            ->groupBy('products.id', 'products.name', 'products.reorder_level', 'products.cost_price')
            ->orderBy('velocity', 'desc')
            ->take(20)
            ->get()
            ->toArray();
    }

    private function getGrossMarginAnalysis(string $startDate, string $endDate): array
    {
        return DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->select(
                'products.name',
                DB::raw('SUM(sale_items.total_price) as revenue'),
                DB::raw('SUM(sale_items.quantity * products.cost_price) as cost'),
                DB::raw('SUM(sale_items.total_price) - SUM(sale_items.quantity * products.cost_price) as gross_profit'),
                DB::raw('((SUM(sale_items.total_price) - SUM(sale_items.quantity * products.cost_price)) / SUM(sale_items.total_price)) * 100 as margin_percentage')
            )
            ->groupBy('products.id', 'products.name')
            ->orderBy('gross_profit', 'desc')
            ->take(20)
            ->get()
            ->toArray();
    }

    private function getProfitByProduct(string $startDate, string $endDate): array
    {
        return $this->getGrossMarginAnalysis($startDate, $endDate);
    }

    private function getProfitByCustomer(string $startDate, string $endDate): array
    {
        return DB::table('sales')
            ->join('customers', 'sales.customer_id', '=', 'customers.id')
            ->join('sale_items', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->select(
                'customers.name as customer_name',
                DB::raw('SUM(sale_items.total_price) as revenue'),
                DB::raw('SUM(sale_items.quantity * products.cost_price) as cost'),
                DB::raw('SUM(sale_items.total_price) - SUM(sale_items.quantity * products.cost_price) as profit')
            )
            ->groupBy('customers.id', 'customers.name')
            ->orderBy('profit', 'desc')
            ->take(20)
            ->get()
            ->toArray();
    }

    private function getCostAnalysis(string $startDate, string $endDate): array
    {
        // This would include operational costs, simplified for demo
        return [
            'cost_of_goods_sold' => DB::table('sale_items')
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->join('products', 'sale_items.product_id', '=', 'products.id')
                ->whereBetween('sales.sale_date', [$startDate, $endDate])
                ->sum(DB::raw('sale_items.quantity * products.cost_price')),
            'operating_expenses' => Account::expenses()
                ->whereHas('debitEntries', function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('entry_date', [$startDate, $endDate]);
                })
                ->sum('current_balance'),
        ];
    }

    private function getBreakEvenAnalysis(string $startDate, string $endDate): array
    {
        $revenue = Sale::whereBetween('sale_date', [$startDate, $endDate])->sum('total_amount');
        $variableCosts = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->sum(DB::raw('sale_items.quantity * products.cost_price'));
        
        $fixedCosts = 50000; // This would come from expense accounts - simplified
        
        $contributionMargin = $revenue - $variableCosts;
        $contributionMarginRatio = $revenue > 0 ? $contributionMargin / $revenue : 0;
        $breakEvenPoint = $contributionMarginRatio > 0 ? $fixedCosts / $contributionMarginRatio : 0;

        return [
            'revenue' => $revenue,
            'variable_costs' => $variableCosts,
            'fixed_costs' => $fixedCosts,
            'contribution_margin' => $contributionMargin,
            'contribution_margin_ratio' => $contributionMarginRatio * 100,
            'break_even_point' => $breakEvenPoint,
        ];
    }
}
