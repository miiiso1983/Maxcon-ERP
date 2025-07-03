<?php

namespace App\Modules\Performance\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CacheService
{
    // Cache TTL constants (in seconds)
    const TTL_SHORT = 300;      // 5 minutes
    const TTL_MEDIUM = 1800;    // 30 minutes
    const TTL_LONG = 3600;      // 1 hour
    const TTL_DAILY = 86400;    // 24 hours
    const TTL_WEEKLY = 604800;  // 7 days

    // Cache key prefixes
    const PREFIX_DASHBOARD = 'dashboard';
    const PREFIX_REPORTS = 'reports';
    const PREFIX_ANALYTICS = 'analytics';
    const PREFIX_INVENTORY = 'inventory';
    const PREFIX_SALES = 'sales';
    const PREFIX_CUSTOMER = 'customer';
    const PREFIX_SETTINGS = 'settings';

    /**
     * Get or set dashboard cache
     */
    public function getDashboardData(string $tenantId, callable $callback = null): array
    {
        $key = $this->buildKey(self::PREFIX_DASHBOARD, $tenantId, 'overview');
        
        return Cache::remember($key, self::TTL_MEDIUM, function () use ($callback) {
            if ($callback) {
                return $callback();
            }
            
            return $this->generateDashboardData();
        });
    }

    /**
     * Get or set sales analytics cache
     */
    public function getSalesAnalytics(string $tenantId, string $period = 'month', callable $callback = null): array
    {
        $key = $this->buildKey(self::PREFIX_ANALYTICS, $tenantId, 'sales', $period);
        
        return Cache::remember($key, self::TTL_LONG, function () use ($callback, $period) {
            if ($callback) {
                return $callback();
            }
            
            return $this->generateSalesAnalytics($period);
        });
    }

    /**
     * Get or set inventory summary cache
     */
    public function getInventorySummary(string $tenantId, callable $callback = null): array
    {
        $key = $this->buildKey(self::PREFIX_INVENTORY, $tenantId, 'summary');
        
        return Cache::remember($key, self::TTL_MEDIUM, function () use ($callback) {
            if ($callback) {
                return $callback();
            }
            
            return $this->generateInventorySummary();
        });
    }

    /**
     * Get or set customer analytics cache
     */
    public function getCustomerAnalytics(string $tenantId, callable $callback = null): array
    {
        $key = $this->buildKey(self::PREFIX_CUSTOMER, $tenantId, 'analytics');
        
        return Cache::remember($key, self::TTL_LONG, function () use ($callback) {
            if ($callback) {
                return $callback();
            }
            
            return $this->generateCustomerAnalytics();
        });
    }

    /**
     * Get or set report data cache
     */
    public function getReportData(string $tenantId, string $reportType, array $params = [], callable $callback = null): array
    {
        $paramHash = md5(serialize($params));
        $key = $this->buildKey(self::PREFIX_REPORTS, $tenantId, $reportType, $paramHash);
        
        return Cache::remember($key, self::TTL_LONG, function () use ($callback, $reportType, $params) {
            if ($callback) {
                return $callback();
            }
            
            return $this->generateReportData($reportType, $params);
        });
    }

    /**
     * Cache frequently accessed settings
     */
    public function getSettings(string $tenantId, callable $callback = null): array
    {
        $key = $this->buildKey(self::PREFIX_SETTINGS, $tenantId, 'all');
        
        return Cache::remember($key, self::TTL_DAILY, function () use ($callback) {
            if ($callback) {
                return $callback();
            }
            
            return $this->loadSettings();
        });
    }

