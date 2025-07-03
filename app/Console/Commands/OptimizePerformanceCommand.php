<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Performance\Services\CacheService;
use App\Modules\Performance\Services\DatabaseOptimizationService;
use App\Modules\Performance\Services\PerformanceMonitoringService;
use Symfony\Component\Console\Command\Command as SymfonyCommand;


class OptimizePerformanceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'performance:optimize
                            {--cache : Only optimize cache}
                            {--database : Only optimize database}
                            {--cleanup : Only cleanup old data}
                            {--warmup : Only warm up cache}
                            {--days=30 : Days to keep for cleanup}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimize application performance by clearing cache, optimizing database, and cleaning up old data';

    protected CacheService $cacheService;
    protected DatabaseOptimizationService $dbOptimizer;
    protected PerformanceMonitoringService $monitor;

    public function __construct(
        CacheService $cacheService,
        DatabaseOptimizationService $dbOptimizer,
        PerformanceMonitoringService $monitor
    ) {
        parent::__construct();
        $this->cacheService = $cacheService;
        $this->dbOptimizer = $dbOptimizer;
        $this->monitor = $monitor;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Starting Performance Optimization...');
        $this->newLine();

        $startTime = microtime(true);
        $optimizations = [];

        try {
            // Cache optimization
            if ($this->option('cache') || !$this->hasSpecificOptions()) {
                $this->optimizeCache();
                $optimizations[] = 'Cache optimization';
            }

            // Database optimization
            if ($this->option('database') || !$this->hasSpecificOptions()) {
                $this->optimizeDatabase();
                $optimizations[] = 'Database optimization';
            }

            // Data cleanup
            if ($this->option('cleanup') || !$this->hasSpecificOptions()) {
                $this->cleanupOldData();
                $optimizations[] = 'Data cleanup';
            }

            // Cache warmup
            if ($this->option('warmup') || !$this->hasSpecificOptions()) {
                $this->warmupCache();
                $optimizations[] = 'Cache warmup';
            }

            $duration = round((microtime(true) - $startTime) * 1000, 2);

            $this->newLine();
            $this->info("✅ Performance optimization completed in {$duration}ms");
            $this->info('📊 Optimizations performed: ' . implode(', ', $optimizations));

            // Show performance summary
            $this->showPerformanceSummary();

        } catch (\Exception $e) {
            $this->error('❌ Performance optimization failed: ' . $e->getMessage());
            return SymfonyCommand::FAILURE;
        }

        return SymfonyCommand::SUCCESS;
    }

    /**
     * Optimize cache
     */
    private function optimizeCache(): void
    {
        $this->info('🗄️  Optimizing cache...');

        // Clear all caches
        $this->call('cache:clear');
        $this->call('config:clear');
        $this->call('route:clear');
        $this->call('view:clear');

        // Clear application cache
        $this->cacheService->clearAll();

        $this->line('   ✓ All caches cleared');
    }

    /**
     * Optimize database
     */
    private function optimizeDatabase(): void
    {
        $this->info('🗃️  Optimizing database...');

        // Optimize tables
        $results = $this->dbOptimizer->optimizeTables();
        $optimizedCount = count(array_filter($results, fn($result) => $result === 'optimized'));

        $this->line("   ✓ Optimized {$optimizedCount} database tables");

        // Show health score
        $health = $this->dbOptimizer->getHealthScore();
        $this->line("   ✓ Database health score: {$health['score']}/100 ({$health['status']})");
    }

    /**
     * Cleanup old data
     */
    private function cleanupOldData(): void
    {
        $this->info('🧹 Cleaning up old data...');

        $daysToKeep = (int) $this->option('days');
        $results = $this->dbOptimizer->cleanupOldData($daysToKeep);

        $totalCleaned = array_sum($results);
        $this->line("   ✓ Cleaned up {$totalCleaned} old records (keeping last {$daysToKeep} days)");

        foreach ($results as $table => $count) {
            if ($count > 0) {
                $this->line("     - {$table}: {$count} records");
            }
        }
    }

    /**
     * Warm up cache
     */
    private function warmupCache(): void
    {
        $this->info('🔥 Warming up cache...');

        // Get tenant ID (simplified for this example)
        $tenantId = 'default';

        $results = $this->cacheService->warmUpCache($tenantId);
        $warmedCount = count($results);

        $this->line("   ✓ Warmed up {$warmedCount} cache entries");

        foreach ($results as $key => $data) {
            if (is_array($data)) {
                $this->line("     - {$key}: " . count($data) . " items");
            }
        }
    }

    /**
     * Show performance summary
     */
    private function showPerformanceSummary(): void
    {
        $this->newLine();
        $this->info('📈 Performance Summary:');

        try {
            // System metrics
            $systemMetrics = $this->monitor->getSystemMetrics();
            $this->line("   Memory Usage: {$systemMetrics['memory']['usage_percent']}% ({$systemMetrics['memory']['used_formatted']})");
            $this->line("   Disk Usage: {$systemMetrics['disk']['usage_percent']}% ({$systemMetrics['disk']['used_formatted']})");

            // Cache stats
            $cacheStats = $this->cacheService->getCacheStats();
            $this->line("   Cache Driver: " . ucfirst($cacheStats['driver'] ?? 'unknown'));

            // Database health
            $dbHealth = $this->dbOptimizer->getHealthScore();
            $this->line("   Database Health: {$dbHealth['score']}/100 ({$dbHealth['status']})");

            // Performance alerts
            $alerts = $this->monitor->getPerformanceAlerts();
            if (!empty($alerts)) {
                $this->newLine();
                $this->warn('⚠️  Performance Alerts:');
                foreach ($alerts as $alert) {
                    $this->line("   - {$alert['message']} ({$alert['severity']})");
                }
            } else {
                $this->line("   No performance alerts");
            }

        } catch (\Exception $e) {
            $this->warn('Could not retrieve performance summary: ' . $e->getMessage());
        }
    }

    /**
     * Check if specific options are provided
     */
    private function hasSpecificOptions(): bool
    {
        return $this->option('cache') ||
               $this->option('database') ||
               $this->option('cleanup') ||
               $this->option('warmup');
    }
}
