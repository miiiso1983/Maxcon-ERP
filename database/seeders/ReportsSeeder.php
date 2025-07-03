<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Modules\Reports\Models\Report;
use App\Modules\Reports\Models\Dashboard;
use App\Models\User;

class ReportsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();

        if (!$user) {
            $this->command->warn('No users found. Please run user seeders first.');
            return;
        }

        // Create default reports
        $defaultReports = Report::getDefaultReports();

        foreach ($defaultReports as $reportData) {
            Report::create(array_merge($reportData, [
                'created_by' => $user->id,
            ]));
        }

        // Create additional business reports
        $businessReports = [
            [
                'name' => ['en' => 'Monthly Sales Report', 'ar' => 'تقرير المبيعات الشهرية', 'ku' => 'ڕاپۆرتی فرۆشتنی مانگانە'],
                'description' => ['en' => 'Comprehensive monthly sales analysis', 'ar' => 'تحليل شامل للمبيعات الشهرية', 'ku' => 'شیکردنەوەی تەواوی فرۆشتنی مانگانە'],
                'report_type' => Report::TYPE_SALES,
                'category' => Report::CATEGORY_OPERATIONAL,
                'query_config' => [
                    'model' => 'App\\Modules\\Sales\\Models\\Sale',
                    'select' => ['sale_date', 'total_amount', 'payment_status', 'customer_id'],
                    'with' => ['customer', 'items.product'],
                ],
                'chart_config' => [
                    'type' => Report::CHART_BAR,
                    'x_field' => 'sale_date',
                    'y_field' => 'total_amount',
                ],
                'filters' => [
                    ['field' => 'sale_date', 'operator' => 'date_range', 'parameter' => 'date_range'],
                    ['field' => 'payment_status', 'operator' => '=', 'parameter' => 'payment_status'],
                    ['field' => 'customer_id', 'operator' => '=', 'parameter' => 'customer_id'],
                ],
                'is_public' => true,
                'created_by' => $user->id,
            ],
            [
                'name' => ['en' => 'Top Customers Report', 'ar' => 'تقرير أفضل العملاء', 'ku' => 'ڕاپۆرتی باشترین کڕیاران'],
                'description' => ['en' => 'Analysis of top performing customers', 'ar' => 'تحليل أفضل العملاء أداءً', 'ku' => 'شیکردنەوەی باشترین کڕیارە کارامەکان'],
                'report_type' => Report::TYPE_CUSTOMER,
                'category' => Report::CATEGORY_ANALYTICAL,
                'query_config' => [
                    'model' => 'App\\Modules\\Customer\\Models\\Customer',
                    'select' => ['name', 'customer_code', 'email', 'phone'],
                    'with' => ['sales'],
                ],
                'chart_config' => [
                    'type' => Report::CHART_PIE,
                    'x_field' => 'name',
                    'y_field' => 'total_sales',
                ],
                'filters' => [
                    ['field' => 'created_at', 'operator' => 'date_range', 'parameter' => 'registration_date'],
                    ['field' => 'city', 'operator' => '=', 'parameter' => 'city'],
                ],
                'is_public' => true,
                'created_by' => $user->id,
            ],
            [
                'name' => ['en' => 'Product Performance', 'ar' => 'أداء المنتجات', 'ku' => 'کارایی بەرهەمەکان'],
                'description' => ['en' => 'Product sales and inventory analysis', 'ar' => 'تحليل مبيعات ومخزون المنتجات', 'ku' => 'شیکردنەوەی فرۆشتن و کۆگای بەرهەمەکان'],
                'report_type' => Report::TYPE_INVENTORY,
                'category' => Report::CATEGORY_ANALYTICAL,
                'query_config' => [
                    'model' => 'App\\Modules\\Inventory\\Models\\Product',
                    'select' => ['name', 'sku', 'current_stock', 'minimum_stock', 'selling_price', 'cost_price'],
                    'with' => ['category', 'supplier'],
                ],
                'chart_config' => [
                    'type' => Report::CHART_BAR,
                    'x_field' => 'name',
                    'y_field' => 'current_stock',
                ],
                'filters' => [
                    ['field' => 'category_id', 'operator' => '=', 'parameter' => 'category_id'],
                    ['field' => 'current_stock', 'operator' => '<=', 'parameter' => 'stock_threshold'],
                    ['field' => 'is_active', 'operator' => '=', 'parameter' => 'is_active'],
                ],
                'is_public' => true,
                'created_by' => $user->id,
            ],
            [
                'name' => ['en' => 'Financial Summary', 'ar' => 'الملخص المالي', 'ku' => 'کورتەی دارایی'],
                'description' => ['en' => 'Overall financial performance overview', 'ar' => 'نظرة عامة على الأداء المالي العام', 'ku' => 'تێڕوانینی گشتی کارایی دارایی'],
                'report_type' => Report::TYPE_FINANCIAL,
                'category' => Report::CATEGORY_FINANCIAL,
                'query_config' => [
                    'model' => 'App\\Modules\\Financial\\Models\\Account',
                    'select' => ['account_code', 'account_name', 'account_type', 'current_balance'],
                ],
                'chart_config' => [
                    'type' => Report::CHART_PIE,
                    'x_field' => 'account_type',
                    'y_field' => 'current_balance',
                ],
                'filters' => [
                    ['field' => 'account_type', 'operator' => '=', 'parameter' => 'account_type'],
                    ['field' => 'is_active', 'operator' => '=', 'parameter' => 'is_active'],
                ],
                'is_public' => true,
                'created_by' => $user->id,
            ],
        ];

        foreach ($businessReports as $reportData) {
            Report::create($reportData);
        }

        // Create default dashboard
        $defaultDashboard = Dashboard::getDefaultDashboard();
        Dashboard::create(array_merge($defaultDashboard, [
            'created_by' => $user->id,
        ]));

        // Create additional dashboards
        $additionalDashboards = [
            [
                'name' => ['en' => 'Sales Dashboard', 'ar' => 'لوحة قيادة المبيعات', 'ku' => 'داشبۆردی فرۆشتن'],
                'description' => ['en' => 'Sales performance monitoring', 'ar' => 'مراقبة أداء المبيعات', 'ku' => 'چاودێری کارایی فرۆشتن'],
                'layout_config' => [
                    'columns' => 12,
                    'rows' => 8,
                    'gap' => 16,
                ],
                'widgets' => [
                    [
                        'type' => 'metric',
                        'title' => ['en' => 'Daily Sales', 'ar' => 'المبيعات اليومية', 'ku' => 'فرۆشتنی ڕۆژانە'],
                        'position' => ['x' => 0, 'y' => 0, 'w' => 4, 'h' => 2],
                        'config' => ['metric' => 'daily_sales', 'format' => 'currency'],
                    ],
                    [
                        'type' => 'chart',
                        'title' => ['en' => 'Sales by Product', 'ar' => 'المبيعات حسب المنتج', 'ku' => 'فرۆشتن بەپێی بەرهەم'],
                        'position' => ['x' => 4, 'y' => 0, 'w' => 8, 'h' => 4],
                        'config' => ['chart_type' => 'bar', 'data_source' => 'product_sales'],
                    ],
                ],
                'is_public' => true,
                'created_by' => $user->id,
                'refresh_interval' => 300,
            ],
            [
                'name' => ['en' => 'Inventory Dashboard', 'ar' => 'لوحة قيادة المخزون', 'ku' => 'داشبۆردی کۆگا'],
                'description' => ['en' => 'Inventory levels and alerts', 'ar' => 'مستويات المخزون والتنبيهات', 'ku' => 'ئاستی کۆگا و ئاگادارکردنەوەکان'],
                'layout_config' => [
                    'columns' => 12,
                    'rows' => 6,
                    'gap' => 16,
                ],
                'widgets' => [
                    [
                        'type' => 'metric',
                        'title' => ['en' => 'Low Stock Items', 'ar' => 'عناصر المخزون المنخفض', 'ku' => 'بڕگەکانی کۆگای کەم'],
                        'position' => ['x' => 0, 'y' => 0, 'w' => 3, 'h' => 2],
                        'config' => ['metric' => 'low_stock_count', 'format' => 'number', 'color' => 'warning'],
                    ],
                    [
                        'type' => 'chart',
                        'title' => ['en' => 'Stock Levels', 'ar' => 'مستويات المخزون', 'ku' => 'ئاستەکانی کۆگا'],
                        'position' => ['x' => 3, 'y' => 0, 'w' => 9, 'h' => 4],
                        'config' => ['chart_type' => 'line', 'data_source' => 'stock_levels'],
                    ],
                ],
                'is_public' => true,
                'created_by' => $user->id,
                'refresh_interval' => 600,
            ],
        ];

        foreach ($additionalDashboards as $dashboardData) {
            Dashboard::create($dashboardData);
        }

        $this->command->info('Reports and dashboards seeded successfully!');
    }
}
