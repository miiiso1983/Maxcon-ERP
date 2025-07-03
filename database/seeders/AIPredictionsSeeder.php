<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Modules\AI\Models\Prediction;
use App\Modules\Inventory\Models\Product;
use App\Modules\Customer\Models\Customer;
use App\Models\User;

class AIPredictionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();
        $products = Product::take(5)->get();
        $customers = Customer::take(5)->get();

        if (!$user || $products->isEmpty()) {
            $this->command->warn('No users or products found. Please run other seeders first.');
            return;
        }

        // Create sample demand forecasts
        foreach ($products as $product) {
            Prediction::create([
                'name' => ['en' => "Demand Forecast for {$product->name}", 'ar' => "توقع الطلب لـ {$product->name}", 'ku' => "پێشبینی داواکاری بۆ {$product->name}"],
                'description' => ['en' => '30-day demand forecasting using exponential smoothing', 'ar' => 'توقع الطلب لمدة 30 يوماً باستخدام التنعيم الأسي', 'ku' => 'پێشبینی داواکاری 30 ڕۆژە بە بەکارهێنانی نەرمکردنی ئەکسپۆنێنشیاڵ'],
                'prediction_type' => Prediction::TYPE_DEMAND_FORECAST,
                'model_type' => Prediction::MODEL_EXPONENTIAL_SMOOTHING,
                'target_entity_type' => Product::class,
                'target_entity_id' => $product->id,
                'input_data' => json_encode([
                    'historical_data' => $this->generateSampleHistoricalData(),
                    'forecast_days' => 30,
                    'data_points' => 60,
                ]),
                'prediction_result' => json_encode([
                    'forecast' => $this->generateSampleForecast(),
                    'total_forecast' => rand(100, 500),
                    'daily_average' => rand(5, 20),
                    'trend' => rand(-10, 10) / 100,
                    'method' => 'exponential_smoothing',
                ]),
                'confidence_score' => rand(60, 95) / 100,
                'status' => Prediction::STATUS_COMPLETED,
                'prediction_date' => now()->addDays(30),
                'created_by' => $user->id,
                'model_parameters' => json_encode(['alpha' => 0.3]),
                'training_data_period' => 365,
            ]);
        }

        // Create sample price optimizations
        foreach ($products->take(3) as $product) {
            Prediction::create([
                'name' => ['en' => "Price Optimization for {$product->name}", 'ar' => "تحسين السعر لـ {$product->name}", 'ku' => "باشترکردنی نرخ بۆ {$product->name}"],
                'description' => ['en' => 'AI-powered price optimization for revenue maximization', 'ar' => 'تحسين الأسعار بالذكاء الاصطناعي لتعظيم الإيرادات', 'ku' => 'باشترکردنی نرخ بە زیرەکی دەستکرد بۆ زیادکردنی داهات'],
                'prediction_type' => Prediction::TYPE_PRICE_OPTIMIZATION,
                'model_type' => Prediction::MODEL_LINEAR_REGRESSION,
                'target_entity_type' => Product::class,
                'target_entity_id' => $product->id,
                'input_data' => json_encode([
                    'current_price' => $product->selling_price,
                    'cost_price' => $product->cost_price,
                    'sales_data_points' => 45,
                    'elasticity' => rand(-200, -50) / 100,
                ]),
                'prediction_result' => json_encode([
                    'current_price' => $product->selling_price,
                    'optimal_price' => $product->selling_price * (1 + rand(-20, 20) / 100),
                    'price_change_percent' => rand(-20, 20),
                    'predicted_revenue' => rand(10000, 50000),
                    'revenue_impact_percent' => rand(-10, 25),
                    'profit_margin_percent' => rand(15, 40),
                    'elasticity' => rand(-200, -50) / 100,
                    'optimization_objective' => 'revenue',
                ]),
                'confidence_score' => rand(65, 90) / 100,
                'status' => Prediction::STATUS_COMPLETED,
                'prediction_date' => now()->addDays(30),
                'created_by' => $user->id,
                'model_parameters' => json_encode(['elasticity_method' => 'linear_regression']),
            ]);
        }

        // Create sample customer behavior analyses
        foreach ($customers as $customer) {
            Prediction::create([
                'name' => ['en' => "Behavior Analysis for {$customer->name}", 'ar' => "تحليل السلوك لـ {$customer->name}", 'ku' => "شیکردنەوەی ڕەفتار بۆ {$customer->name}"],
                'description' => ['en' => 'Customer behavior analysis and segmentation', 'ar' => 'تحليل سلوك العملاء والتقسيم', 'ku' => 'شیکردنەوەی ڕەفتاری کڕیار و دابەشکردن'],
                'prediction_type' => Prediction::TYPE_CUSTOMER_BEHAVIOR,
                'model_type' => Prediction::MODEL_LINEAR_REGRESSION,
                'target_entity_type' => Customer::class,
                'target_entity_id' => $customer->id,
                'input_data' => json_encode([
                    'transaction_count' => rand(5, 25),
                    'analysis_period' => 365,
                    'customer_age_days' => rand(30, 1000),
                ]),
                'prediction_result' => json_encode([
                    'rfm_analysis' => [
                        'recency_days' => rand(1, 180),
                        'frequency_count' => rand(1, 20),
                        'monetary_value' => rand(500, 5000),
                        'rfm_score' => rand(2, 5),
                    ],
                    'segmentation' => [
                        'segment' => $this->getRandomSegment(),
                        'confidence' => rand(70, 95) / 100,
                    ],
                    'lifetime_value' => [
                        'predicted_lifetime_value' => rand(2000, 10000),
                        'predicted_annual_value' => rand(1000, 3000),
                    ],
                ]),
                'confidence_score' => rand(70, 90) / 100,
                'status' => Prediction::STATUS_COMPLETED,
                'prediction_date' => now()->addDays(30),
                'created_by' => $user->id,
                'model_parameters' => json_encode(['rfm_weights' => ['recency' => 0.3, 'frequency' => 0.3, 'monetary' => 0.4]]),
            ]);
        }

        // Create sample churn predictions
        foreach ($customers->take(3) as $customer) {
            $churnProbability = rand(10, 80) / 100;
            Prediction::create([
                'name' => ['en' => "Churn Risk for {$customer->name}", 'ar' => "مخاطر فقدان العميل {$customer->name}", 'ku' => "مەترسی لەدەستدانی کڕیار {$customer->name}"],
                'description' => ['en' => 'Customer churn risk prediction', 'ar' => 'توقع مخاطر فقدان العميل', 'ku' => 'پێشبینی مەترسی لەدەستدانی کڕیار'],
                'prediction_type' => Prediction::TYPE_CHURN_PREDICTION,
                'model_type' => Prediction::MODEL_LINEAR_REGRESSION,
                'target_entity_type' => Customer::class,
                'target_entity_id' => $customer->id,
                'input_data' => json_encode([
                    'rfm_analysis' => [
                        'recency_days' => rand(30, 200),
                        'frequency_count' => rand(1, 15),
                        'monetary_value' => rand(200, 3000),
                    ],
                    'transaction_count' => rand(3, 20),
                ]),
                'prediction_result' => json_encode([
                    'churn_probability' => $churnProbability,
                    'risk_level' => $this->getRiskLevel($churnProbability),
                    'days_since_last_purchase' => rand(30, 200),
                    'risk_factors' => $this->getRandomRiskFactors(),
                    'recommended_actions' => $this->getRecommendedActions($churnProbability),
                ]),
                'confidence_score' => rand(75, 95) / 100,
                'status' => Prediction::STATUS_COMPLETED,
                'prediction_date' => now()->addDays(30),
                'created_by' => $user->id,
            ]);
        }

        $this->command->info('AI predictions seeded successfully!');
    }

    private function generateSampleHistoricalData(): array
    {
        $data = [];
        for ($i = 30; $i >= 0; $i--) {
            $data[] = [
                'date' => now()->subDays($i)->format('Y-m-d'),
                'value' => rand(5, 25),
            ];
        }
        return $data;
    }

    private function generateSampleForecast(): array
    {
        $forecast = [];
        for ($i = 1; $i <= 30; $i++) {
            $forecast[] = [
                'date' => now()->addDays($i)->format('Y-m-d'),
                'value' => rand(8, 22),
                'method' => 'exponential_smoothing',
            ];
        }
        return $forecast;
    }

    private function getRandomSegment(): string
    {
        $segments = ['Champions', 'Loyal Customers', 'Potential Loyalists', 'New Customers', 'At Risk', 'Others'];
        return $segments[array_rand($segments)];
    }

    private function getRiskLevel(float $probability): string
    {
        if ($probability >= 0.8) return 'critical';
        if ($probability >= 0.6) return 'high';
        if ($probability >= 0.4) return 'medium';
        if ($probability >= 0.2) return 'low';
        return 'minimal';
    }

    private function getRandomRiskFactors(): array
    {
        $factors = [
            'Long time since last purchase',
            'Low purchase frequency',
            'Low total spending',
            'No recent activity',
            'Declining order values',
        ];

        return array_slice($factors, 0, rand(1, 3));
    }

    private function getRecommendedActions(float $probability): array
    {
        if ($probability >= 0.8) {
            return [
                'Immediate personal outreach',
                'Exclusive discount offer',
                'Account manager assignment',
            ];
        } elseif ($probability >= 0.6) {
            return [
                'Targeted re-engagement campaign',
                'Special promotion',
                'Product recommendations',
            ];
        } else {
            return [
                'Regular check-in email',
                'Loyalty program invitation',
            ];
        }
    }
}
