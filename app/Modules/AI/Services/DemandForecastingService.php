<?php

namespace App\Modules\AI\Services;

use App\Modules\AI\Models\Prediction;
use App\Modules\Inventory\Models\Product;
use App\Modules\Sales\Models\Sale;
use App\Modules\Sales\Models\SaleItem;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DemandForecastingService
{
    private array $config;

    public function __construct()
    {
        $this->config = [
            'min_data_points' => 10,
            'forecast_periods' => 30,
            'seasonality_periods' => [7, 30, 365], // weekly, monthly, yearly
            'confidence_threshold' => 0.6,
        ];
    }

    public function forecastDemand(Product $product, int $forecastDays = 30, string $modelType = Prediction::MODEL_EXPONENTIAL_SMOOTHING): Prediction
    {
        // Get historical sales data
        $historicalData = $this->getHistoricalSalesData($product, 365); // Last year of data
        
        if (count($historicalData) < $this->config['min_data_points']) {
            throw new \Exception('Insufficient historical data for forecasting');
        }

        // Prepare data for forecasting
        $timeSeries = $this->prepareTimeSeriesData($historicalData);
        
        // Apply forecasting model
        $forecast = $this->applyForecastingModel($timeSeries, $forecastDays, $modelType);
        
        // Calculate confidence score
        $confidence = $this->calculateConfidenceScore($timeSeries, $forecast);
        
        // Create prediction record
        $prediction = Prediction::create([
            'name' => ['en' => "Demand Forecast for {$product->name}", 'ar' => "توقع الطلب لـ {$product->name}", 'ku' => "پێشبینی داواکاری بۆ {$product->name}"],
            'description' => ['en' => "Demand forecasting for next {$forecastDays} days", 'ar' => "توقع الطلب للأيام الـ {$forecastDays} القادمة", 'ku' => "پێشبینی داواکاری بۆ {$forecastDays} ڕۆژی داهاتوو"],
            'prediction_type' => Prediction::TYPE_DEMAND_FORECAST,
            'model_type' => $modelType,
            'target_entity_type' => Product::class,
            'target_entity_id' => $product->id,
            'input_data' => [
                'historical_data' => $timeSeries,
                'forecast_days' => $forecastDays,
                'data_points' => count($historicalData),
            ],
            'prediction_result' => $forecast,
            'confidence_score' => $confidence,
            'status' => Prediction::STATUS_COMPLETED,
            'prediction_date' => now()->addDays($forecastDays),
            'created_by' => auth()->id() ?? 1,
            'model_parameters' => $this->getModelParameters($modelType),
            'training_data_period' => 365,
        ]);

        return $prediction;
    }

    private function getHistoricalSalesData(Product $product, int $days): array
    {
        $startDate = now()->subDays($days);
        
        return SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sale_items.product_id', $product->id)
            ->where('sales.sale_date', '>=', $startDate)
            ->select(
                DB::raw('DATE(sales.sale_date) as date'),
                DB::raw('SUM(sale_items.quantity) as quantity')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();
    }

    private function prepareTimeSeriesData(array $historicalData): array
    {
        $timeSeries = [];
        $dates = [];
        $quantities = [];

        foreach ($historicalData as $data) {
            $dates[] = $data['date'];
            $quantities[] = (float) $data['quantity'];
        }

        // Fill missing dates with zero
        if (!empty($dates)) {
            $startDate = Carbon::parse(min($dates));
            $endDate = Carbon::parse(max($dates));
            
            $current = $startDate->copy();
            while ($current <= $endDate) {
                $dateStr = $current->format('Y-m-d');
                $index = array_search($dateStr, $dates);
                
                $timeSeries[] = [
                    'date' => $dateStr,
                    'value' => $index !== false ? $quantities[$index] : 0,
                ];
                
                $current->addDay();
            }
        }

        return $timeSeries;
    }

    private function applyForecastingModel(array $timeSeries, int $forecastDays, string $modelType): array
    {
        switch ($modelType) {
            case Prediction::MODEL_MOVING_AVERAGE:
                return $this->movingAverageForecasting($timeSeries, $forecastDays);
            
            case Prediction::MODEL_EXPONENTIAL_SMOOTHING:
                return $this->exponentialSmoothingForecasting($timeSeries, $forecastDays);
            
            case Prediction::MODEL_LINEAR_REGRESSION:
                return $this->linearRegressionForecasting($timeSeries, $forecastDays);
            
            default:
                return $this->exponentialSmoothingForecasting($timeSeries, $forecastDays);
        }
    }

    private function movingAverageForecasting(array $timeSeries, int $forecastDays, int $window = 7): array
    {
        $values = array_column($timeSeries, 'value');
        $n = count($values);
        
        if ($n < $window) {
            $window = $n;
        }

        // Calculate moving average for the last window
        $lastValues = array_slice($values, -$window);
        $average = array_sum($lastValues) / count($lastValues);
        
        // Generate forecast
        $forecast = [];
        $lastDate = Carbon::parse(end($timeSeries)['date']);
        
        for ($i = 1; $i <= $forecastDays; $i++) {
            $forecastDate = $lastDate->copy()->addDays($i);
            $forecast[] = [
                'date' => $forecastDate->format('Y-m-d'),
                'value' => round($average, 2),
                'method' => 'moving_average',
            ];
        }

        return [
            'forecast' => $forecast,
            'total_forecast' => round($average * $forecastDays, 2),
            'daily_average' => round($average, 2),
            'method' => 'moving_average',
            'parameters' => ['window' => $window],
        ];
    }

    private function exponentialSmoothingForecasting(array $timeSeries, int $forecastDays, float $alpha = 0.3): array
    {
        $values = array_column($timeSeries, 'value');
        $n = count($values);
        
        if ($n == 0) {
            return $this->getEmptyForecast($forecastDays);
        }

        // Initialize with first value
        $smoothed = [$values[0]];
        
        // Calculate exponentially smoothed values
        for ($i = 1; $i < $n; $i++) {
            $smoothed[] = $alpha * $values[$i] + (1 - $alpha) * $smoothed[$i - 1];
        }

        $lastSmoothed = end($smoothed);
        
        // Detect trend
        $trend = 0;
        if ($n >= 2) {
            $recentValues = array_slice($smoothed, -min(7, $n));
            $trend = $this->calculateTrend($recentValues);
        }

        // Generate forecast with trend
        $forecast = [];
        $lastDate = Carbon::parse(end($timeSeries)['date']);
        
        for ($i = 1; $i <= $forecastDays; $i++) {
            $forecastDate = $lastDate->copy()->addDays($i);
            $trendAdjustment = $trend * $i;
            $forecastValue = max(0, $lastSmoothed + $trendAdjustment);
            
            $forecast[] = [
                'date' => $forecastDate->format('Y-m-d'),
                'value' => round($forecastValue, 2),
                'method' => 'exponential_smoothing',
            ];
        }

        return [
            'forecast' => $forecast,
            'total_forecast' => round(array_sum(array_column($forecast, 'value')), 2),
            'daily_average' => round($lastSmoothed, 2),
            'trend' => round($trend, 4),
            'method' => 'exponential_smoothing',
            'parameters' => ['alpha' => $alpha],
        ];
    }

    private function linearRegressionForecasting(array $timeSeries, int $forecastDays): array
    {
        $values = array_column($timeSeries, 'value');
        $n = count($values);
        
        if ($n < 2) {
            return $this->getEmptyForecast($forecastDays);
        }

        // Prepare data for linear regression
        $x = range(1, $n);
        $y = $values;
        
        // Calculate linear regression coefficients
        $sumX = array_sum($x);
        $sumY = array_sum($y);
        $sumXY = 0;
        $sumXX = 0;
        
        for ($i = 0; $i < $n; $i++) {
            $sumXY += $x[$i] * $y[$i];
            $sumXX += $x[$i] * $x[$i];
        }
        
        $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumXX - $sumX * $sumX);
        $intercept = ($sumY - $slope * $sumX) / $n;
        
        // Generate forecast
        $forecast = [];
        $lastDate = Carbon::parse(end($timeSeries)['date']);
        
        for ($i = 1; $i <= $forecastDays; $i++) {
            $forecastDate = $lastDate->copy()->addDays($i);
            $forecastValue = max(0, $intercept + $slope * ($n + $i));
            
            $forecast[] = [
                'date' => $forecastDate->format('Y-m-d'),
                'value' => round($forecastValue, 2),
                'method' => 'linear_regression',
            ];
        }

        return [
            'forecast' => $forecast,
            'total_forecast' => round(array_sum(array_column($forecast, 'value')), 2),
            'daily_average' => round(($intercept + $slope * ($n + $forecastDays/2)), 2),
            'trend' => round($slope, 4),
            'method' => 'linear_regression',
            'parameters' => ['slope' => $slope, 'intercept' => $intercept],
        ];
    }

    private function calculateTrend(array $values): float
    {
        $n = count($values);
        if ($n < 2) return 0;

        $x = range(1, $n);
        $sumX = array_sum($x);
        $sumY = array_sum($values);
        $sumXY = 0;
        $sumXX = 0;
        
        for ($i = 0; $i < $n; $i++) {
            $sumXY += $x[$i] * $values[$i];
            $sumXX += $x[$i] * $x[$i];
        }
        
        return ($n * $sumXY - $sumX * $sumY) / ($n * $sumXX - $sumX * $sumX);
    }

    private function calculateConfidenceScore(array $timeSeries, array $forecast): float
    {
        $values = array_column($timeSeries, 'value');
        $n = count($values);
        
        if ($n < 3) {
            return 0.5; // Low confidence for insufficient data
        }

        // Calculate variance and stability
        $mean = array_sum($values) / $n;
        $variance = 0;
        
        foreach ($values as $value) {
            $variance += pow($value - $mean, 2);
        }
        $variance /= $n;
        
        // Calculate coefficient of variation
        $cv = $mean > 0 ? sqrt($variance) / $mean : 1;
        
        // Calculate trend consistency
        $trendConsistency = $this->calculateTrendConsistency($values);
        
        // Calculate data completeness
        $dataCompleteness = min(1, $n / 30); // Prefer at least 30 data points
        
        // Combine factors for confidence score
        $stabilityScore = max(0, 1 - $cv);
        $confidence = ($stabilityScore * 0.4 + $trendConsistency * 0.3 + $dataCompleteness * 0.3);
        
        return min(1, max(0, $confidence));
    }

    private function calculateTrendConsistency(array $values): float
    {
        $n = count($values);
        if ($n < 3) return 0.5;

        $trends = [];
        for ($i = 1; $i < $n; $i++) {
            $trends[] = $values[$i] - $values[$i - 1];
        }

        $positiveTrends = count(array_filter($trends, fn($t) => $t > 0));
        $negativeTrends = count(array_filter($trends, fn($t) => $t < 0));
        $totalTrends = count($trends);

        // Higher consistency when trends are more uniform
        $consistency = 1 - abs($positiveTrends - $negativeTrends) / $totalTrends;
        
        return $consistency;
    }

    private function getEmptyForecast(int $forecastDays): array
    {
        return [
            'forecast' => [],
            'total_forecast' => 0,
            'daily_average' => 0,
            'trend' => 0,
            'method' => 'insufficient_data',
            'parameters' => [],
        ];
    }

    private function getModelParameters(string $modelType): array
    {
        return match($modelType) {
            Prediction::MODEL_MOVING_AVERAGE => ['window' => 7],
            Prediction::MODEL_EXPONENTIAL_SMOOTHING => ['alpha' => 0.3],
            Prediction::MODEL_LINEAR_REGRESSION => ['method' => 'least_squares'],
            default => [],
        };
    }

    public function forecastMultipleProducts(array $productIds, int $forecastDays = 30): array
    {
        $forecasts = [];
        
        foreach ($productIds as $productId) {
            $product = Product::find($productId);
            if ($product) {
                try {
                    $forecast = $this->forecastDemand($product, $forecastDays);
                    $forecasts[] = [
                        'product' => $product,
                        'forecast' => $forecast,
                        'success' => true,
                    ];
                } catch (\Exception $e) {
                    $forecasts[] = [
                        'product' => $product,
                        'error' => $e->getMessage(),
                        'success' => false,
                    ];
                }
            }
        }

        return $forecasts;
    }

    public function getInventoryRecommendations(Product $product, Prediction $forecast): array
    {
        $forecastData = $forecast->prediction_result;
        $totalForecast = $forecastData['total_forecast'] ?? 0;
        $currentStock = $product->current_stock;
        $minimumStock = $product->minimum_stock;
        
        $recommendations = [];
        
        // Stock level recommendation
        if ($currentStock < $totalForecast) {
            $shortfall = $totalForecast - $currentStock;
            $recommendations[] = [
                'type' => 'reorder',
                'priority' => 'high',
                'message' => "Reorder {$shortfall} units to meet forecasted demand",
                'quantity' => ceil($shortfall),
            ];
        } elseif ($currentStock > $totalForecast * 2) {
            $excess = $currentStock - ($totalForecast * 1.5);
            $recommendations[] = [
                'type' => 'excess_stock',
                'priority' => 'medium',
                'message' => "Consider reducing stock by {$excess} units",
                'quantity' => floor($excess),
            ];
        }

        // Minimum stock adjustment
        $recommendedMinimum = ceil($totalForecast * 0.2); // 20% of forecast as buffer
        if ($minimumStock < $recommendedMinimum) {
            $recommendations[] = [
                'type' => 'adjust_minimum',
                'priority' => 'medium',
                'message' => "Increase minimum stock level to {$recommendedMinimum}",
                'quantity' => $recommendedMinimum,
            ];
        }

        return $recommendations;
    }
}
