<?php

namespace App\Modules\AI\Services;

use App\Modules\AI\Models\Prediction;
use App\Modules\Inventory\Models\Product;
use App\Modules\Sales\Models\Sale;
use App\Modules\Sales\Models\SaleItem;
use App\Modules\Customer\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PriceOptimizationService
{
    private array $config;

    public function __construct()
    {
        $this->config = [
            'min_sales_data' => 10,
            'price_change_limit' => 0.3, // Maximum 30% price change
            'elasticity_threshold' => 0.1,
            'profit_margin_min' => 0.1, // Minimum 10% profit margin
        ];
    }

    public function optimizePrice(Product $product, array $options = []): Prediction
    {
        // Get historical sales and pricing data
        $salesData = $this->getSalesData($product, 90); // Last 3 months
        
        if (count($salesData) < $this->config['min_sales_data']) {
            throw new \Exception('Insufficient sales data for price optimization');
        }

        // Calculate price elasticity
        $elasticity = $this->calculatePriceElasticity($salesData);
        
        // Analyze competitor pricing (simulated)
        $competitorAnalysis = $this->analyzeCompetitorPricing($product);
        
        // Calculate optimal price
        $optimization = $this->calculateOptimalPrice($product, $salesData, $elasticity, $competitorAnalysis, $options);
        
        // Calculate confidence score
        $confidence = $this->calculateOptimizationConfidence($salesData, $elasticity, $optimization);
        
        // Create prediction record
        $prediction = Prediction::create([
            'name' => ['en' => "Price Optimization for {$product->getTranslation('name', 'en')}", 'ar' => "تحسين السعر لـ {$product->getTranslation('name', 'ar')}", 'ku' => "باشترکردنی نرخ بۆ {$product->getTranslation('name', 'ku')}"],
            'description' => ['en' => 'AI-powered price optimization analysis', 'ar' => 'تحليل تحسين الأسعار بالذكاء الاصطناعي', 'ku' => 'شیکردنەوەی باشترکردنی نرخ بە زیرەکی دەستکرد'],
            'prediction_type' => Prediction::TYPE_PRICE_OPTIMIZATION,
            'model_type' => Prediction::MODEL_LINEAR_REGRESSION,
            'target_entity_type' => Product::class,
            'target_entity_id' => $product->id,
            'input_data' => [
                'current_price' => $product->selling_price,
                'cost_price' => $product->cost_price,
                'sales_data_points' => count($salesData),
                'elasticity' => $elasticity,
                'competitor_analysis' => $competitorAnalysis,
            ],
            'prediction_result' => $optimization,
            'confidence_score' => $confidence,
            'status' => Prediction::STATUS_COMPLETED,
            'prediction_date' => now()->addDays(30),
            'created_by' => Auth::id() ?? 1,
            'model_parameters' => [
                'elasticity_method' => 'linear_regression',
                'optimization_objective' => $options['objective'] ?? 'revenue',
            ],
        ]);

        return $prediction;
    }

    private function getSalesData(Product $product, int $days): array
    {
        $startDate = now()->subDays($days);
        
        return SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sale_items.product_id', $product->id)
            ->where('sales.sale_date', '>=', $startDate)
            ->select(
                'sales.sale_date',
                'sale_items.unit_price',
                'sale_items.quantity',
                'sale_items.total_price'
            )
            ->orderBy('sales.sale_date')
            ->get()
            ->toArray();
    }

    private function calculatePriceElasticity(array $salesData): float
    {
        if (count($salesData) < 2) {
            return -1.0; // Default elasticity
        }

        // Group data by price points
        $priceGroups = [];
        foreach ($salesData as $sale) {
            $price = round($sale['unit_price'], 2);
            if (!isset($priceGroups[$price])) {
                $priceGroups[$price] = ['quantity' => 0, 'count' => 0];
            }
            $priceGroups[$price]['quantity'] += $sale['quantity'];
            $priceGroups[$price]['count']++;
        }

        // Calculate average quantity for each price
        $pricePoints = [];
        foreach ($priceGroups as $price => $data) {
            $pricePoints[] = [
                'price' => $price,
                'avg_quantity' => $data['quantity'] / $data['count'],
            ];
        }

        if (count($pricePoints) < 2) {
            return -1.0; // Default elasticity
        }

        // Sort by price
        usort($pricePoints, fn($a, $b) => $a['price'] <=> $b['price']);

        // Calculate elasticity using linear regression
        $prices = array_column($pricePoints, 'price');
        $quantities = array_column($pricePoints, 'avg_quantity');
        
        return $this->calculateElasticityCoefficient($prices, $quantities);
    }

    private function calculateElasticityCoefficient(array $prices, array $quantities): float
    {
        $n = count($prices);
        if ($n < 2) return -1.0;

        // Convert to log scale for elasticity calculation
        $logPrices = array_map('log', array_filter($prices, fn($p) => $p > 0));
        $logQuantities = array_map('log', array_filter($quantities, fn($q) => $q > 0));
        
        if (count($logPrices) < 2 || count($logQuantities) < 2) {
            return -1.0;
        }

        // Linear regression on log-log scale
        $n = min(count($logPrices), count($logQuantities));
        $sumX = array_sum(array_slice($logPrices, 0, $n));
        $sumY = array_sum(array_slice($logQuantities, 0, $n));
        $sumXY = 0;
        $sumXX = 0;
        
        for ($i = 0; $i < $n; $i++) {
            $sumXY += $logPrices[$i] * $logQuantities[$i];
            $sumXX += $logPrices[$i] * $logPrices[$i];
        }
        
        $elasticity = ($n * $sumXY - $sumX * $sumY) / ($n * $sumXX - $sumX * $sumX);
        
        // Ensure elasticity is negative (normal goods)
        return min(-0.1, $elasticity);
    }

    private function analyzeCompetitorPricing(Product $product): array
    {
        // Simulated competitor analysis
        // In a real implementation, this would integrate with competitor price monitoring APIs
        
        $basePrice = $product->selling_price;
        
        return [
            'competitor_count' => rand(3, 8),
            'min_price' => $basePrice * (0.8 + rand(0, 20) / 100),
            'max_price' => $basePrice * (1.2 + rand(0, 30) / 100),
            'avg_price' => $basePrice * (0.95 + rand(0, 10) / 100),
            'market_position' => $this->determineMarketPosition($basePrice, $basePrice * 0.95),
        ];
    }

    private function determineMarketPosition(float $ourPrice, float $avgCompetitorPrice): string
    {
        $ratio = $ourPrice / $avgCompetitorPrice;
        
        if ($ratio > 1.1) {
            return 'premium';
        } elseif ($ratio < 0.9) {
            return 'discount';
        } else {
            return 'competitive';
        }
    }

    private function calculateOptimalPrice(Product $product, array $salesData, float $elasticity, array $competitorAnalysis, array $options): array
    {
        $currentPrice = $product->selling_price;
        $costPrice = $product->cost_price;
        $objective = $options['objective'] ?? 'revenue';
        
        // Calculate current metrics
        $currentQuantity = $this->calculateAverageQuantity($salesData);
        $currentRevenue = $currentPrice * $currentQuantity;
        $currentProfit = ($currentPrice - $costPrice) * $currentQuantity;
        
        // Test different price points
        $priceTests = [];
        $minPrice = max($costPrice * (1 + $this->config['profit_margin_min']), $currentPrice * (1 - $this->config['price_change_limit']));
        $maxPrice = min($competitorAnalysis['max_price'], $currentPrice * (1 + $this->config['price_change_limit']));
        
        $priceStep = ($maxPrice - $minPrice) / 20; // Test 20 price points
        
        for ($testPrice = $minPrice; $testPrice <= $maxPrice; $testPrice += $priceStep) {
            $predictedQuantity = $this->predictQuantityAtPrice($currentPrice, $currentQuantity, $testPrice, $elasticity);
            $predictedRevenue = $testPrice * $predictedQuantity;
            $predictedProfit = ($testPrice - $costPrice) * $predictedQuantity;
            
            $priceTests[] = [
                'price' => round($testPrice, 2),
                'quantity' => round($predictedQuantity, 2),
                'revenue' => round($predictedRevenue, 2),
                'profit' => round($predictedProfit, 2),
                'margin' => round((($testPrice - $costPrice) / $testPrice) * 100, 2),
            ];
        }

        // Find optimal price based on objective
        $optimalTest = $this->findOptimalPriceTest($priceTests, $objective);
        
        // Calculate price change impact
        $priceChange = (($optimalTest['price'] - $currentPrice) / $currentPrice) * 100;
        $revenueImpact = (($optimalTest['revenue'] - $currentRevenue) / $currentRevenue) * 100;
        $profitImpact = $currentProfit > 0 ? (($optimalTest['profit'] - $currentProfit) / $currentProfit) * 100 : 0;

        return [
            'current_price' => round($currentPrice, 2),
            'optimal_price' => $optimalTest['price'],
            'price_change_percent' => round($priceChange, 2),
            'predicted_quantity' => $optimalTest['quantity'],
            'predicted_revenue' => $optimalTest['revenue'],
            'predicted_profit' => $optimalTest['profit'],
            'revenue_impact_percent' => round($revenueImpact, 2),
            'profit_impact_percent' => round($profitImpact, 2),
            'profit_margin_percent' => $optimalTest['margin'],
            'elasticity' => round($elasticity, 3),
            'market_position' => $competitorAnalysis['market_position'],
            'competitor_avg_price' => round($competitorAnalysis['avg_price'], 2),
            'optimization_objective' => $objective,
            'price_tests' => array_slice($priceTests, 0, 10), // Top 10 tests
        ];
    }

    private function calculateAverageQuantity(array $salesData): float
    {
        if (empty($salesData)) return 0;
        
        $totalQuantity = array_sum(array_column($salesData, 'quantity'));
        $uniqueDays = count(array_unique(array_column($salesData, 'sale_date')));
        
        return $uniqueDays > 0 ? $totalQuantity / $uniqueDays : 0;
    }

    private function predictQuantityAtPrice(float $currentPrice, float $currentQuantity, float $newPrice, float $elasticity): float
    {
        if ($currentPrice <= 0 || $currentQuantity <= 0) return 0;
        
        $priceChangePercent = ($newPrice - $currentPrice) / $currentPrice;
        $quantityChangePercent = $elasticity * $priceChangePercent;
        
        return max(0, $currentQuantity * (1 + $quantityChangePercent));
    }

    private function findOptimalPriceTest(array $priceTests, string $objective): array
    {
        if (empty($priceTests)) {
            throw new \Exception('No valid price tests generated');
        }

        switch ($objective) {
            case 'profit':
                return collect($priceTests)->sortByDesc('profit')->first();
            
            case 'volume':
                return collect($priceTests)->sortByDesc('quantity')->first();
            
            case 'margin':
                return collect($priceTests)->sortByDesc('margin')->first();
            
            case 'revenue':
            default:
                return collect($priceTests)->sortByDesc('revenue')->first();
        }
    }

    private function calculateOptimizationConfidence(array $salesData, float $elasticity, array $optimization): float
    {
        $confidence = 0.5; // Base confidence
        
        // Data quality factor
        $dataPoints = count($salesData);
        $dataQuality = min(1, $dataPoints / 30); // Prefer 30+ data points
        $confidence += $dataQuality * 0.3;
        
        // Elasticity reliability
        if (abs($elasticity) > 0.1 && abs($elasticity) < 5) {
            $confidence += 0.2; // Reasonable elasticity
        }
        
        // Price change reasonableness
        $priceChange = abs($optimization['price_change_percent']);
        if ($priceChange < 20) {
            $confidence += 0.2; // Conservative price change
        }
        
        // Market position consistency
        if (isset($optimization['market_position']) && $optimization['market_position'] === 'competitive') {
            $confidence += 0.1;
        }

        return min(1, max(0, $confidence));
    }

    public function optimizeMultipleProducts(array $productIds, array $options = []): array
    {
        $optimizations = [];
        
        foreach ($productIds as $productId) {
            $product = Product::find($productId);
            if ($product) {
                try {
                    $optimization = $this->optimizePrice($product, $options);
                    $optimizations[] = [
                        'product' => $product,
                        'optimization' => $optimization,
                        'success' => true,
                    ];
                } catch (\Exception $e) {
                    $optimizations[] = [
                        'product' => $product,
                        'error' => $e->getMessage(),
                        'success' => false,
                    ];
                }
            }
        }

        return $optimizations;
    }

    public function getPricingRecommendations(Product $product, Prediction $optimization): array
    {
        $result = $optimization->prediction_result;
        $recommendations = [];
        
        // Price change recommendation
        $priceChange = (float) ($result['price_change_percent'] ?? 0);
        if (abs($priceChange) > 5) {
            $action = $priceChange > 0 ? 'increase' : 'decrease';
            $recommendations[] = [
                'type' => 'price_change',
                'priority' => abs($priceChange) > 15 ? 'high' : 'medium',
                'action' => $action,
                'message' => "Consider {$action}ing price by " . number_format(abs($priceChange), 1) . "%",
                'new_price' => $result['optimal_price'] ?? 0,
            ];
        }

        // Revenue impact
        $revenueImpact = $result['revenue_impact_percent'] ?? 0;
        if ($revenueImpact > 10) {
            $recommendations[] = [
                'type' => 'revenue_opportunity',
                'priority' => 'high',
                'message' => "Potential revenue increase of " . number_format($revenueImpact, 1) . "%",
                'impact' => $revenueImpact,
            ];
        }

        // Market position
        $marketPosition = $result['market_position'] ?? '';
        if ($marketPosition === 'premium') {
            $recommendations[] = [
                'type' => 'market_position',
                'priority' => 'medium',
                'message' => 'Product is priced at premium level - ensure value justification',
            ];
        } elseif ($marketPosition === 'discount') {
            $recommendations[] = [
                'type' => 'market_position',
                'priority' => 'medium',
                'message' => 'Product is priced below market average - consider value-based pricing',
            ];
        }

        return $recommendations;
    }

    public function analyzeElasticityTrends(Product $product, int $days = 180): array
    {
        $salesData = $this->getSalesData($product, $days);
        
        // Split data into periods
        $periodLength = 30; // 30-day periods
        $periods = [];
        
        for ($i = 0; $i < $days; $i += $periodLength) {
            $periodStart = now()->subDays($days - $i);
            $periodEnd = $periodStart->copy()->addDays($periodLength);
            
            $periodData = array_filter($salesData, function($sale) use ($periodStart, $periodEnd) {
                $saleDate = Carbon::parse($sale['sale_date']);
                return $saleDate >= $periodStart && $saleDate < $periodEnd;
            });
            
            if (count($periodData) >= 3) {
                $elasticity = $this->calculatePriceElasticity($periodData);
                $periods[] = [
                    'period' => $periodStart->format('Y-m-d') . ' to ' . $periodEnd->format('Y-m-d'),
                    'elasticity' => round($elasticity, 3),
                    'data_points' => count($periodData),
                ];
            }
        }

        return [
            'periods' => $periods,
            'trend' => $this->calculateElasticityTrend($periods),
            'average_elasticity' => count($periods) > 0 ? array_sum(array_column($periods, 'elasticity')) / count($periods) : 0,
        ];
    }

    private function calculateElasticityTrend(array $periods): string
    {
        if (count($periods) < 2) return 'insufficient_data';
        
        $elasticities = array_column($periods, 'elasticity');
        $first = array_slice($elasticities, 0, ceil(count($elasticities) / 2));
        $last = array_slice($elasticities, floor(count($elasticities) / 2));
        
        $firstAvg = array_sum($first) / count($first);
        $lastAvg = array_sum($last) / count($last);
        
        $change = $lastAvg - $firstAvg;
        
        if ($change > 0.1) {
            return 'becoming_more_elastic';
        } elseif ($change < -0.1) {
            return 'becoming_less_elastic';
        } else {
            return 'stable';
        }
    }
}
