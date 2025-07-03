<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;

class RedisMonitoringService
{
    /**
     * Get Redis server information
     */
    public function getServerInfo(): array
    {
        try {
            $info = Redis::info();
            
            return [
                'status' => 'connected',
                'version' => $info['redis_version'] ?? 'unknown',
                'uptime' => $info['uptime_in_seconds'] ?? 0,
                'connected_clients' => $info['connected_clients'] ?? 0,
                'used_memory' => $info['used_memory'] ?? 0,
                'used_memory_human' => $info['used_memory_human'] ?? '0B',
                'used_memory_peak' => $info['used_memory_peak'] ?? 0,
                'used_memory_peak_human' => $info['used_memory_peak_human'] ?? '0B',
                'total_commands_processed' => $info['total_commands_processed'] ?? 0,
                'instantaneous_ops_per_sec' => $info['instantaneous_ops_per_sec'] ?? 0,
                'keyspace_hits' => $info['keyspace_hits'] ?? 0,
                'keyspace_misses' => $info['keyspace_misses'] ?? 0,
                'expired_keys' => $info['expired_keys'] ?? 0,
                'evicted_keys' => $info['evicted_keys'] ?? 0,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get cache hit ratio
     */
    public function getCacheHitRatio(): float
    {
        $info = $this->getServerInfo();
        
        if ($info['status'] === 'error') {
            return 0.0;
        }

        $hits = $info['keyspace_hits'] ?? 0;
        $misses = $info['keyspace_misses'] ?? 0;
        $total = $hits + $misses;

        return $total > 0 ? round(($hits / $total) * 100, 2) : 0.0;
    }

    /**
     * Get memory usage statistics
     */
    public function getMemoryStats(): array
    {
        $info = $this->getServerInfo();
        
        if ($info['status'] === 'error') {
            return [];
        }

        return [
            'used_memory' => $info['used_memory'],
            'used_memory_human' => $info['used_memory_human'],
            'used_memory_peak' => $info['used_memory_peak'],
            'used_memory_peak_human' => $info['used_memory_peak_human'],
            'memory_usage_percentage' => $this->calculateMemoryUsagePercentage($info),
        ];
    }

    /**
     * Get performance metrics
     */
    public function getPerformanceMetrics(): array
    {
        $info = $this->getServerInfo();
        
        if ($info['status'] === 'error') {
            return [];
        }

        return [
            'operations_per_second' => $info['instantaneous_ops_per_sec'],
            'total_commands' => $info['total_commands_processed'],
            'connected_clients' => $info['connected_clients'],
            'cache_hit_ratio' => $this->getCacheHitRatio(),
            'uptime_hours' => round($info['uptime'] / 3600, 2),
        ];
    }

    /**
     * Get key statistics
     */
    public function getKeyStats(): array
    {
        try {
            $keys = Redis::keys('*');
            $keyCount = count($keys);
            
            $keysByType = [
                'cache' => 0,
                'session' => 0,
                'queue' => 0,
                'other' => 0,
            ];

            foreach ($keys as $key) {
                if (str_contains($key, '_cache')) {
                    $keysByType['cache']++;
                } elseif (str_contains($key, 'session')) {
                    $keysByType['session']++;
                } elseif (str_contains($key, 'queue')) {
                    $keysByType['queue']++;
                } else {
                    $keysByType['other']++;
                }
            }

            return [
                'total_keys' => $keyCount,
                'keys_by_type' => $keysByType,
                'expired_keys' => $this->getServerInfo()['expired_keys'] ?? 0,
                'evicted_keys' => $this->getServerInfo()['evicted_keys'] ?? 0,
            ];
        } catch (\Exception $e) {
            return [
                'total_keys' => 0,
                'keys_by_type' => [],
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Test cache performance
     */
    public function testCachePerformance(): array
    {
        $results = [];
        
        // Test write performance
        $writeStart = microtime(true);
        for ($i = 0; $i < 100; $i++) {
            Cache::put("perf_test_$i", "test_value_$i", 60);
        }
        $writeTime = (microtime(true) - $writeStart) * 1000;
        
        // Test read performance
        $readStart = microtime(true);
        for ($i = 0; $i < 100; $i++) {
            Cache::get("perf_test_$i");
        }
        $readTime = (microtime(true) - $readStart) * 1000;
        
        // Clean up test keys
        for ($i = 0; $i < 100; $i++) {
            Cache::forget("perf_test_$i");
        }
        
        return [
            'write_time_ms' => round($writeTime, 2),
            'read_time_ms' => round($readTime, 2),
            'write_ops_per_sec' => round(100 / ($writeTime / 1000), 2),
            'read_ops_per_sec' => round(100 / ($readTime / 1000), 2),
        ];
    }

    /**
     * Get comprehensive Redis dashboard data
     */
    public function getDashboardData(): array
    {
        return [
            'server_info' => $this->getServerInfo(),
            'memory_stats' => $this->getMemoryStats(),
            'performance_metrics' => $this->getPerformanceMetrics(),
            'key_stats' => $this->getKeyStats(),
            'cache_performance' => $this->testCachePerformance(),
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * Clear all cache data
     */
    public function clearAllCache(): bool
    {
        try {
            Cache::flush();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Warm up cache with common data
     */
    public function warmUpCache(): array
    {
        $warmedKeys = [];
        
        try {
            // Add common cache warming logic here
            Cache::put('cache_warmed_at', now(), 3600);
            $warmedKeys[] = 'cache_warmed_at';
            
            // You can add more cache warming logic here
            // For example: pre-load frequently accessed data
            
            return [
                'success' => true,
                'warmed_keys' => $warmedKeys,
                'count' => count($warmedKeys)
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Calculate memory usage percentage
     */
    private function calculateMemoryUsagePercentage(array $info): float
    {
        // This is a simplified calculation
        // In production, you might want to compare against system memory or Redis maxmemory
        $used = $info['used_memory'] ?? 0;
        $peak = $info['used_memory_peak'] ?? 1;
        
        return $peak > 0 ? round(($used / $peak) * 100, 2) : 0.0;
    }
}