    /**
     * Invalidate cache by pattern
     */
    public function invalidateByPattern(string $pattern): int
    {
        try {
            if (config('cache.default') === 'redis') {
                return $this->invalidateRedisPattern($pattern);
            } else {
                return $this->invalidateFilePattern($pattern);
            }
        } catch (\Exception $e) {
            Log::error('Cache invalidation failed', [
                'pattern' => $pattern,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Invalidate dashboard cache
     */
    public function invalidateDashboard(string $tenantId): void
    {
        $pattern = $this->buildKey(self::PREFIX_DASHBOARD, $tenantId, '*');
        $this->invalidateByPattern($pattern);
    }

    /**
     * Invalidate sales cache
     */
    public function invalidateSales(string $tenantId): void
    {
        $patterns = [
            $this->buildKey(self::PREFIX_SALES, $tenantId, '*'),
            $this->buildKey(self::PREFIX_ANALYTICS, $tenantId, 'sales', '*'),
            $this->buildKey(self::PREFIX_DASHBOARD, $tenantId, '*'),
        ];
        
        foreach ($patterns as $pattern) {
            $this->invalidateByPattern($pattern);
        }
    }

    /**
     * Invalidate inventory cache
     */
    public function invalidateInventory(string $tenantId): void
    {
        $patterns = [
            $this->buildKey(self::PREFIX_INVENTORY, $tenantId, '*'),
            $this->buildKey(self::PREFIX_DASHBOARD, $tenantId, '*'),
        ];
        
        foreach ($patterns as $pattern) {
            $this->invalidateByPattern($pattern);
        }
    }

    /**
     * Invalidate customer cache
     */
    public function invalidateCustomer(string $tenantId): void
    {
        $patterns = [
            $this->buildKey(self::PREFIX_CUSTOMER, $tenantId, '*'),
            $this->buildKey(self::PREFIX_ANALYTICS, $tenantId, '*'),
            $this->buildKey(self::PREFIX_DASHBOARD, $tenantId, '*'),
        ];
        
        foreach ($patterns as $pattern) {
            $this->invalidateByPattern($pattern);
        }
    }

    /**
     * Warm up critical caches
     */
    public function warmUpCache(string $tenantId): array
    {
        $results = [];
        
        try {
            // Warm up dashboard
            $results['dashboard'] = $this->getDashboardData($tenantId);
            
            // Warm up inventory summary
            $results['inventory'] = $this->getInventorySummary($tenantId);
            
            // Warm up sales analytics
            $results['sales_month'] = $this->getSalesAnalytics($tenantId, 'month');
            $results['sales_year'] = $this->getSalesAnalytics($tenantId, 'year');
            
            // Warm up customer analytics
            $results['customers'] = $this->getCustomerAnalytics($tenantId);
            
            // Warm up settings
            $results['settings'] = $this->getSettings($tenantId);
            
            Log::info('Cache warmed up successfully', ['tenant_id' => $tenantId]);
            
        } catch (\Exception $e) {
            Log::error('Cache warm up failed', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage()
            ]);
        }
        
        return $results;
    }

    /**
     * Get cache statistics
     */
    public function getCacheStats(): array
    {
        try {
            if (config('cache.default') === 'redis') {
                return $this->getRedisStats();
            } else {
                return $this->getFileStats();
            }
        } catch (\Exception $e) {
            Log::error('Failed to get cache stats', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Clear all cache
     */
    public function clearAll(): bool
    {
        try {
            Cache::flush();
            Log::info('All cache cleared successfully');
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to clear cache', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Build cache key
     */
    private function buildKey(string ...$parts): string
    {
        return 'maxcon_erp:' . implode(':', array_filter($parts));
    }

    /**
     * Invalidate Redis cache by pattern
     */
    private function invalidateRedisPattern(string $pattern): int
    {
        $redis = Redis::connection();
        $keys = $redis->keys($pattern);
        
        if (empty($keys)) {
            return 0;
        }
        
        return $redis->del($keys);
    }

    /**
     * Invalidate file cache by pattern (simplified)
     */
    private function invalidateFilePattern(string $pattern): int
    {
        // For file cache, we'll clear all cache as pattern matching is complex
        Cache::flush();
        return 1;
    }

    /**
     * Get Redis cache statistics
     */
    private function getRedisStats(): array
    {
        $redis = Redis::connection();
        $info = $redis->info();
        
        return [
            'driver' => 'redis',
            'memory_used' => $info['used_memory_human'] ?? 'N/A',
            'total_keys' => $redis->dbsize(),
            'hits' => $info['keyspace_hits'] ?? 0,
            'misses' => $info['keyspace_misses'] ?? 0,
            'hit_rate' => $this->calculateHitRate($info['keyspace_hits'] ?? 0, $info['keyspace_misses'] ?? 0),
        ];
    }

    /**
     * Get file cache statistics
     */
    private function getFileStats(): array
    {
        $cachePath = storage_path('framework/cache/data');
        $size = 0;
        $files = 0;
        
        if (is_dir($cachePath)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($cachePath)
            );
            
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $size += $file->getSize();
                    $files++;
                }
            }
        }
        
        return [
            'driver' => 'file',
            'total_files' => $files,
            'total_size' => $this->formatBytes($size),
            'cache_path' => $cachePath,
        ];
    }

    /**
     * Calculate cache hit rate
     */
    private function calculateHitRate(int $hits, int $misses): float
    {
        $total = $hits + $misses;
        return $total > 0 ? round(($hits / $total) * 100, 2) : 0;
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
     * Generate dashboard data (fallback)
     */
    private function generateDashboardData(): array
    {
        return [
            'total_sales' => 0,
            'total_customers' => 0,
            'total_products' => 0,
            'low_stock_items' => 0,
            'generated_at' => now()->toISOString(),
        ];
    }

    /**
     * Generate sales analytics (fallback)
     */
    private function generateSalesAnalytics(string $period): array
    {
        return [
            'period' => $period,
            'total_sales' => 0,
            'total_orders' => 0,
            'average_order_value' => 0,
            'generated_at' => now()->toISOString(),
        ];
    }

    /**
     * Generate inventory summary (fallback)
     */
    private function generateInventorySummary(): array
    {
        return [
            'total_products' => 0,
            'total_value' => 0,
            'low_stock_count' => 0,
            'out_of_stock_count' => 0,
            'generated_at' => now()->toISOString(),
        ];
    }

    /**
     * Generate customer analytics (fallback)
     */
    private function generateCustomerAnalytics(): array
    {
        return [
            'total_customers' => 0,
            'new_customers_this_month' => 0,
            'top_customers' => [],
            'generated_at' => now()->toISOString(),
        ];
    }

    /**
     * Generate report data (fallback)
     */
    private function generateReportData(string $reportType, array $params): array
    {
        return [
            'report_type' => $reportType,
            'parameters' => $params,
            'data' => [],
            'generated_at' => now()->toISOString(),
        ];
    }

    /**
     * Load settings (fallback)
     */
    private function loadSettings(): array
    {
        return [
            'company_name' => config('app.name'),
            'currency' => 'IQD',
            'timezone' => config('app.timezone'),
            'loaded_at' => now()->toISOString(),
        ];
    }
}
