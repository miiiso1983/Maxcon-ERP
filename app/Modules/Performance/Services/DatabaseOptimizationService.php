<?php

namespace App\Modules\Performance\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class DatabaseOptimizationService
{
    /**
     * Analyze database performance
     */
    public function analyzePerformance(): array
    {
        $analysis = [
            'slow_queries' => $this->getSlowQueries(),
            'table_sizes' => $this->getTableSizes(),
            'index_usage' => $this->getIndexUsage(),
            'connection_stats' => $this->getConnectionStats(),
            'recommendations' => [],
        ];

        $analysis['recommendations'] = $this->generateRecommendations($analysis);

        return $analysis;
    }

    /**
     * Get slow queries
     */
    public function getSlowQueries(int $limit = 10): array
    {
        try {
            // Enable slow query log analysis
            $slowQueries = DB::select("
                SELECT 
                    sql_text,
                    exec_count,
                    avg_timer_wait/1000000000 as avg_time_seconds,
                    sum_timer_wait/1000000000 as total_time_seconds,
                    sum_rows_examined,
                    sum_rows_sent
                FROM performance_schema.events_statements_summary_by_digest 
                WHERE avg_timer_wait > 1000000000
                ORDER BY avg_timer_wait DESC 
                LIMIT ?
            ", [$limit]);

            return array_map(function ($query) {
                return [
                    'query' => $this->sanitizeQuery($query->sql_text),
                    'execution_count' => $query->exec_count,
                    'avg_time' => round($query->avg_time_seconds, 4),
                    'total_time' => round($query->total_time_seconds, 4),
                    'rows_examined' => $query->sum_rows_examined,
                    'rows_sent' => $query->sum_rows_sent,
                ];
            }, $slowQueries);

        } catch (\Exception $e) {
            Log::warning('Could not retrieve slow queries', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get table sizes
     */
    public function getTableSizes(): array
    {
        try {
            $tables = DB::select("
                SELECT 
                    table_name,
                    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb,
                    table_rows,
                    ROUND((data_length / 1024 / 1024), 2) AS data_mb,
                    ROUND((index_length / 1024 / 1024), 2) AS index_mb
                FROM information_schema.TABLES 
                WHERE table_schema = DATABASE()
                ORDER BY (data_length + index_length) DESC
            ");

            return array_map(function ($table) {
                return [
                    'table' => $table->table_name,
                    'total_size_mb' => $table->size_mb,
                    'data_size_mb' => $table->data_mb,
                    'index_size_mb' => $table->index_mb,
                    'row_count' => $table->table_rows,
                ];
            }, $tables);

        } catch (\Exception $e) {
            Log::warning('Could not retrieve table sizes', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get index usage statistics
     */
    public function getIndexUsage(): array
    {
        try {
            $indexes = DB::select("
                SELECT 
                    t.table_name,
                    t.index_name,
                    t.column_name,
                    s.rows_read,
                    s.rows_inserted,
                    s.rows_updated,
                    s.rows_deleted
                FROM information_schema.statistics t
                LEFT JOIN performance_schema.table_io_waits_summary_by_index_usage s 
                    ON t.table_schema = s.object_schema 
                    AND t.table_name = s.object_name 
                    AND t.index_name = s.index_name
                WHERE t.table_schema = DATABASE()
                ORDER BY t.table_name, t.index_name
            ");

            return array_map(function ($index) {
                return [
                    'table' => $index->table_name,
                    'index' => $index->index_name,
                    'column' => $index->column_name,
                    'reads' => $index->rows_read ?? 0,
                    'inserts' => $index->rows_inserted ?? 0,
                    'updates' => $index->rows_updated ?? 0,
                    'deletes' => $index->rows_deleted ?? 0,
                ];
            }, $indexes);

        } catch (\Exception $e) {
            Log::warning('Could not retrieve index usage', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get connection statistics
     */
    public function getConnectionStats(): array
    {
        try {
            $stats = DB::select("SHOW STATUS LIKE 'Connections'");
            $maxConnections = DB::select("SHOW VARIABLES LIKE 'max_connections'");
            $threadsConnected = DB::select("SHOW STATUS LIKE 'Threads_connected'");

            return [
                'total_connections' => $stats[0]->Value ?? 0,
                'max_connections' => $maxConnections[0]->Value ?? 0,
                'current_connections' => $threadsConnected[0]->Value ?? 0,
                'connection_usage_percent' => $maxConnections[0]->Value > 0 
                    ? round(($threadsConnected[0]->Value / $maxConnections[0]->Value) * 100, 2) 
                    : 0,
            ];

        } catch (\Exception $e) {
            Log::warning('Could not retrieve connection stats', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Optimize database tables
     */
    public function optimizeTables(): array
    {
        $results = [];
        
        try {
            $tables = $this->getAllTables();
            
            foreach ($tables as $table) {
                try {
                    DB::statement("OPTIMIZE TABLE `{$table}`");
                    $results[$table] = 'optimized';
                } catch (\Exception $e) {
                    $results[$table] = 'failed: ' . $e->getMessage();
                    Log::warning("Failed to optimize table {$table}", ['error' => $e->getMessage()]);
                }
            }

        } catch (\Exception $e) {
            Log::error('Database optimization failed', ['error' => $e->getMessage()]);
        }

        return $results;
    }

    /**
     * Analyze and suggest missing indexes
     */
    public function suggestIndexes(): array
    {
        $suggestions = [];

        try {
            // Analyze foreign key columns without indexes
            $foreignKeys = $this->getForeignKeysWithoutIndexes();
            foreach ($foreignKeys as $fk) {
                $suggestions[] = [
                    'type' => 'foreign_key_index',
                    'table' => $fk['table'],
                    'column' => $fk['column'],
                    'reason' => 'Foreign key column without index',
                    'sql' => "ALTER TABLE `{$fk['table']}` ADD INDEX `idx_{$fk['column']}` (`{$fk['column']}`);",
                ];
            }

            // Analyze frequently queried columns
            $frequentColumns = $this->getFrequentlyQueriedColumns();
            foreach ($frequentColumns as $column) {
                $suggestions[] = [
                    'type' => 'query_optimization',
                    'table' => $column['table'],
                    'column' => $column['column'],
                    'reason' => 'Frequently queried column in WHERE clauses',
                    'sql' => "ALTER TABLE `{$column['table']}` ADD INDEX `idx_{$column['column']}` (`{$column['column']}`);",
                ];
            }

        } catch (\Exception $e) {
            Log::warning('Could not generate index suggestions', ['error' => $e->getMessage()]);
        }

        return $suggestions;
    }

    /**
     * Clean up old data
     */
    public function cleanupOldData(int $daysToKeep = 90): array
    {
        $results = [];

        try {
            $cutoffDate = now()->subDays($daysToKeep);

            // Clean up old activity logs
            $deletedLogs = DB::table('activity_log')
                ->where('created_at', '<', $cutoffDate)
                ->delete();
            $results['activity_logs'] = $deletedLogs;

            // Clean up old failed jobs
            $deletedJobs = DB::table('failed_jobs')
                ->where('failed_at', '<', $cutoffDate)
                ->delete();
            $results['failed_jobs'] = $deletedJobs;

            // Clean up old sessions
            $deletedSessions = DB::table('sessions')
                ->where('last_activity', '<', $cutoffDate->timestamp)
                ->delete();
            $results['sessions'] = $deletedSessions;

            // Clean up old cache entries (if using database cache)
            if (config('cache.default') === 'database') {
                $deletedCache = DB::table('cache')
                    ->where('expiration', '<', now()->timestamp)
                    ->delete();
                $results['cache_entries'] = $deletedCache;
            }

            Log::info('Database cleanup completed', $results);

        } catch (\Exception $e) {
            Log::error('Database cleanup failed', ['error' => $e->getMessage()]);
        }

        return $results;
    }

    /**
     * Get database health score
     */
    public function getHealthScore(): array
    {
        $score = 100;
        $issues = [];

        try {
            // Check slow queries
            $slowQueries = $this->getSlowQueries(5);
            if (count($slowQueries) > 0) {
                $score -= 20;
                $issues[] = 'Slow queries detected';
            }

            // Check table sizes
            $tableSizes = $this->getTableSizes();
            $largeTables = array_filter($tableSizes, fn($table) => $table['total_size_mb'] > 1000);
            if (count($largeTables) > 0) {
                $score -= 10;
                $issues[] = 'Large tables detected (>1GB)';
            }

            // Check connection usage
            $connectionStats = $this->getConnectionStats();
            if ($connectionStats['connection_usage_percent'] > 80) {
                $score -= 15;
                $issues[] = 'High connection usage';
            }

            // Check for missing indexes
            $indexSuggestions = $this->suggestIndexes();
            if (count($indexSuggestions) > 5) {
                $score -= 15;
                $issues[] = 'Multiple missing indexes detected';
            }

        } catch (\Exception $e) {
            $score -= 30;
            $issues[] = 'Error analyzing database health';
            Log::error('Database health check failed', ['error' => $e->getMessage()]);
        }

        return [
            'score' => max(0, $score),
            'status' => $this->getHealthStatus($score),
            'issues' => $issues,
            'checked_at' => now()->toISOString(),
        ];
    }

    /**
     * Get all tables in the database
     */
    private function getAllTables(): array
    {
        $tables = DB::select("SHOW TABLES");
        $databaseName = DB::getDatabaseName();
        $tableKey = "Tables_in_{$databaseName}";

        return array_map(fn($table) => $table->$tableKey, $tables);
    }

    /**
     * Get foreign keys without indexes
     */
    private function getForeignKeysWithoutIndexes(): array
    {
        try {
            return DB::select("
                SELECT 
                    kcu.table_name as `table`,
                    kcu.column_name as `column`
                FROM information_schema.key_column_usage kcu
                LEFT JOIN information_schema.statistics s 
                    ON kcu.table_name = s.table_name 
                    AND kcu.column_name = s.column_name
                    AND s.seq_in_index = 1
                WHERE kcu.table_schema = DATABASE()
                    AND kcu.referenced_table_name IS NOT NULL
                    AND s.column_name IS NULL
            ");
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get frequently queried columns (simplified analysis)
     */
    private function getFrequentlyQueriedColumns(): array
    {
        // This is a simplified version - in production, you'd analyze query logs
        $commonColumns = [
            ['table' => 'sales', 'column' => 'customer_id'],
            ['table' => 'sales', 'column' => 'sale_date'],
            ['table' => 'products', 'column' => 'category_id'],
            ['table' => 'inventory_movements', 'column' => 'product_id'],
        ];

        return array_filter($commonColumns, function ($column) {
            return Schema::hasTable($column['table']) && 
                   Schema::hasColumn($column['table'], $column['column']);
        });
    }

    /**
     * Sanitize query for display
     */
    private function sanitizeQuery(string $query): string
    {
        // Remove sensitive data and normalize
        $query = preg_replace('/\s+/', ' ', $query);
        $query = str_replace(['?', '"', "'"], ['[?]', '["]', "[']"], $query);
        return substr($query, 0, 200) . (strlen($query) > 200 ? '...' : '');
    }

    /**
     * Get health status based on score
     */
    private function getHealthStatus(int $score): string
    {
        if ($score >= 90) return 'excellent';
        if ($score >= 75) return 'good';
        if ($score >= 60) return 'fair';
        if ($score >= 40) return 'poor';
        return 'critical';
    }

    /**
     * Generate optimization recommendations
     */
    private function generateRecommendations(array $analysis): array
    {
        $recommendations = [];

        // Slow query recommendations
        if (!empty($analysis['slow_queries'])) {
            $recommendations[] = [
                'type' => 'performance',
                'priority' => 'high',
                'title' => 'Optimize Slow Queries',
                'description' => 'Found ' . count($analysis['slow_queries']) . ' slow queries that need optimization.',
                'action' => 'Review and optimize slow queries, add appropriate indexes.',
            ];
        }

        // Large table recommendations
        $largeTables = array_filter($analysis['table_sizes'], fn($table) => $table['total_size_mb'] > 500);
        if (!empty($largeTables)) {
            $recommendations[] = [
                'type' => 'storage',
                'priority' => 'medium',
                'title' => 'Large Tables Detected',
                'description' => 'Found ' . count($largeTables) . ' tables larger than 500MB.',
                'action' => 'Consider archiving old data or partitioning large tables.',
            ];
        }

        // Index recommendations
        $indexSuggestions = $this->suggestIndexes();
        if (!empty($indexSuggestions)) {
            $recommendations[] = [
                'type' => 'indexing',
                'priority' => 'medium',
                'title' => 'Missing Indexes',
                'description' => 'Found ' . count($indexSuggestions) . ' potential missing indexes.',
                'action' => 'Add recommended indexes to improve query performance.',
            ];
        }

        return $recommendations;
    }
}
