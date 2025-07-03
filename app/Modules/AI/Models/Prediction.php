<?php

namespace App\Modules\AI\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Translatable\HasTranslations;
use App\Models\User;

class Prediction extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'name',
        'description',
        'prediction_type',
        'model_type',
        'target_entity_type',
        'target_entity_id',
        'input_data',
        'prediction_result',
        'confidence_score',
        'accuracy_score',
        'status',
        'prediction_date',
        'actual_result',
        'created_by',
        'model_parameters',
        'training_data_period',
        'meta_data',
    ];

    protected $casts = [
        'input_data' => 'array',
        'prediction_result' => 'array',
        'confidence_score' => 'decimal:4',
        'accuracy_score' => 'decimal:4',
        'prediction_date' => 'date',
        'actual_result' => 'array',
        'model_parameters' => 'array',
        'meta_data' => 'array',
    ];

    public $translatable = ['name', 'description'];

    // Prediction Types
    const TYPE_DEMAND_FORECAST = 'demand_forecast';
    const TYPE_PRICE_OPTIMIZATION = 'price_optimization';
    const TYPE_CUSTOMER_BEHAVIOR = 'customer_behavior';
    const TYPE_INVENTORY_OPTIMIZATION = 'inventory_optimization';
    const TYPE_SALES_FORECAST = 'sales_forecast';
    const TYPE_CHURN_PREDICTION = 'churn_prediction';

    // Model Types
    const MODEL_LINEAR_REGRESSION = 'linear_regression';
    const MODEL_POLYNOMIAL_REGRESSION = 'polynomial_regression';
    const MODEL_MOVING_AVERAGE = 'moving_average';
    const MODEL_EXPONENTIAL_SMOOTHING = 'exponential_smoothing';
    const MODEL_ARIMA = 'arima';
    const MODEL_NEURAL_NETWORK = 'neural_network';

    // Status
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    // Relationships
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function targetEntity()
    {
        return $this->morphTo('target_entity', 'target_entity_type', 'target_entity_id');
    }

    // Scopes
    public function scopeByType($query, string $type)
    {
        return $query->where('prediction_type', $type);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    // Accessors
    public function getTypeColorAttribute(): string
    {
        return match($this->prediction_type) {
            self::TYPE_DEMAND_FORECAST => 'primary',
            self::TYPE_PRICE_OPTIMIZATION => 'success',
            self::TYPE_CUSTOMER_BEHAVIOR => 'info',
            self::TYPE_INVENTORY_OPTIMIZATION => 'warning',
            self::TYPE_SALES_FORECAST => 'secondary',
            self::TYPE_CHURN_PREDICTION => 'danger',
            default => 'light',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'secondary',
            self::STATUS_PROCESSING => 'info',
            self::STATUS_COMPLETED => 'success',
            self::STATUS_FAILED => 'danger',
            default => 'light',
        };
    }

    public function getConfidenceLevelAttribute(): string
    {
        if ($this->confidence_score >= 0.9) {
            return 'Very High';
        } elseif ($this->confidence_score >= 0.8) {
            return 'High';
        } elseif ($this->confidence_score >= 0.7) {
            return 'Medium';
        } elseif ($this->confidence_score >= 0.6) {
            return 'Low';
        } else {
            return 'Very Low';
        }
    }

    public function getAccuracyLevelAttribute(): string
    {
        if (!$this->accuracy_score) {
            return 'Not Available';
        }

        if ($this->accuracy_score >= 0.95) {
            return 'Excellent';
        } elseif ($this->accuracy_score >= 0.85) {
            return 'Good';
        } elseif ($this->accuracy_score >= 0.75) {
            return 'Fair';
        } else {
            return 'Poor';
        }
    }

    // Methods
    public function updateAccuracy($actualResult): void
    {
        $this->actual_result = $actualResult;
        
        // Calculate accuracy based on prediction type
        $accuracy = $this->calculateAccuracy($actualResult);
        $this->accuracy_score = $accuracy;
        $this->save();
    }

    private function calculateAccuracy($actualResult): float
    {
        $predicted = $this->prediction_result;
        
        switch ($this->prediction_type) {
            case self::TYPE_DEMAND_FORECAST:
            case self::TYPE_SALES_FORECAST:
                return $this->calculateForecastAccuracy($predicted, $actualResult);
            
            case self::TYPE_PRICE_OPTIMIZATION:
                return $this->calculatePriceOptimizationAccuracy($predicted, $actualResult);
            
            case self::TYPE_CUSTOMER_BEHAVIOR:
            case self::TYPE_CHURN_PREDICTION:
                return $this->calculateClassificationAccuracy($predicted, $actualResult);
            
            default:
                return 0.0;
        }
    }

    private function calculateForecastAccuracy($predicted, $actual): float
    {
        if (!isset($predicted['value']) || !isset($actual['value'])) {
            return 0.0;
        }

        $predictedValue = (float) $predicted['value'];
        $actualValue = (float) $actual['value'];

        if ($actualValue == 0) {
            return $predictedValue == 0 ? 1.0 : 0.0;
        }

        $error = abs($predictedValue - $actualValue) / $actualValue;
        return max(0, 1 - $error);
    }

    private function calculatePriceOptimizationAccuracy($predicted, $actual): float
    {
        if (!isset($predicted['optimal_price']) || !isset($actual['actual_sales'])) {
            return 0.0;
        }

        $predictedSales = $predicted['predicted_sales'] ?? 0;
        $actualSales = $actual['actual_sales'] ?? 0;

        if ($actualSales == 0) {
            return $predictedSales == 0 ? 1.0 : 0.0;
        }

        $error = abs($predictedSales - $actualSales) / $actualSales;
        return max(0, 1 - $error);
    }

    private function calculateClassificationAccuracy($predicted, $actual): float
    {
        if (!isset($predicted['classification']) || !isset($actual['classification'])) {
            return 0.0;
        }

        return $predicted['classification'] === $actual['classification'] ? 1.0 : 0.0;
    }

    public function generateInsights(): array
    {
        $insights = [];
        
        switch ($this->prediction_type) {
            case self::TYPE_DEMAND_FORECAST:
                $insights = $this->generateDemandInsights();
                break;
            
            case self::TYPE_PRICE_OPTIMIZATION:
                $insights = $this->generatePriceInsights();
                break;
            
            case self::TYPE_CUSTOMER_BEHAVIOR:
                $insights = $this->generateCustomerInsights();
                break;
            
            case self::TYPE_CHURN_PREDICTION:
                $insights = $this->generateChurnInsights();
                break;
        }

        return $insights;
    }

    private function generateDemandInsights(): array
    {
        $result = $this->prediction_result;
        $insights = [];

        if (isset($result['trend'])) {
            $trend = $result['trend'];
            if ($trend > 0.1) {
                $insights[] = [
                    'type' => 'positive',
                    'message' => 'Strong upward demand trend detected. Consider increasing inventory.',
                    'priority' => 'high'
                ];
            } elseif ($trend < -0.1) {
                $insights[] = [
                    'type' => 'warning',
                    'message' => 'Declining demand trend. Review pricing and marketing strategies.',
                    'priority' => 'medium'
                ];
            }
        }

        if (isset($result['seasonality'])) {
            $insights[] = [
                'type' => 'info',
                'message' => 'Seasonal patterns detected. Plan inventory accordingly.',
                'priority' => 'medium'
            ];
        }

        return $insights;
    }

    private function generatePriceInsights(): array
    {
        $result = $this->prediction_result;
        $insights = [];

        if (isset($result['optimal_price']) && isset($result['current_price'])) {
            $optimal = $result['optimal_price'];
            $current = $result['current_price'];
            $difference = (($optimal - $current) / $current) * 100;

            if ($difference > 5) {
                $insights[] = [
                    'type' => 'positive',
                    'message' => "Consider increasing price by " . number_format($difference, 1) . "% to optimize revenue.",
                    'priority' => 'high'
                ];
            } elseif ($difference < -5) {
                $insights[] = [
                    'type' => 'warning',
                    'message' => "Consider decreasing price by " . number_format(abs($difference), 1) . "% to increase sales volume.",
                    'priority' => 'medium'
                ];
            }
        }

        return $insights;
    }

    private function generateCustomerInsights(): array
    {
        $result = $this->prediction_result;
        $insights = [];

        if (isset($result['segment'])) {
            $segment = $result['segment'];
            $insights[] = [
                'type' => 'info',
                'message' => "Customer classified as '{$segment}'. Tailor marketing accordingly.",
                'priority' => 'medium'
            ];
        }

        if (isset($result['lifetime_value'])) {
            $ltv = $result['lifetime_value'];
            if ($ltv > 1000) {
                $insights[] = [
                    'type' => 'positive',
                    'message' => 'High-value customer. Prioritize retention efforts.',
                    'priority' => 'high'
                ];
            }
        }

        return $insights;
    }

    private function generateChurnInsights(): array
    {
        $result = $this->prediction_result;
        $insights = [];

        if (isset($result['churn_probability'])) {
            $probability = $result['churn_probability'];
            if ($probability > 0.7) {
                $insights[] = [
                    'type' => 'danger',
                    'message' => 'High churn risk. Immediate intervention recommended.',
                    'priority' => 'urgent'
                ];
            } elseif ($probability > 0.4) {
                $insights[] = [
                    'type' => 'warning',
                    'message' => 'Moderate churn risk. Consider retention campaigns.',
                    'priority' => 'medium'
                ];
            }
        }

        return $insights;
    }

    public static function getModelTypes(): array
    {
        return [
            self::MODEL_LINEAR_REGRESSION => 'Linear Regression',
            self::MODEL_POLYNOMIAL_REGRESSION => 'Polynomial Regression',
            self::MODEL_MOVING_AVERAGE => 'Moving Average',
            self::MODEL_EXPONENTIAL_SMOOTHING => 'Exponential Smoothing',
            self::MODEL_ARIMA => 'ARIMA',
            self::MODEL_NEURAL_NETWORK => 'Neural Network',
        ];
    }

    public static function getPredictionTypes(): array
    {
        return [
            self::TYPE_DEMAND_FORECAST => 'Demand Forecasting',
            self::TYPE_PRICE_OPTIMIZATION => 'Price Optimization',
            self::TYPE_CUSTOMER_BEHAVIOR => 'Customer Behavior Analysis',
            self::TYPE_INVENTORY_OPTIMIZATION => 'Inventory Optimization',
            self::TYPE_SALES_FORECAST => 'Sales Forecasting',
            self::TYPE_CHURN_PREDICTION => 'Customer Churn Prediction',
        ];
    }
}
