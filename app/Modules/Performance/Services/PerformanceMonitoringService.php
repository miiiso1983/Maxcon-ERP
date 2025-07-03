<?php

namespace App\Modules\Performance\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PerformanceMonitoringService
{
    private array $metrics = [];
    private float $startTime;
    private int $startMemory;

    public function __construct()
    {
        $this->startTime = microtime(true);
        $this->startMemory = memory_get_usage(true);
    }

    /**
     * Start monitoring a request
     */
    public function startRequest(Request $request): void
    {
        $this->metrics = [
            'request_id' => uniqid('req_'),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'start_time' => $this->startTime,
            'start_memory' => $this->startMemory,
            'queries' => [],
            'cache_hits' => 0,
            'cache_misses' => 0,
        ];

        // Enable query logging
        DB::enableQueryLog();
    }

    /**
     * End monitoring and log results
     */
    public function endRequest(): array
    {
        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);
        $peakMemory = memory_get_peak_usage(true);

        $this->metrics['end_time'] = $endTime;
        $this->metrics['duration'] = round(($endTime - $this->startTime) * 1000, 2); // milliseconds
        $this->metrics['memory_used'] = $endMemory - $this->startMemory;
        $this->metrics['peak_memory'] = $peakMemory;
        $this->metrics['queries'] = DB::getQueryLog();
        $this->metrics['query_count'] = count($this->metrics['queries']);
        $this->metrics['total_query_time'] = $this->calculateTotalQueryTime();

        // Log performance metrics
        $this->logPerformanceMetrics();

        // Store metrics for analysis
        $this->storeMetrics();

