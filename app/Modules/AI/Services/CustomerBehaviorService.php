<?php

namespace App\Modules\AI\Services;

use App\Modules\AI\Models\Prediction;
use App\Modules\Customer\Models\Customer;
use App\Modules\Sales\Models\Sale;
use App\Modules\Sales\Models\SaleItem;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CustomerBehaviorService
{
    private array $config;

    public function __construct()
    {
        $this->config = [
            'min_transactions' => 3,
            'analysis_period' => 365, // days
            'churn_threshold_days' => 90,
            'high_value_threshold' => 1000,
        ];
    }

    public function analyzeCustomerBehavior(Customer $customer): Prediction
    {
        // Get customer transaction history
        $transactions = $this->getCustomerTransactions($customer);
        
        if (count($transactions) < $this->config['min_transactions']) {
            throw new \Exception('Insufficient transaction history for analysis');
        }

        // Perform RFM analysis
        $rfmAnalysis = $this->performRFMAnalysis($customer, $transactions);
        
        // Analyze purchase patterns
        $purchasePatterns = $this->analyzePurchasePatterns($transactions);
        
        // Predict customer segment
        $segmentation = $this->predictCustomerSegment($rfmAnalysis, $purchasePatterns);
        
        // Calculate lifetime value
        $lifetimeValue = $this->calculateLifetimeValue($customer, $transactions);
        
        // Predict next purchase
        $nextPurchase = $this->predictNextPurchase($transactions);
        
        // Calculate confidence
        $confidence = $this->calculateBehaviorConfidence($transactions, $rfmAnalysis);
        
        $analysis = [
            'rfm_analysis' => $rfmAnalysis,
            'purchase_patterns' => $purchasePatterns,
            'segmentation' => $segmentation,
            'lifetime_value' => $lifetimeValue,
            'next_purchase_prediction' => $nextPurchase,
            'behavioral_insights' => $this->generateBehavioralInsights($rfmAnalysis, $purchasePatterns, $segmentation),
        ];

        // Create prediction record
        $prediction = Prediction::create([
            'name' => ['en' => "Behavior Analysis for {$customer->name}", 'ar' => "تحليل السلوك لـ {$customer->name}", 'ku' => "شیکردنەوەی ڕەفتار بۆ {$customer->name}"],
            'description' => ['en' => 'Customer behavior analysis and segmentation', 'ar' => 'تحليل سلوك العملاء والتقسيم', 'ku' => 'شیکردنەوەی ڕەفتاری کڕیار و دابەشکردن'],
            'prediction_type' => Prediction::TYPE_CUSTOMER_BEHAVIOR,
            'model_type' => Prediction::MODEL_LINEAR_REGRESSION,
            'target_entity_type' => Customer::class,
            'target_entity_id' => $customer->id,
            'input_data' => [
                'transaction_count' => count($transactions),
                'analysis_period' => $this->config['analysis_period'],
                'customer_age_days' => $customer->created_at->diffInDays(now()),
            ],
            'prediction_result' => $analysis,
            'confidence_score' => $confidence,
            'status' => Prediction::STATUS_COMPLETED,
            'prediction_date' => now()->addDays(30),
            'created_by' => auth()->id() ?? 1,
            'model_parameters' => [
                'rfm_weights' => ['recency' => 0.3, 'frequency' => 0.3, 'monetary' => 0.4],
                'segmentation_method' => 'rfm_based',
            ],
        ]);

        return $prediction;
    }

    private function getCustomerTransactions(Customer $customer): array
    {
        $startDate = now()->subDays($this->config['analysis_period']);
        
        return Sale::where('customer_id', $customer->id)
            ->where('sale_date', '>=', $startDate)
            ->with('items.product')
            ->orderBy('sale_date')
            ->get()
            ->toArray();
    }

    private function performRFMAnalysis(Customer $customer, array $transactions): array
    {
        if (empty($transactions)) {
            return ['recency' => 999, 'frequency' => 0, 'monetary' => 0];
        }

        // Recency: Days since last purchase
        $lastPurchase = max(array_column($transactions, 'sale_date'));
        $recency = Carbon::parse($lastPurchase)->diffInDays(now());
        
        // Frequency: Number of transactions
        $frequency = count($transactions);
        
        // Monetary: Total amount spent
        $monetary = array_sum(array_column($transactions, 'total_amount'));
        
        // Calculate RFM scores (1-5 scale)
        $rfmScores = $this->calculateRFMScores($recency, $frequency, $monetary);
        
        return [
            'recency_days' => $recency,
            'frequency_count' => $frequency,
            'monetary_value' => round($monetary, 2),
            'recency_score' => $rfmScores['recency'],
            'frequency_score' => $rfmScores['frequency'],
            'monetary_score' => $rfmScores['monetary'],
            'rfm_score' => $rfmScores['combined'],
        ];
    }

    private function calculateRFMScores(int $recency, int $frequency, float $monetary): array
    {
        // Recency scoring (lower is better)
        $recencyScore = 5;
        if ($recency > 365) $recencyScore = 1;
        elseif ($recency > 180) $recencyScore = 2;
        elseif ($recency > 90) $recencyScore = 3;
        elseif ($recency > 30) $recencyScore = 4;
        
        // Frequency scoring
        $frequencyScore = 1;
        if ($frequency >= 20) $frequencyScore = 5;
        elseif ($frequency >= 10) $frequencyScore = 4;
        elseif ($frequency >= 5) $frequencyScore = 3;
        elseif ($frequency >= 2) $frequencyScore = 2;
        
        // Monetary scoring
        $monetaryScore = 1;
        if ($monetary >= 10000) $monetaryScore = 5;
        elseif ($monetary >= 5000) $monetaryScore = 4;
        elseif ($monetary >= 2000) $monetaryScore = 3;
        elseif ($monetary >= 500) $monetaryScore = 2;
        
        $combinedScore = ($recencyScore + $frequencyScore + $monetaryScore) / 3;
        
        return [
            'recency' => $recencyScore,
            'frequency' => $frequencyScore,
            'monetary' => $monetaryScore,
            'combined' => round($combinedScore, 2),
        ];
    }

    private function analyzePurchasePatterns(array $transactions): array
    {
        if (empty($transactions)) {
            return [];
        }

        // Analyze purchase timing
        $timingPatterns = $this->analyzeTimingPatterns($transactions);
        
        // Analyze product preferences
        $productPreferences = $this->analyzeProductPreferences($transactions);
        
        // Analyze spending patterns
        $spendingPatterns = $this->analyzeSpendingPatterns($transactions);
        
        // Analyze purchase intervals
        $intervals = $this->analyzePurchaseIntervals($transactions);

        return [
            'timing_patterns' => $timingPatterns,
            'product_preferences' => $productPreferences,
            'spending_patterns' => $spendingPatterns,
            'purchase_intervals' => $intervals,
        ];
    }

    private function analyzeTimingPatterns(array $transactions): array
    {
        $dayOfWeek = [];
        $hourOfDay = [];
        $monthOfYear = [];

        foreach ($transactions as $transaction) {
            $date = Carbon::parse($transaction['sale_date']);
            $created = Carbon::parse($transaction['created_at']);
            
            $dayOfWeek[] = $date->dayOfWeek;
            $hourOfDay[] = $created->hour;
            $monthOfYear[] = $date->month;
        }

        return [
            'preferred_day_of_week' => $this->getMostFrequent($dayOfWeek),
            'preferred_hour' => $this->getMostFrequent($hourOfDay),
            'preferred_month' => $this->getMostFrequent($monthOfYear),
            'day_distribution' => array_count_values($dayOfWeek),
            'hour_distribution' => array_count_values($hourOfDay),
        ];
    }

    private function analyzeProductPreferences(array $transactions): array
    {
        $categories = [];
        $products = [];
        $totalSpent = 0;

        foreach ($transactions as $transaction) {
            $totalSpent += $transaction['total_amount'];
            
            if (isset($transaction['items'])) {
                foreach ($transaction['items'] as $item) {
                    $product = $item['product'] ?? null;
                    if ($product) {
                        $products[] = $product['name'];
                        if (isset($product['category'])) {
                            $categories[] = $product['category']['name'] ?? 'Unknown';
                        }
                    }
                }
            }
        }

        $categoryFreq = array_count_values($categories);
        $productFreq = array_count_values($products);

        return [
            'favorite_categories' => array_slice($categoryFreq, 0, 5, true),
            'favorite_products' => array_slice($productFreq, 0, 10, true),
            'category_diversity' => count($categoryFreq),
            'product_diversity' => count($productFreq),
        ];
    }

    private function analyzeSpendingPatterns(array $transactions): array
    {
        $amounts = array_column($transactions, 'total_amount');
        
        if (empty($amounts)) {
            return [];
        }

        sort($amounts);
        $count = count($amounts);
        
        return [
            'average_order_value' => round(array_sum($amounts) / $count, 2),
            'median_order_value' => $count % 2 === 0 
                ? ($amounts[$count/2 - 1] + $amounts[$count/2]) / 2 
                : $amounts[floor($count/2)],
            'min_order_value' => min($amounts),
            'max_order_value' => max($amounts),
            'spending_consistency' => $this->calculateSpendingConsistency($amounts),
        ];
    }

    private function analyzePurchaseIntervals(array $transactions): array
    {
        if (count($transactions) < 2) {
            return ['average_interval_days' => null];
        }

        $intervals = [];
        for ($i = 1; $i < count($transactions); $i++) {
            $prev = Carbon::parse($transactions[$i-1]['sale_date']);
            $curr = Carbon::parse($transactions[$i]['sale_date']);
            $intervals[] = $prev->diffInDays($curr);
        }

        return [
            'average_interval_days' => round(array_sum($intervals) / count($intervals), 1),
            'min_interval_days' => min($intervals),
            'max_interval_days' => max($intervals),
            'interval_consistency' => $this->calculateIntervalConsistency($intervals),
        ];
    }

    private function predictCustomerSegment(array $rfmAnalysis, array $purchasePatterns): array
    {
        $rfmScore = $rfmAnalysis['rfm_score'];
        $recency = $rfmAnalysis['recency_score'];
        $frequency = $rfmAnalysis['frequency_score'];
        $monetary = $rfmAnalysis['monetary_score'];

        // Determine segment based on RFM scores
        if ($recency >= 4 && $frequency >= 4 && $monetary >= 4) {
            $segment = 'Champions';
            $description = 'Best customers who buy frequently and recently';
        } elseif ($recency >= 3 && $frequency >= 3 && $monetary >= 3) {
            $segment = 'Loyal Customers';
            $description = 'Regular customers with good value';
        } elseif ($recency >= 4 && $frequency <= 2) {
            $segment = 'New Customers';
            $description = 'Recent customers with potential';
        } elseif ($recency <= 2 && $frequency >= 3) {
            $segment = 'At Risk';
            $description = 'Good customers who haven\'t purchased recently';
        } elseif ($recency <= 2 && $frequency <= 2) {
            $segment = 'Lost';
            $description = 'Customers who haven\'t purchased in a long time';
        } elseif ($monetary >= 4) {
            $segment = 'Big Spenders';
            $description = 'High-value customers regardless of frequency';
        } else {
            $segment = 'Others';
            $description = 'Customers requiring further analysis';
        }

        return [
            'segment' => $segment,
            'description' => $description,
            'confidence' => $this->calculateSegmentConfidence($rfmAnalysis),
            'characteristics' => $this->getSegmentCharacteristics($segment),
        ];
    }

    private function calculateLifetimeValue(Customer $customer, array $transactions): array
    {
        $totalSpent = array_sum(array_column($transactions, 'total_amount'));
        $customerAge = $customer->created_at->diffInDays(now());
        $transactionCount = count($transactions);
        
        $avgOrderValue = $transactionCount > 0 ? $totalSpent / $transactionCount : 0;
        $purchaseFrequency = $customerAge > 0 ? $transactionCount / ($customerAge / 365) : 0;
        
        // Predict future value (simplified model)
        $predictedLifespan = max(365, $customerAge * 2); // Assume customer will be active for at least current age * 2
        $predictedAnnualValue = $avgOrderValue * $purchaseFrequency;
        $predictedLifetimeValue = $predictedAnnualValue * ($predictedLifespan / 365);

        return [
            'historical_value' => round($totalSpent, 2),
            'average_order_value' => round($avgOrderValue, 2),
            'purchase_frequency_per_year' => round($purchaseFrequency, 2),
            'predicted_annual_value' => round($predictedAnnualValue, 2),
            'predicted_lifetime_value' => round($predictedLifetimeValue, 2),
            'customer_age_days' => $customerAge,
        ];
    }

    private function predictNextPurchase(array $transactions): array
    {
        if (count($transactions) < 2) {
            return ['predicted_date' => null, 'confidence' => 0];
        }

        // Calculate average purchase interval
        $intervals = [];
        for ($i = 1; $i < count($transactions); $i++) {
            $prev = Carbon::parse($transactions[$i-1]['sale_date']);
            $curr = Carbon::parse($transactions[$i]['sale_date']);
            $intervals[] = $prev->diffInDays($curr);
        }

        $avgInterval = array_sum($intervals) / count($intervals);
        $lastPurchase = Carbon::parse(end($transactions)['sale_date']);
        $predictedDate = $lastPurchase->addDays(round($avgInterval));

        // Calculate confidence based on interval consistency
        $intervalStdDev = $this->calculateStandardDeviation($intervals);
        $confidence = max(0, 1 - ($intervalStdDev / $avgInterval));

        return [
            'predicted_date' => $predictedDate->format('Y-m-d'),
            'confidence' => round($confidence, 3),
            'average_interval_days' => round($avgInterval, 1),
            'days_since_last_purchase' => $lastPurchase->diffInDays(now()),
        ];
    }

    private function calculateBehaviorConfidence(array $transactions, array $rfmAnalysis): float
    {
        $confidence = 0.5; // Base confidence
        
        // Data quantity factor
        $transactionCount = count($transactions);
        $dataQuality = min(1, $transactionCount / 10); // Prefer 10+ transactions
        $confidence += $dataQuality * 0.3;
        
        // RFM score reliability
        $rfmScore = $rfmAnalysis['rfm_score'];
        if ($rfmScore >= 3) {
            $confidence += 0.2; // Higher confidence for clear patterns
        }
        
        // Recency factor
        $recency = $rfmAnalysis['recency_days'];
        if ($recency <= 90) {
            $confidence += 0.2; // Recent activity increases confidence
        }

        return min(1, max(0, $confidence));
    }

    private function generateBehavioralInsights(array $rfmAnalysis, array $purchasePatterns, array $segmentation): array
    {
        $insights = [];
        
        // RFM insights
        if ($rfmAnalysis['recency_days'] > 90) {
            $insights[] = [
                'type' => 'warning',
                'category' => 'engagement',
                'message' => 'Customer has not purchased in over 90 days - consider re-engagement campaign',
            ];
        }
        
        if ($rfmAnalysis['monetary_score'] >= 4) {
            $insights[] = [
                'type' => 'positive',
                'category' => 'value',
                'message' => 'High-value customer - prioritize retention efforts',
            ];
        }
        
        // Timing insights
        if (isset($purchasePatterns['timing_patterns']['preferred_day_of_week'])) {
            $dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            $preferredDay = $dayNames[$purchasePatterns['timing_patterns']['preferred_day_of_week']];
            $insights[] = [
                'type' => 'info',
                'category' => 'timing',
                'message' => "Customer prefers shopping on {$preferredDay}s",
            ];
        }
        
        // Segment insights
        $segment = $segmentation['segment'];
        if ($segment === 'At Risk') {
            $insights[] = [
                'type' => 'danger',
                'category' => 'retention',
                'message' => 'Customer is at risk of churning - immediate action recommended',
            ];
        } elseif ($segment === 'Champions') {
            $insights[] = [
                'type' => 'success',
                'category' => 'loyalty',
                'message' => 'Champion customer - consider VIP treatment and referral programs',
            ];
        }

        return $insights;
    }

    // Helper methods
    private function getMostFrequent(array $array): int
    {
        if (empty($array)) return 0;
        $counts = array_count_values($array);
        return array_keys($counts, max($counts))[0];
    }

    private function calculateSpendingConsistency(array $amounts): float
    {
        if (count($amounts) < 2) return 1;
        
        $mean = array_sum($amounts) / count($amounts);
        $variance = 0;
        
        foreach ($amounts as $amount) {
            $variance += pow($amount - $mean, 2);
        }
        $variance /= count($amounts);
        
        $stdDev = sqrt($variance);
        $cv = $mean > 0 ? $stdDev / $mean : 1;
        
        return max(0, 1 - $cv); // Higher consistency = lower coefficient of variation
    }

    private function calculateIntervalConsistency(array $intervals): float
    {
        if (count($intervals) < 2) return 1;
        
        $stdDev = $this->calculateStandardDeviation($intervals);
        $mean = array_sum($intervals) / count($intervals);
        
        $cv = $mean > 0 ? $stdDev / $mean : 1;
        return max(0, 1 - $cv);
    }

    private function calculateStandardDeviation(array $values): float
    {
        if (count($values) < 2) return 0;
        
        $mean = array_sum($values) / count($values);
        $variance = 0;
        
        foreach ($values as $value) {
            $variance += pow($value - $mean, 2);
        }
        $variance /= count($values);
        
        return sqrt($variance);
    }

    private function calculateSegmentConfidence(array $rfmAnalysis): float
    {
        $scores = [$rfmAnalysis['recency_score'], $rfmAnalysis['frequency_score'], $rfmAnalysis['monetary_score']];
        $avgScore = array_sum($scores) / count($scores);
        
        // Higher average scores indicate clearer segmentation
        return min(1, $avgScore / 5);
    }

    private function getSegmentCharacteristics(string $segment): array
    {
        return match($segment) {
            'Champions' => [
                'retention_priority' => 'high',
                'marketing_approach' => 'vip_treatment',
                'recommended_actions' => ['loyalty_program', 'referral_incentives', 'exclusive_offers'],
            ],
            'Loyal Customers' => [
                'retention_priority' => 'high',
                'marketing_approach' => 'relationship_building',
                'recommended_actions' => ['personalized_offers', 'loyalty_rewards', 'feedback_requests'],
            ],
            'At Risk' => [
                'retention_priority' => 'urgent',
                'marketing_approach' => 're_engagement',
                'recommended_actions' => ['win_back_campaign', 'special_discounts', 'personal_contact'],
            ],
            'New Customers' => [
                'retention_priority' => 'medium',
                'marketing_approach' => 'onboarding',
                'recommended_actions' => ['welcome_series', 'product_education', 'first_purchase_incentive'],
            ],
            default => [
                'retention_priority' => 'medium',
                'marketing_approach' => 'general',
                'recommended_actions' => ['regular_communication', 'value_demonstration'],
            ],
        };
    }

    public function predictChurnRisk(Customer $customer): Prediction
    {
        $transactions = $this->getCustomerTransactions($customer);
        $rfmAnalysis = $this->performRFMAnalysis($customer, $transactions);
        
        // Calculate churn probability
        $churnProbability = $this->calculateChurnProbability($rfmAnalysis, $transactions);
        
        // Determine risk level
        $riskLevel = $this->determineChurnRiskLevel($churnProbability);
        
        $analysis = [
            'churn_probability' => round($churnProbability, 3),
            'risk_level' => $riskLevel,
            'days_since_last_purchase' => $rfmAnalysis['recency_days'],
            'risk_factors' => $this->identifyChurnRiskFactors($rfmAnalysis, $transactions),
            'recommended_actions' => $this->getChurnPreventionActions($riskLevel),
        ];

        return Prediction::create([
            'name' => ['en' => "Churn Risk for {$customer->name}", 'ar' => "مخاطر فقدان العميل {$customer->name}", 'ku' => "مەترسی لەدەستدانی کڕیار {$customer->name}"],
            'description' => ['en' => 'Customer churn risk prediction', 'ar' => 'توقع مخاطر فقدان العميل', 'ku' => 'پێشبینی مەترسی لەدەستدانی کڕیار'],
            'prediction_type' => Prediction::TYPE_CHURN_PREDICTION,
            'model_type' => Prediction::MODEL_LINEAR_REGRESSION,
            'target_entity_type' => Customer::class,
            'target_entity_id' => $customer->id,
            'input_data' => [
                'rfm_analysis' => $rfmAnalysis,
                'transaction_count' => count($transactions),
            ],
            'prediction_result' => $analysis,
            'confidence_score' => $this->calculateChurnConfidence($rfmAnalysis, $transactions),
            'status' => Prediction::STATUS_COMPLETED,
            'prediction_date' => now()->addDays(30),
            'created_by' => auth()->id() ?? 1,
        ]);
    }

    private function calculateChurnProbability(array $rfmAnalysis, array $transactions): float
    {
        $recencyDays = $rfmAnalysis['recency_days'];
        $frequency = $rfmAnalysis['frequency_count'];
        $monetary = $rfmAnalysis['monetary_value'];
        
        // Base probability on recency (most important factor)
        $recencyWeight = 0.5;
        $frequencyWeight = 0.3;
        $monetaryWeight = 0.2;
        
        // Recency factor (higher recency = higher churn probability)
        $recencyFactor = min(1, $recencyDays / $this->config['churn_threshold_days']);
        
        // Frequency factor (lower frequency = higher churn probability)
        $frequencyFactor = max(0, 1 - ($frequency / 10)); // Normalize to 10 transactions
        
        // Monetary factor (lower value = higher churn probability)
        $monetaryFactor = max(0, 1 - ($monetary / $this->config['high_value_threshold']));
        
        $churnProbability = ($recencyFactor * $recencyWeight) + 
                           ($frequencyFactor * $frequencyWeight) + 
                           ($monetaryFactor * $monetaryWeight);
        
        return min(1, max(0, $churnProbability));
    }

    private function determineChurnRiskLevel(float $probability): string
    {
        if ($probability >= 0.8) return 'critical';
        if ($probability >= 0.6) return 'high';
        if ($probability >= 0.4) return 'medium';
        if ($probability >= 0.2) return 'low';
        return 'minimal';
    }

    private function identifyChurnRiskFactors(array $rfmAnalysis, array $transactions): array
    {
        $factors = [];
        
        if ($rfmAnalysis['recency_days'] > $this->config['churn_threshold_days']) {
            $factors[] = 'Long time since last purchase';
        }
        
        if ($rfmAnalysis['frequency_count'] < 3) {
            $factors[] = 'Low purchase frequency';
        }
        
        if ($rfmAnalysis['monetary_value'] < 500) {
            $factors[] = 'Low total spending';
        }
        
        if (count($transactions) > 0) {
            $recentTransactions = array_filter($transactions, function($t) {
                return Carbon::parse($t['sale_date'])->diffInDays(now()) <= 30;
            });
            
            if (empty($recentTransactions)) {
                $factors[] = 'No recent activity';
            }
        }
        
        return $factors;
    }

    private function getChurnPreventionActions(string $riskLevel): array
    {
        return match($riskLevel) {
            'critical' => [
                'Immediate personal outreach',
                'Exclusive discount offer',
                'Account manager assignment',
                'Exit interview if churned',
            ],
            'high' => [
                'Targeted re-engagement campaign',
                'Special promotion',
                'Product recommendations',
                'Feedback survey',
            ],
            'medium' => [
                'Regular check-in email',
                'Loyalty program invitation',
                'Cross-sell opportunities',
            ],
            'low' => [
                'Newsletter subscription',
                'Seasonal promotions',
            ],
            default => [
                'Monitor activity',
            ],
        };
    }

    private function calculateChurnConfidence(array $rfmAnalysis, array $transactions): float
    {
        $confidence = 0.6; // Base confidence
        
        // More transactions = higher confidence
        $transactionCount = count($transactions);
        if ($transactionCount >= 5) $confidence += 0.2;
        
        // Clear RFM patterns = higher confidence
        $rfmScore = $rfmAnalysis['rfm_score'];
        if ($rfmScore <= 2 || $rfmScore >= 4) $confidence += 0.2;
        
        return min(1, $confidence);
    }
}
