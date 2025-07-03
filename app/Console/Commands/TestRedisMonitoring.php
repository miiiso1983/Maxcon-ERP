<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\RedisMonitoringService;

class TestRedisMonitoring extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:redis-monitoring';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Redis monitoring service functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Redis Monitoring Service...');

        $redisMonitor = app(RedisMonitoringService::class);

        // Get dashboard data
        $this->info('1. Getting Redis dashboard data...');
        $dashboardData = $redisMonitor->getDashboardData();

        $this->table(
            ['Metric', 'Value'],
            [
                ['Status', $dashboardData['server_info']['status']],
                ['Version', $dashboardData['server_info']['version'] ?? 'N/A'],
                ['Memory Usage', $dashboardData['memory_stats']['used_memory_human'] ?? 'N/A'],
                ['Total Keys', $dashboardData['key_stats']['total_keys'] ?? 0],
                ['Cache Hit Ratio', $dashboardData['performance_metrics']['cache_hit_ratio'] . '%'],
                ['Operations/sec', $dashboardData['performance_metrics']['operations_per_second'] ?? 0],
                ['Connected Clients', $dashboardData['performance_metrics']['connected_clients'] ?? 0],
                ['Uptime (hours)', $dashboardData['performance_metrics']['uptime_hours'] ?? 0],
            ]
        );

        // Test cache performance
        $this->info('2. Testing cache performance...');
        $performance = $dashboardData['cache_performance'];
        $this->table(
            ['Operation', 'Time (ms)', 'Ops/sec'],
            [
                ['Write (100 keys)', $performance['write_time_ms'], $performance['write_ops_per_sec']],
                ['Read (100 keys)', $performance['read_time_ms'], $performance['read_ops_per_sec']],
            ]
        );

        // Show key distribution
        if (isset($dashboardData['key_stats']['keys_by_type'])) {
            $this->info('3. Key distribution:');
            $keyTypes = $dashboardData['key_stats']['keys_by_type'];
            $this->table(
                ['Key Type', 'Count'],
                [
                    ['Cache', $keyTypes['cache'] ?? 0],
                    ['Session', $keyTypes['session'] ?? 0],
                    ['Queue', $keyTypes['queue'] ?? 0],
                    ['Other', $keyTypes['other'] ?? 0],
                ]
            );
        }

        $this->info('Redis monitoring test completed successfully!');
    }
}