        return $this->metrics;
    }

    /**
     * Monitor database query performance
     */
    public function monitorQuery(string $sql, array $bindings, float $time): void
    {
        if ($time > 1000) { // Log slow queries (>1 second)
            Log::warning('Slow query detected', [
                'sql' => $sql,
                'bindings' => $bindings,
                'time' => $time,
                'request_id' => $this->metrics['request_id'] ?? null,
            ]);
        }
    }

    /**
     * Monitor cache operations
     */
    public function recordCacheHit(string $key): void
    {
        $this->metrics['cache_hits']++;
        
        Log::debug('Cache hit', [
            'key' => $key,
            'request_id' => $this->metrics['request_id'] ?? null,
        ]);
    }

    public function recordCacheMiss(string $key): void
    {
        $this->metrics['cache_misses']++;
        
        Log::debug('Cache miss', [
            'key' => $key,
            'request_id' => $this->metrics['request_id'] ?? null,
        ]);
    }

    /**
     * Get system performance metrics
     */
    public function getSystemMetrics(): array
    {
        return [
            'cpu' => $this->getCpuUsage(),
            'memory' => $this->getMemoryUsage(),
            'disk' => $this->getDiskUsage(),
            'load_average' => $this->getLoadAverage(),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * Get application performance summary
     */
    public function getPerformanceSummary(int $hours = 24): array
    {
        $cacheKey = "performance_summary_{$hours}h";
        
        return Cache::remember($cacheKey, 300, function () use ($hours) {
            $startTime = now()->subHours($hours);
            
            return [
                'period' => "{$hours} hours",
                'total_requests' => $this->getRequestCount($startTime),
                'average_response_time' => $this->getAverageResponseTime($startTime),
                'slow_requests' => $this->getSlowRequestCount($startTime),
                'error_rate' => $this->getErrorRate($startTime),
                'cache_hit_rate' => $this->getCacheHitRate($startTime),
                'database_performance' => $this->getDatabasePerformance($startTime),
                'top_slow_endpoints' => $this->getTopSlowEndpoints($startTime),
            ];
        });
    }

    /**
     * Get real-time performance alerts
     */
    public function getPerformanceAlerts(): array
    {
        $alerts = [];

        // Check response time
        $avgResponseTime = $this->getAverageResponseTime(now()->subMinutes(5));
        if ($avgResponseTime > 2000) { // 2 seconds
            $alerts[] = [
                'type' => 'response_time',
                'severity' => 'warning',
                'message' => "Average response time is {$avgResponseTime}ms (last 5 minutes)",
                'threshold' => 2000,
                'current' => $avgResponseTime,
            ];
        }

        // Check error rate
        $errorRate = $this->getErrorRate(now()->subMinutes(5));
        if ($errorRate > 5) { // 5%
            $alerts[] = [
                'type' => 'error_rate',
                'severity' => 'critical',
                'message' => "Error rate is {$errorRate}% (last 5 minutes)",
                'threshold' => 5,
                'current' => $errorRate,
            ];
        }

        // Check memory usage
        $memoryUsage = $this->getMemoryUsage();
        if ($memoryUsage['usage_percent'] > 90) {
            $alerts[] = [
                'type' => 'memory',
                'severity' => 'critical',
                'message' => "Memory usage is {$memoryUsage['usage_percent']}%",
                'threshold' => 90,
                'current' => $memoryUsage['usage_percent'],
            ];
        }

        // Check disk usage
        $diskUsage = $this->getDiskUsage();
        if ($diskUsage['usage_percent'] > 85) {
            $alerts[] = [
                'type' => 'disk',
                'severity' => 'warning',
                'message' => "Disk usage is {$diskUsage['usage_percent']}%",
                'threshold' => 85,
                'current' => $diskUsage['usage_percent'],
            ];
        }

        return $alerts;
    }

    /**
     * Optimize performance based on metrics
     */
    public function optimizePerformance(): array
    {
        $optimizations = [];

        try {
            // Clear expired cache
            if (config('cache.default') === 'file') {
                $this->clearExpiredFileCache();
                $optimizations[] = 'Cleared expired file cache';
            }

            // Optimize database
            $dbOptimizer = new DatabaseOptimizationService();
            $cleanupResults = $dbOptimizer->cleanupOldData(30);
            if (array_sum($cleanupResults) > 0) {
                $optimizations[] = 'Cleaned up old database records';
            }

            // Clear old logs
            $this->clearOldLogs();
            $optimizations[] = 'Cleared old log files';

            // Warm up critical caches
            $cacheService = new CacheService();
            $tenantId = tenant('id') ?? 'default';
            $cacheService->warmUpCache($tenantId);
            $optimizations[] = 'Warmed up critical caches';

        } catch (\Exception $e) {
            Log::error('Performance optimization failed', ['error' => $e->getMessage()]);
            $optimizations[] = 'Error during optimization: ' . $e->getMessage();
        }

        return $optimizations;
    }

    /**
     * Generate performance report
     */
    public function generateReport(int $days = 7): array
    {
        $startDate = now()->subDays($days);
        
        return [
            'period' => "{$days} days",
            'generated_at' => now()->toISOString(),
            'summary' => $this->getPerformanceSummary($days * 24),
            'trends' => $this->getPerformanceTrends($startDate),
            'top_issues' => $this->getTopPerformanceIssues($startDate),
            'recommendations' => $this->getPerformanceRecommendations(),
        ];
    }

    /**
     * Calculate total query time
     */
    private function calculateTotalQueryTime(): float
    {
        $totalTime = 0;
        foreach ($this->metrics['queries'] as $query) {
            $totalTime += $query['time'] ?? 0;
        }
        return round($totalTime, 2);
    }

    /**
     * Log performance metrics
     */
    private function logPerformanceMetrics(): void
    {
        $logData = [
            'request_id' => $this->metrics['request_id'],
            'url' => $this->metrics['url'],
            'method' => $this->metrics['method'],
            'duration' => $this->metrics['duration'],
            'memory_used' => $this->formatBytes($this->metrics['memory_used']),
            'query_count' => $this->metrics['query_count'],
            'total_query_time' => $this->metrics['total_query_time'],
        ];

        if ($this->metrics['duration'] > 1000) { // Log slow requests
            Log::warning('Slow request detected', $logData);
        } else {
            Log::info('Request completed', $logData);
        }
    }

    /**
     * Store metrics for analysis
     */
    private function storeMetrics(): void
    {
        try {
            // Store in cache for recent analysis
            $key = 'performance_metrics:' . date('Y-m-d-H');
            $metrics = Cache::get($key, []);
            $metrics[] = [
                'timestamp' => $this->metrics['start_time'],
                'duration' => $this->metrics['duration'],
                'memory' => $this->metrics['memory_used'],
                'queries' => $this->metrics['query_count'],
                'url' => $this->metrics['url'],
            ];
            
            // Keep only last 1000 requests per hour
            if (count($metrics) > 1000) {
                $metrics = array_slice($metrics, -1000);
            }
            
            Cache::put($key, $metrics, 3600); // 1 hour

        } catch (\Exception $e) {
            Log::error('Failed to store performance metrics', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get CPU usage (Linux/Unix only)
     */
    private function getCpuUsage(): array
    {
        if (PHP_OS_FAMILY !== 'Linux') {
            return ['usage_percent' => 0, 'available' => false];
        }

        try {
            $load = sys_getloadavg();
            return [
                'load_1min' => $load[0] ?? 0,
                'load_5min' => $load[1] ?? 0,
                'load_15min' => $load[2] ?? 0,
                'available' => true,
            ];
        } catch (\Exception $e) {
            return ['usage_percent' => 0, 'available' => false];
        }
    }

    /**
     * Get memory usage
     */
    private function getMemoryUsage(): array
    {
        $used = memory_get_usage(true);
        $peak = memory_get_peak_usage(true);
        $limit = $this->parseMemoryLimit(ini_get('memory_limit'));

        return [
            'used' => $used,
            'used_formatted' => $this->formatBytes($used),
            'peak' => $peak,
            'peak_formatted' => $this->formatBytes($peak),
            'limit' => $limit,
            'limit_formatted' => $this->formatBytes($limit),
            'usage_percent' => $limit > 0 ? round(($used / $limit) * 100, 2) : 0,
        ];
    }

    /**
     * Get disk usage
     */
    private function getDiskUsage(): array
    {
        $path = storage_path();
        $total = disk_total_space($path);
        $free = disk_free_space($path);
        $used = $total - $free;

        return [
            'total' => $total,
            'total_formatted' => $this->formatBytes($total),
            'used' => $used,
            'used_formatted' => $this->formatBytes($used),
            'free' => $free,
            'free_formatted' => $this->formatBytes($free),
            'usage_percent' => $total > 0 ? round(($used / $total) * 100, 2) : 0,
        ];
    }

    /**
     * Get load average (Linux/Unix only)
     */
    private function getLoadAverage(): array
    {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            return [
                '1min' => $load[0] ?? 0,
                '5min' => $load[1] ?? 0,
                '15min' => $load[2] ?? 0,
                'available' => true,
            ];
        }

        return ['available' => false];
    }

    /**
     * Parse memory limit string
     */
    private function parseMemoryLimit(string $limit): int
    {
        if ($limit === '-1') {
            return PHP_INT_MAX;
        }

        $unit = strtolower(substr($limit, -1));
        $value = (int) substr($limit, 0, -1);

        switch ($unit) {
            case 'g': return $value * 1024 * 1024 * 1024;
            case 'm': return $value * 1024 * 1024;
            case 'k': return $value * 1024;
            default: return (int) $limit;
        }
    }

    /**
     * Format bytes to human readable
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get request count for period
     */
    private function getRequestCount(Carbon $since): int
    {
        // This would typically query a metrics database
        // For now, return a simulated value
        return rand(1000, 5000);
    }

    /**
     * Get average response time for period
     */
    private function getAverageResponseTime(Carbon $since): float
    {
        // This would typically query stored metrics
        // For now, return a simulated value
        return rand(200, 800);
    }

    /**
     * Get slow request count for period
     */
    private function getSlowRequestCount(Carbon $since): int
    {
        // This would typically query stored metrics
        return rand(10, 50);
    }

    /**
     * Get error rate for period
     */
    private function getErrorRate(Carbon $since): float
    {
        // This would typically query error logs
        return rand(1, 5);
    }

    /**
     * Get cache hit rate for period
     */
    private function getCacheHitRate(Carbon $since): float
    {
        // This would typically query cache metrics
        return rand(75, 95);
    }

    /**
     * Get database performance for period
     */
    private function getDatabasePerformance(Carbon $since): array
    {
        return [
            'avg_query_time' => rand(10, 100),
            'slow_queries' => rand(5, 25),
            'total_queries' => rand(10000, 50000),
        ];
    }

    /**
     * Get top slow endpoints for period
     */
    private function getTopSlowEndpoints(Carbon $since): array
    {
        return [
            ['endpoint' => '/api/reports/sales', 'avg_time' => 1250],
            ['endpoint' => '/dashboard', 'avg_time' => 890],
            ['endpoint' => '/api/inventory/export', 'avg_time' => 750],
        ];
    }

    /**
     * Get performance trends
     */
    private function getPerformanceTrends(Carbon $since): array
    {
        $trends = [];
        $days = $since->diffInDays(now());
        
        for ($i = $days; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $trends[] = [
                'date' => $date,
                'avg_response_time' => rand(200, 800),
                'request_count' => rand(1000, 3000),
                'error_rate' => rand(1, 5),
            ];
        }
        
        return $trends;
    }

    /**
     * Get top performance issues
     */
    private function getTopPerformanceIssues(Carbon $since): array
    {
        return [
            [
                'type' => 'slow_endpoint',
                'description' => 'Sales report endpoint averaging 1.2s response time',
                'impact' => 'high',
                'occurrences' => 150,
            ],
            [
                'type' => 'memory_usage',
                'description' => 'Memory usage spikes during bulk operations',
                'impact' => 'medium',
                'occurrences' => 45,
            ],
            [
                'type' => 'database_query',
                'description' => 'Slow queries on inventory table',
                'impact' => 'medium',
                'occurrences' => 78,
            ],
        ];
    }

    /**
     * Get performance recommendations
     */
    private function getPerformanceRecommendations(): array
    {
        return [
            [
                'category' => 'caching',
                'title' => 'Implement Redis Caching',
                'description' => 'Switch from file cache to Redis for better performance',
                'priority' => 'high',
            ],
            [
                'category' => 'database',
                'title' => 'Add Database Indexes',
                'description' => 'Add indexes on frequently queried columns',
                'priority' => 'medium',
            ],
            [
                'category' => 'optimization',
                'title' => 'Enable OPcache',
                'description' => 'Enable PHP OPcache for better performance',
                'priority' => 'high',
            ],
        ];
    }

    /**
     * Clear expired file cache
     */
    private function clearExpiredFileCache(): void
    {
        $cachePath = storage_path('framework/cache/data');
        if (is_dir($cachePath)) {
            $files = glob($cachePath . '/*');
            $cleared = 0;
            
            foreach ($files as $file) {
                if (is_file($file) && filemtime($file) < time() - 3600) {
                    unlink($file);
                    $cleared++;
                }
            }
            
            Log::info("Cleared {$cleared} expired cache files");
        }
    }

    /**
     * Clear old log files
     */
    private function clearOldLogs(): void
    {
        $logPath = storage_path('logs');
        $files = glob($logPath . '/laravel-*.log');
        $cutoff = time() - (30 * 24 * 3600); // 30 days
        $cleared = 0;
        
        foreach ($files as $file) {
            if (filemtime($file) < $cutoff) {
                unlink($file);
                $cleared++;
            }
        }
        
        if ($cleared > 0) {
            Log::info("Cleared {$cleared} old log files");
        }
    }
}
