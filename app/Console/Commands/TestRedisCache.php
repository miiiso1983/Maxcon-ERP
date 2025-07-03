<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class TestRedisCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:redis-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Redis cache functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Redis Cache Configuration...');

        // Test Cache facade
        $this->info('1. Testing Cache facade...');
        Cache::put('test_cache_key', 'Hello from Laravel Cache!', 60);
        $cacheValue = Cache::get('test_cache_key');
        $this->info("Cache value: {$cacheValue}");

        // Test Redis facade directly
        $this->info('2. Testing Redis facade...');
        Redis::set('test_redis_key', 'Hello from Redis!');
        Redis::expire('test_redis_key', 60);
        $redisValue = Redis::get('test_redis_key');
        $this->info("Redis value: {$redisValue}");

        // Test cache driver info
        $this->info('3. Cache driver information...');
        $this->info('Cache driver: ' . config('cache.default'));
        $this->info('Redis client: ' . config('database.redis.client'));
        $this->info('Redis host: ' . config('database.redis.default.host'));
        $this->info('Redis port: ' . config('database.redis.default.port'));

        // Test Redis connection
        $this->info('4. Testing Redis connection...');
        try {
            $ping = Redis::ping();
            $this->info("Redis ping response: {$ping}");
        } catch (\Exception $e) {
            $this->error("Redis connection failed: " . $e->getMessage());
        }

        // Clean up
        Cache::forget('test_cache_key');
        Redis::del('test_redis_key');

        $this->info('Redis cache test completed successfully!');
    }
}
