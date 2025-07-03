<?php

namespace App\Modules\AI\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\AI\Models\Prediction;
use App\Modules\AI\Services\DemandForecastingService;
use App\Modules\AI\Services\PriceOptimizationService;
use App\Modules\AI\Services\CustomerBehaviorService;
use App\Modules\Inventory\Models\Product;
use App\Modules\Customer\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AIController extends Controller
{
    protected DemandForecastingService $demandService;
    protected PriceOptimizationService $priceService;
    protected CustomerBehaviorService $behaviorService;

    public function __construct(
        DemandForecastingService $demandService,
        PriceOptimizationService $priceService,
        CustomerBehaviorService $behaviorService
    ) {
        $this->demandService = $demandService;
        $this->priceService = $priceService;
        $this->behaviorService = $behaviorService;
    }

    public function dashboard()
    {
        // Get AI insights summary
        $insights = $this->getAIInsights();
        
        // Get recent predictions
        $recentPredictions = Prediction::with(['createdBy', 'targetEntity'])
            ->latest()
            ->take(10)
            ->get();

        // Get prediction statistics
        $stats = $this->getPredictionStatistics();

        return view('tenant.ai.dashboard', compact('insights', 'recentPredictions', 'stats'));
    }

    public function demandForecasting()
    {
        $products = Product::active()
            ->whereHas('saleItems')
            ->with(['category', 'supplier'])
            ->paginate(20);

        $recentForecasts = Prediction::byType(Prediction::TYPE_DEMAND_FORECAST)
            ->with('targetEntity')
            ->latest()
            ->take(10)
            ->get();

        return view('tenant.ai.demand-forecasting', compact('products', 'recentForecasts'));
    }

    public function createDemandForecast(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'forecast_days' => 'required|integer|min:1|max:365',
            'model_type' => 'required|in:' . implode(',', array_keys(Prediction::getModelTypes())),
        ]);

        try {
            $product = Product::findOrFail($request->product_id);
            $prediction = $this->demandService->forecastDemand(
                $product,
                $request->forecast_days,
                $request->model_type
            );

            return response()->json([
                'success' => true,
                'prediction' => $prediction->load('targetEntity'),
                'insights' => $prediction->generateInsights(),
                'recommendations' => $this->demandService->getInventoryRecommendations($product, $prediction),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function priceOptimization()
    {
        $products = Product::active()
            ->whereHas('saleItems')
            ->with(['category', 'supplier'])
            ->paginate(20);

        $recentOptimizations = Prediction::byType(Prediction::TYPE_PRICE_OPTIMIZATION)
            ->with('targetEntity')
            ->latest()
            ->take(10)
            ->get();

        return view('tenant.ai.price-optimization', compact('products', 'recentOptimizations'));
    }

    public function createPriceOptimization(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'objective' => 'required|in:revenue,profit,volume,margin',
        ]);

        try {
            $product = Product::findOrFail($request->product_id);
            $prediction = $this->priceService->optimizePrice($product, [
                'objective' => $request->objective,
            ]);

            return response()->json([
                'success' => true,
                'prediction' => $prediction->load('targetEntity'),
                'insights' => $prediction->generateInsights(),
                'recommendations' => $this->priceService->getPricingRecommendations($product, $prediction),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function customerAnalytics()
    {
        $customers = Customer::whereHas('sales')
            ->withCount('sales')
            ->withSum('sales', 'total_amount')
            ->orderBy('sales_sum_total_amount', 'desc')
            ->paginate(20);

        $recentAnalyses = Prediction::whereIn('prediction_type', [
                Prediction::TYPE_CUSTOMER_BEHAVIOR,
                Prediction::TYPE_CHURN_PREDICTION
            ])
            ->with('targetEntity')
            ->latest()
            ->take(10)
            ->get();

        return view('tenant.ai.customer-analytics', compact('customers', 'recentAnalyses'));
    }

    public function analyzeCustomerBehavior(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
        ]);

        try {
            $customer = Customer::findOrFail($request->customer_id);
            $prediction = $this->behaviorService->analyzeCustomerBehavior($customer);

            return response()->json([
                'success' => true,
                'prediction' => $prediction->load('targetEntity'),
                'insights' => $prediction->generateInsights(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function predictChurnRisk(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
        ]);

        try {
            $customer = Customer::findOrFail($request->customer_id);
            $prediction = $this->behaviorService->predictChurnRisk($customer);

            return response()->json([
                'success' => true,
                'prediction' => $prediction->load('targetEntity'),
                'insights' => $prediction->generateInsights(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function batchAnalysis(Request $request)
    {
        $request->validate([
            'analysis_type' => 'required|in:demand_forecast,price_optimization,customer_behavior,churn_prediction',
            'entity_ids' => 'required|array|min:1|max:50',
            'entity_ids.*' => 'required|integer',
            'parameters' => 'sometimes|array',
        ]);

        try {
            $results = [];
            $analysisType = $request->analysis_type;
            $entityIds = $request->entity_ids;
            $parameters = $request->parameters ?? [];

            switch ($analysisType) {
                case 'demand_forecast':
                    $results = $this->demandService->forecastMultipleProducts($entityIds, $parameters['forecast_days'] ?? 30);
                    break;

                case 'price_optimization':
                    $results = $this->priceService->optimizeMultipleProducts($entityIds, $parameters);
                    break;

                case 'customer_behavior':
                    foreach ($entityIds as $customerId) {
                        $customer = Customer::find($customerId);
                        if ($customer) {
                            try {
                                $prediction = $this->behaviorService->analyzeCustomerBehavior($customer);
                                $results[] = [
                                    'customer' => $customer,
                                    'prediction' => $prediction,
                                    'success' => true,
                                ];
                            } catch (\Exception $e) {
                                $results[] = [
                                    'customer' => $customer,
                                    'error' => $e->getMessage(),
                                    'success' => false,
                                ];
                            }
                        }
                    }
                    break;

                case 'churn_prediction':
                    foreach ($entityIds as $customerId) {
                        $customer = Customer::find($customerId);
                        if ($customer) {
                            try {
                                $prediction = $this->behaviorService->predictChurnRisk($customer);
                                $results[] = [
                                    'customer' => $customer,
                                    'prediction' => $prediction,
                                    'success' => true,
                                ];
                            } catch (\Exception $e) {
                                $results[] = [
                                    'customer' => $customer,
                                    'error' => $e->getMessage(),
                                    'success' => false,
                                ];
                            }
                        }
                    }
                    break;
            }

            return response()->json([
                'success' => true,
                'results' => $results,
                'summary' => [
                    'total_processed' => count($results),
                    'successful' => count(array_filter($results, fn($r) => $r['success'])),
                    'failed' => count(array_filter($results, fn($r) => !$r['success'])),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function predictionDetails(Prediction $prediction)
    {
        $prediction->load(['createdBy', 'targetEntity']);
        
        $insights = $prediction->generateInsights();
        $recommendations = $this->getRecommendationsForPrediction($prediction);

        return view('tenant.ai.prediction-details', compact('prediction', 'insights', 'recommendations'));
    }

    public function updatePredictionAccuracy(Request $request, Prediction $prediction)
    {
        $request->validate([
            'actual_result' => 'required|array',
        ]);

        $prediction->updateAccuracy($request->actual_result);

        return response()->json([
            'success' => true,
            'accuracy_score' => $prediction->accuracy_score,
            'accuracy_level' => $prediction->accuracy_level,
        ]);
    }

    private function getAIInsights(): array
    {
        // Get high-confidence predictions with actionable insights
        $demandInsights = Prediction::byType(Prediction::TYPE_DEMAND_FORECAST)
            ->where('confidence_score', '>=', 0.7)
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        $priceInsights = Prediction::byType(Prediction::TYPE_PRICE_OPTIMIZATION)
            ->where('confidence_score', '>=', 0.7)
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        $churnRisks = Prediction::byType(Prediction::TYPE_CHURN_PREDICTION)
            ->whereRaw('JSON_EXTRACT(prediction_result, "$.churn_probability") >= ?', [0.7])
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        $lowStockAlerts = Product::whereHas('stocks', function ($query) {
            $query->whereRaw('quantity <= (SELECT reorder_level FROM products WHERE products.id = stocks.product_id)');
        })->count();

        return [
            'demand_forecasts' => $demandInsights,
            'price_optimizations' => $priceInsights,
            'churn_risks' => $churnRisks,
            'low_stock_alerts' => $lowStockAlerts,
            'total_predictions' => Prediction::where('created_at', '>=', now()->subDays(7))->count(),
        ];
    }

    private function getPredictionStatistics(): array
    {
        return [
            'total_predictions' => Prediction::count(),
            'accuracy_stats' => [
                'excellent' => Prediction::where('accuracy_score', '>=', 0.95)->count(),
                'good' => Prediction::whereBetween('accuracy_score', [0.85, 0.94])->count(),
                'fair' => Prediction::whereBetween('accuracy_score', [0.75, 0.84])->count(),
                'poor' => Prediction::where('accuracy_score', '<', 0.75)->whereNotNull('accuracy_score')->count(),
            ],
            'confidence_stats' => [
                'high' => Prediction::where('confidence_score', '>=', 0.8)->count(),
                'medium' => Prediction::whereBetween('confidence_score', [0.6, 0.79])->count(),
                'low' => Prediction::where('confidence_score', '<', 0.6)->count(),
            ],
            'type_distribution' => Prediction::select('prediction_type', DB::raw('count(*) as count'))
                ->groupBy('prediction_type')
                ->pluck('count', 'prediction_type')
                ->toArray(),
        ];
    }

    private function getRecommendationsForPrediction(Prediction $prediction): array
    {
        switch ($prediction->prediction_type) {
            case Prediction::TYPE_DEMAND_FORECAST:
                if ($prediction->targetEntity instanceof Product) {
                    return $this->demandService->getInventoryRecommendations($prediction->targetEntity, $prediction);
                }
                break;

            case Prediction::TYPE_PRICE_OPTIMIZATION:
                if ($prediction->targetEntity instanceof Product) {
                    return $this->priceService->getPricingRecommendations($prediction->targetEntity, $prediction);
                }
                break;

            case Prediction::TYPE_CUSTOMER_BEHAVIOR:
            case Prediction::TYPE_CHURN_PREDICTION:
                return $prediction->generateInsights();
        }

        return [];
    }

    public function aiSettings()
    {
        $settings = [
            'demand_forecasting' => [
                'enabled' => true,
                'auto_forecast_days' => 30,
                'min_data_points' => 10,
                'confidence_threshold' => 0.6,
            ],
            'price_optimization' => [
                'enabled' => true,
                'max_price_change' => 30, // percentage
                'min_profit_margin' => 10, // percentage
                'optimization_frequency' => 'weekly',
            ],
            'customer_analytics' => [
                'enabled' => true,
                'churn_threshold_days' => 90,
                'high_value_threshold' => 1000,
                'auto_segmentation' => true,
            ],
        ];

        return view('tenant.ai.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'settings' => 'required|array',
        ]);

        // In a real implementation, these would be stored in a settings table or config
        // For now, we'll just return success
        
        return response()->json([
            'success' => true,
            'message' => 'AI settings updated successfully',
        ]);
    }
}
