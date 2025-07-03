<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Redis Optimization Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains Redis optimization settings for the MAXCON ERP system.
    | These settings are designed to maximize Redis performance for caching,
    | sessions, and queue operations.
    |
    */

    'cache' => [
        /*
        |--------------------------------------------------------------------------
        | Cache Optimization Settings
        |--------------------------------------------------------------------------
        */
        'default_ttl' => env('REDIS_CACHE_TTL', 3600), // 1 hour default
        'prefix' => env('CACHE_PREFIX', 'maxcon_cache'),
        'serializer' => env('REDIS_SERIALIZER', 'php'), // php, igbinary, json
        'compression' => env('REDIS_COMPRESSION', false),
        
        // Cache warming settings
        'warm_up' => [
            'enabled' => env('CACHE_WARMUP_ENABLED', true),
            'keys' => [
                'system_settings' => 7200, // 2 hours
                'user_permissions' => 3600, // 1 hour
                'tenant_config' => 1800, // 30 minutes
                'menu_structure' => 3600, // 1 hour
            ],
        ],
        
        // Cache invalidation patterns
        'invalidation' => [
            'patterns' => [
                'user_*' => ['user_updated', 'user_deleted'],
                'tenant_*' => ['tenant_updated', 'settings_changed'],
                'product_*' => ['product_updated', 'inventory_changed'],
            ],
        ],
    ],

    'sessions' => [
        /*
        |--------------------------------------------------------------------------
        | Session Optimization Settings
        |--------------------------------------------------------------------------
        */
        'lifetime' => env('SESSION_LIFETIME', 120), // minutes
        'prefix' => env('SESSION_PREFIX', 'maxcon_session'),
        'gc_probability' => env('SESSION_GC_PROBABILITY', 1),
        'gc_divisor' => env('SESSION_GC_DIVISOR', 100),
    ],

    'queues' => [
        /*
        |--------------------------------------------------------------------------
        | Queue Optimization Settings
        |--------------------------------------------------------------------------
        */
        'default_queue' => env('QUEUE_DEFAULT', 'default'),
        'retry_after' => env('QUEUE_RETRY_AFTER', 90),
        'block_for' => env('QUEUE_BLOCK_FOR', null),
        
        // Queue priorities
        'priorities' => [
            'critical' => 10,
            'high' => 5,
            'normal' => 0,
            'low' => -5,
        ],
    ],

    'monitoring' => [
        /*
        |--------------------------------------------------------------------------
        | Redis Monitoring Settings
        |--------------------------------------------------------------------------
        */
        'enabled' => env('REDIS_MONITORING_ENABLED', true),
        'refresh_interval' => env('REDIS_MONITORING_REFRESH', 30), // seconds
        'alert_thresholds' => [
            'memory_usage_percent' => 80,
            'cache_hit_ratio_min' => 70,
            'connected_clients_max' => 100,
            'operations_per_sec_max' => 10000,
        ],
        
        // Performance metrics collection
        'metrics' => [
            'collect_performance_data' => true,
            'performance_test_keys' => 100,
            'store_metrics_for_days' => 7,
        ],
    ],

    'optimization' => [
        /*
        |--------------------------------------------------------------------------
        | Redis Optimization Strategies
        |--------------------------------------------------------------------------
        */
        'memory' => [
            'max_memory_policy' => env('REDIS_MAXMEMORY_POLICY', 'allkeys-lru'),
            'lazy_free' => env('REDIS_LAZY_FREE', true),
            'active_rehashing' => env('REDIS_ACTIVE_REHASHING', true),
        ],
        
        'persistence' => [
            'save_enabled' => env('REDIS_SAVE_ENABLED', true),
            'save_seconds' => env('REDIS_SAVE_SECONDS', 900), // 15 minutes
            'save_changes' => env('REDIS_SAVE_CHANGES', 1),
            'aof_enabled' => env('REDIS_AOF_ENABLED', false),
        ],
        
        'networking' => [
            'tcp_keepalive' => env('REDIS_TCP_KEEPALIVE', 300),
            'timeout' => env('REDIS_TIMEOUT', 0),
            'tcp_backlog' => env('REDIS_TCP_BACKLOG', 511),
        ],
    ],

    'security' => [
        /*
        |--------------------------------------------------------------------------
        | Redis Security Settings
        |--------------------------------------------------------------------------
        */
        'require_auth' => env('REDIS_REQUIRE_AUTH', false),
        'protected_mode' => env('REDIS_PROTECTED_MODE', true),
        'bind_interface' => env('REDIS_BIND', '127.0.0.1'),
        'rename_commands' => [
            // 'FLUSHDB' => 'FLUSHDB_RENAMED',
            // 'FLUSHALL' => 'FLUSHALL_RENAMED',
            // 'CONFIG' => 'CONFIG_RENAMED',
        ],
    ],

    'development' => [
        /*
        |--------------------------------------------------------------------------
        | Development & Debugging Settings
        |--------------------------------------------------------------------------
        */
        'debug_enabled' => env('REDIS_DEBUG', false),
        'log_slow_queries' => env('REDIS_LOG_SLOW_QUERIES', true),
        'slow_query_threshold' => env('REDIS_SLOW_QUERY_THRESHOLD', 10000), // microseconds
        'command_logging' => env('REDIS_COMMAND_LOGGING', false),
    ],

    'backup' => [
        /*
        |--------------------------------------------------------------------------
        | Redis Backup Settings
        |--------------------------------------------------------------------------
        */
        'enabled' => env('REDIS_BACKUP_ENABLED', false),
        'schedule' => env('REDIS_BACKUP_SCHEDULE', 'daily'),
        'retention_days' => env('REDIS_BACKUP_RETENTION', 7),
        'backup_path' => env('REDIS_BACKUP_PATH', storage_path('backups/redis')),
    ],

    'clustering' => [
        /*
        |--------------------------------------------------------------------------
        | Redis Clustering Settings (for production scaling)
        |--------------------------------------------------------------------------
        */
        'enabled' => env('REDIS_CLUSTER_ENABLED', false),
        'nodes' => [
            // Add cluster nodes here when scaling
        ],
        'options' => [
            'cluster' => 'redis',
            'prefix' => env('REDIS_PREFIX', 'maxcon_erp_database_'),
        ],
    ],

    'performance_profiles' => [
        /*
        |--------------------------------------------------------------------------
        | Performance Profiles for Different Environments
        |--------------------------------------------------------------------------
        */
        'development' => [
            'cache_ttl' => 300, // 5 minutes
            'session_lifetime' => 60, // 1 hour
            'monitoring_interval' => 60, // 1 minute
        ],
        
        'staging' => [
            'cache_ttl' => 1800, // 30 minutes
            'session_lifetime' => 120, // 2 hours
            'monitoring_interval' => 30, // 30 seconds
        ],
        
        'production' => [
            'cache_ttl' => 3600, // 1 hour
            'session_lifetime' => 240, // 4 hours
            'monitoring_interval' => 30, // 30 seconds
        ],
    ],

    'auto_optimization' => [
        /*
        |--------------------------------------------------------------------------
        | Automatic Optimization Settings
        |--------------------------------------------------------------------------
        */
        'enabled' => env('REDIS_AUTO_OPTIMIZATION', true),
        'schedule' => [
            'cache_cleanup' => 'hourly',
            'memory_optimization' => 'daily',
            'performance_analysis' => 'weekly',
        ],
        
        'triggers' => [
            'memory_threshold' => 85, // percent
            'hit_ratio_threshold' => 60, // percent
            'response_time_threshold' => 100, // milliseconds
        ],
    ],
];
