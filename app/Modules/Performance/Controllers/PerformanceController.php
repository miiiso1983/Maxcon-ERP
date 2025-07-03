<?php

namespace App\Modules\Performance\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Performance\Services\CacheService;
use App\Modules\Performance\Services\DatabaseOptimizationService;
use App\Modules\Performance\Services\PerformanceMonitoringService;
use App\Services\RedisMonitoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class PerformanceController extends Controller
{
    protected CacheService $cacheService;
    protected DatabaseOptimizationService $dbOptimizer;
    protected PerformanceMonitoringService $monitor;
    protected RedisMonitoringService $redisMonitor;

    public function __construct(
        CacheService $cacheService,
        DatabaseOptimizationService $dbOptimizer,
        PerformanceMonitoringService $monitor,
        RedisMonitoringService $redisMonitor
    ) {
        $this->cacheService = $cacheService;
        $this->dbOptimizer = $dbOptimizer;
        $this->monitor = $monitor;
        $this->redisMonitor = $redisMonitor;
    }

    public function dashboard()
    {
        // Get system metrics
        $systemMetrics = $this->monitor->getSystemMetrics();
        
        // Get performance summary
        $performanceSummary = $this->monitor->getPerformanceSummary(24);
        
        // Get cache statistics
        $cacheStats = $this->cacheService->getCacheStats();
        
        // Get database health
        $databaseHealth = $this->dbOptimizer->getHealthScore();
        
        // Get performance alerts
        $alerts = $this->monitor->getPerformanceAlerts();
        
        // Get optimization recommendations
        $recommendations = $this->getOptimizationRecommendations();

        return view('tenant.performance.dashboard', compact(
            'systemMetrics', 'performanceSummary', 'cacheStats', 
            'databaseHealth', 'alerts', 'recommendations'
        ));
    }

    public function cacheManagement()
    {
        $stats = $this->cacheService->getCacheStats();
        $tenantId = tenant('id') ?? 'default';
        
        return view('tenant.performance.cache', compact('stats', 'tenantId'));
    }

    public function clearCache(Request $request)
    {
        $request->validate([
            'cache_type' => 'required|in:all,config,route,view,application',
        ]);

        try {
            $results = [];
            
            switch ($request->cache_type) {
                case 'all':
                    Artisan::call('cache:clear');
                    Artisan::call('config:clear');
                    Artisan::call('route:clear');
                    Artisan::call('view:clear');
                    $results['message'] = 'All caches cleared successfully';
                    break;
                    
                case 'config':
                    Artisan::call('config:clear');
                    $results['message'] = 'Configuration cache cleared';
                    break;
                    
                case 'route':
                    Artisan::call('route:clear');
                    $results['message'] = 'Route cache cleared';
                    break;
                    
                case 'view':
                    Artisan::call('view:clear');
                    $results['message'] = 'View cache cleared';
                    break;
                    
                case 'application':
                    $this->cacheService->clearAll();
                    $results['message'] = 'Application cache cleared';
                    break;
            }

            return response()->json([
                'success' => true,
                'results' => $results,
            ]);

        } catch (\Exception $e) {
            Log::error('Cache clear failed', [
                'type' => $request->cache_type,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to clear cache: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function warmUpCache()
    {
        try {
            $tenantId = tenant('id') ?? 'default';
            $results = $this->cacheService->warmUpCache($tenantId);

            return response()->json([
                'success' => true,
                'message' => 'Cache warmed up successfully',
                'results' => $results,
            ]);

        } catch (\Exception $e) {
            Log::error('Cache warm up failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to warm up cache: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function databaseOptimization()
    {
        $analysis = $this->dbOptimizer->analyzePerformance();
        $healthScore = $this->dbOptimizer->getHealthScore();
        $indexSuggestions = $this->dbOptimizer->suggestIndexes();

        return view('tenant.performance.database', compact(
            'analysis', 'healthScore', 'indexSuggestions'
        ));
    }

    public function optimizeDatabase(Request $request)
    {
        $request->validate([
            'action' => 'required|in:optimize_tables,cleanup_data,analyze_performance',
            'days_to_keep' => 'sometimes|integer|min:1|max:365',
        ]);

        try {
            $results = [];

            switch ($request->action) {
                case 'optimize_tables':
                    $results = $this->dbOptimizer->optimizeTables();
                    break;
                    
                case 'cleanup_data':
                    $daysToKeep = $request->days_to_keep ?? 90;
                    $results = $this->dbOptimizer->cleanupOldData($daysToKeep);
                    break;
                    
                case 'analyze_performance':
                    $results = $this->dbOptimizer->analyzePerformance();
                    break;
            }

            return response()->json([
                'success' => true,
                'results' => $results,
            ]);

        } catch (\Exception $e) {
            Log::error('Database optimization failed', [
                'action' => $request->action,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Database optimization failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function performanceMonitoring()
    {
        $systemMetrics = $this->monitor->getSystemMetrics();
        $performanceSummary = $this->monitor->getPerformanceSummary(24);
        $alerts = $this->monitor->getPerformanceAlerts();

        return view('tenant.performance.monitoring', compact(
            'systemMetrics', 'performanceSummary', 'alerts'
        ));
    }

    public function generateReport(Request $request)
    {
        $request->validate([
            'days' => 'sometimes|integer|min:1|max:30',
        ]);

        try {
            $days = $request->days ?? 7;
            $report = $this->monitor->generateReport($days);

            return response()->json([
                'success' => true,
                'report' => $report,
            ]);

        } catch (\Exception $e) {
            Log::error('Performance report generation failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to generate report: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function optimizePerformance()
    {
        try {
            $optimizations = $this->monitor->optimizePerformance();

            return response()->json([
                'success' => true,
                'message' => 'Performance optimization completed',
                'optimizations' => $optimizations,
            ]);

        } catch (\Exception $e) {
            Log::error('Performance optimization failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'error' => 'Performance optimization failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getMetrics(Request $request)
    {
        $request->validate([
            'type' => 'required|in:system,performance,cache,database',
            'period' => 'sometimes|integer|min:1|max:24',
        ]);

        try {
            $metrics = [];

            switch ($request->type) {
                case 'system':
                    $metrics = $this->monitor->getSystemMetrics();
                    break;
                    
                case 'performance':
                    $period = $request->period ?? 1;
                    $metrics = $this->monitor->getPerformanceSummary($period);
                    break;
                    
                case 'cache':
                    $metrics = $this->cacheService->getCacheStats();
                    break;
                    
                case 'database':
                    $metrics = $this->dbOptimizer->getHealthScore();
                    break;
            }

            return response()->json([
                'success' => true,
                'metrics' => $metrics,
                'timestamp' => now()->toISOString(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to get metrics: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getAlerts()
    {
        try {
            $alerts = $this->monitor->getPerformanceAlerts();

            return response()->json([
                'success' => true,
                'alerts' => $alerts,
                'count' => count($alerts),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to get alerts: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function getOptimizationRecommendations(): array
    {
        $recommendations = [];

        try {
            // Cache recommendations
            $cacheStats = $this->cacheService->getCacheStats();
            if (isset($cacheStats['driver']) && $cacheStats['driver'] === 'file') {
                $recommendations[] = [
                    'category' => 'caching',
                    'priority' => 'high',
                    'title' => 'Upgrade to Redis Cache',
                    'description' => 'File cache is slower than Redis. Consider upgrading for better performance.',
                    'action' => 'Configure Redis cache driver',
                ];
            }

            // Database recommendations
            $dbHealth = $this->dbOptimizer->getHealthScore();
            if ($dbHealth['score'] < 80) {
                $recommendations[] = [
                    'category' => 'database',
                    'priority' => 'high',
                    'title' => 'Database Optimization Needed',
                    'description' => "Database health score is {$dbHealth['score']}/100. Optimization required.",
                    'action' => 'Run database optimization tools',
                ];
            }

            // Memory recommendations
            $systemMetrics = $this->monitor->getSystemMetrics();
            if ($systemMetrics['memory']['usage_percent'] > 80) {
                $recommendations[] = [
                    'category' => 'memory',
                    'priority' => 'medium',
                    'title' => 'High Memory Usage',
                    'description' => "Memory usage is {$systemMetrics['memory']['usage_percent']}%. Consider optimization.",
                    'action' => 'Optimize memory usage or increase server memory',
                ];
            }

            // Performance recommendations
            $performanceSummary = $this->monitor->getPerformanceSummary(1);
            if ($performanceSummary['average_response_time'] > 1000) {
                $recommendations[] = [
                    'category' => 'performance',
                    'priority' => 'high',
                    'title' => 'Slow Response Times',
                    'description' => "Average response time is {$performanceSummary['average_response_time']}ms.",
                    'action' => 'Optimize slow queries and implement caching',
                ];
            }

        } catch (\Exception $e) {
            Log::error('Failed to generate recommendations', ['error' => $e->getMessage()]);
        }

        return $recommendations;
    }

    /**
     * Get Redis monitoring data
     */
    public function redisMonitoring()
    {
        try {
            $data = $this->redisMonitor->getDashboardData();

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Redis monitoring failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to get Redis monitoring data'
            ], 500);
        }
    }

    /**
     * Warm up Redis cache
     */
    public function warmUpRedisCache()
    {
        try {
            $result = $this->redisMonitor->warmUpCache();

            return response()->json([
                'success' => $result['success'],
                'message' => $result['success']
                    ? "Cache warmed successfully. {$result['count']} keys cached."
                    : 'Failed to warm up cache',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Redis cache warm up failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to warm up Redis cache'
            ], 500);
        }
    }

    /**
     * Clear Redis cache
     */
    public function clearRedisCache()
    {
        try {
            $success = $this->redisMonitor->clearAllCache();

            return response()->json([
                'success' => $success,
                'message' => $success
                    ? 'Redis cache cleared successfully'
                    : 'Failed to clear Redis cache'
            ]);
        } catch (\Exception $e) {
            Log::error('Redis cache clear failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to clear Redis cache'
            ], 500);
        }
    }
}
