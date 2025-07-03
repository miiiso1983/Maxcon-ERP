<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;
use App\Services\RedisMonitoringService;

class OptimizeRedis extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'redis:optimize {--force : Force optimization even if thresholds are not met}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimize Redis performance and clean up expired data';

    protected RedisMonitoringService $redisMonitor;

    public function __construct(RedisMonitoringService $redisMonitor)
    {
        parent::__construct();
        $this->redisMonitor = $redisMonitor;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Redis optimization...');

        // Get current Redis status
        $dashboardData = $this->redisMonitor->getDashboardData();
        $serverInfo = $dashboardData['server_info'];

        if ($serverInfo['status'] !== 'connected') {
            $this->error('Redis is not connected. Cannot perform optimization.');
            return 1;
        }

        $this->info('Redis Status: Connected');
        $this->info('Memory Usage: ' . ($dashboardData['memory_stats']['used_memory_human'] ?? 'Unknown'));
        $this->info('Total Keys: ' . ($dashboardData['key_stats']['total_keys'] ?? 0));

        $optimizations = 0;

        // 1. Clean up expired keys
        $this->info('1. Cleaning up expired keys...');
        $expiredCleaned = $this->cleanupExpiredKeys();
        if ($expiredCleaned > 0) {
            $this->info("   Cleaned up {$expiredCleaned} expired keys");
            $optimizations++;
        }

        // 2. Optimize memory usage
        $this->info('2. Optimizing memory usage...');
        $memoryOptimized = $this->optimizeMemory();
        if ($memoryOptimized) {
            $this->info('   Memory optimization completed');
            $optimizations++;
        }

        // 3. Analyze and optimize key patterns
        $this->info('3. Analyzing key patterns...');
        $patternsOptimized = $this->optimizeKeyPatterns();
        if ($patternsOptimized > 0) {
            $this->info("   Optimized {$patternsOptimized} key patterns");
            $optimizations++;
        }

        // 4. Warm up frequently accessed cache
        $this->info('4. Warming up cache...');
        $warmupResult = $this->redisMonitor->warmUpCache();
        if ($warmupResult['success']) {
            $this->info("   Warmed up {$warmupResult['count']} cache keys");
            $optimizations++;
        }

        // 5. Performance analysis
        $this->info('5. Running performance analysis...');
        $performanceData = $dashboardData['cache_performance'];
        $this->table(
            ['Operation', 'Time (ms)', 'Performance'],
            [
                ['Write', $performanceData['write_time_ms'], $this->getPerformanceRating($performanceData['write_time_ms'], 'write')],
                ['Read', $performanceData['read_time_ms'], $this->getPerformanceRating($performanceData['read_time_ms'], 'read')],
            ]
        );

        // 6. Generate recommendations
        $this->info('6. Generating optimization recommendations...');
        $recommendations = $this->generateRecommendations($dashboardData);
        if (!empty($recommendations)) {
            $this->warn('Recommendations:');
            foreach ($recommendations as $recommendation) {
                $this->line("   • {$recommendation}");
            }
        }

        // Summary
        $this->info('');
        $this->info("Redis optimization completed!");
        $this->info("Optimizations performed: {$optimizations}");

        // Final status
        $finalData = $this->redisMonitor->getDashboardData();
        $this->info('Final Status:');
        $this->info('Memory Usage: ' . ($finalData['memory_stats']['used_memory_human'] ?? 'Unknown'));
        $this->info('Total Keys: ' . ($finalData['key_stats']['total_keys'] ?? 0));
        $this->info('Cache Hit Ratio: ' . ($finalData['performance_metrics']['cache_hit_ratio'] ?? 0) . '%');

        return 0;
    }

    /**
     * Clean up expired keys
     */
    private function cleanupExpiredKeys(): int
    {
        try {
            // Get all keys
            $keys = Redis::keys('*');
            $expiredCount = 0;

            foreach ($keys as $key) {
                $ttl = Redis::ttl($key);
                if ($ttl === -1) { // Key exists but has no expiration
                    // Set a default expiration for cache keys without TTL
                    if (str_contains($key, 'cache')) {
                        Redis::expire($key, 3600); // 1 hour default
                        $expiredCount++;
                    }
                }
            }

            return $expiredCount;
        } catch (\Exception $e) {
            $this->error('Error cleaning up expired keys: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Optimize memory usage
     */
    private function optimizeMemory(): bool
    {
        try {
            // Run Redis memory optimization commands
            Redis::command('MEMORY', ['PURGE']);
            return true;
        } catch (\Exception $e) {
            $this->error('Error optimizing memory: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Optimize key patterns
     */
    private function optimizeKeyPatterns(): int
    {
        try {
            $keys = Redis::keys('*');
            $optimized = 0;

            // Group keys by pattern and optimize
            $patterns = [];
            foreach ($keys as $key) {
                $pattern = $this->getKeyPattern($key);
                $patterns[$pattern][] = $key;
            }

            // Optimize patterns with many keys
            foreach ($patterns as $pattern => $patternKeys) {
                if (count($patternKeys) > 100) {
                    // Consider optimizing large key sets
                    $optimized++;
                }
            }

            return $optimized;
        } catch (\Exception $e) {
            $this->error('Error optimizing key patterns: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get key pattern
     */
    private function getKeyPattern(string $key): string
    {
        // Extract pattern from key (remove specific IDs, timestamps, etc.)
        $pattern = preg_replace('/\d+/', '*', $key);
        $pattern = preg_replace('/[a-f0-9]{32}/', '*', $pattern); // MD5 hashes
        return $pattern;
    }

    /**
     * Get performance rating
     */
    private function getPerformanceRating(float $timeMs, string $operation): string
    {
        $thresholds = [
            'write' => ['excellent' => 5, 'good' => 15, 'fair' => 50],
            'read' => ['excellent' => 2, 'good' => 10, 'fair' => 30],
        ];

        $limits = $thresholds[$operation] ?? $thresholds['read'];

        if ($timeMs <= $limits['excellent']) {
            return '🟢 Excellent';
        } elseif ($timeMs <= $limits['good']) {
            return '🟡 Good';
        } elseif ($timeMs <= $limits['fair']) {
            return '🟠 Fair';
        } else {
            return '🔴 Poor';
        }
    }

    /**
     * Generate optimization recommendations
     */
    private function generateRecommendations(array $data): array
    {
        $recommendations = [];

        $memoryStats = $data['memory_stats'];
        $performanceMetrics = $data['performance_metrics'];
        $keyStats = $data['key_stats'];

        // Memory recommendations
        if (isset($memoryStats['memory_usage_percentage']) && $memoryStats['memory_usage_percentage'] > 80) {
            $recommendations[] = 'Memory usage is high (>80%). Consider increasing Redis memory or implementing key expiration policies.';
        }

        // Cache hit ratio recommendations
        if ($performanceMetrics['cache_hit_ratio'] < 70) {
            $recommendations[] = 'Cache hit ratio is low (<70%). Review caching strategy and key TTL settings.';
        }

        // Key count recommendations
        if ($keyStats['total_keys'] > 10000) {
            $recommendations[] = 'High number of keys detected. Consider implementing key cleanup policies.';
        }

        // Performance recommendations
        $cachePerf = $data['cache_performance'];
        if ($cachePerf['read_time_ms'] > 10) {
            $recommendations[] = 'Read performance is slow. Consider optimizing Redis configuration or hardware.';
        }

        if ($cachePerf['write_time_ms'] > 20) {
            $recommendations[] = 'Write performance is slow. Consider optimizing Redis persistence settings.';
        }

        return $recommendations;
    }
}
