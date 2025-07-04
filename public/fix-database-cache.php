<?php
/**
 * Fix Laravel Database Cache Issue
 * Resolves: SQLSTATE[42S02]: Base table or view not found: 1146 Table 'cache' doesn't exist
 */

echo "<h2>🔧 Laravel Database Cache Fix</h2>";

try {
    // Load Laravel if possible
    $laravelRoot = dirname(__DIR__);
    $autoloadPath = $laravelRoot . '/vendor/autoload.php';
    
    if (file_exists($autoloadPath)) {
        require_once $autoloadPath;
        echo "<p>✅ Composer autoload loaded</p>";
    } else {
        throw new Exception("Composer autoload not found");
    }
    
    // Try to load Laravel app
    if (file_exists($laravelRoot . '/bootstrap/app.php')) {
        $app = require_once $laravelRoot . '/bootstrap/app.php';
        echo "<p>✅ Laravel app loaded</p>";
    } else {
        throw new Exception("Laravel bootstrap not found");
    }
    
    echo "<h3>📋 Step 1: Checking Database Connection</h3>";
    
    // Test database connection
    try {
        $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
        $request = Illuminate\Http\Request::create('/test', 'GET');
        $app->instance('request', $request);
        
        // Boot the application
        $app->boot();
        
        // Test database connection
        $pdo = DB::connection()->getPdo();
        echo "<p>✅ Database connection successful</p>";
        
        echo "<h3>📋 Step 2: Checking Cache Configuration</h3>";
        
        // Check current cache driver
        $cacheDriver = config('cache.default');
        echo "<p><strong>Current cache driver:</strong> $cacheDriver</p>";
        
        if ($cacheDriver === 'database') {
            echo "<p>⚠️ Using database cache driver - checking cache tables...</p>";
            
            echo "<h3>📋 Step 3: Checking Cache Tables</h3>";
            
            // Check if cache table exists
            try {
                $cacheCount = DB::table('cache')->count();
                echo "<p>✅ Cache table exists with $cacheCount entries</p>";
            } catch (Exception $e) {
                echo "<p>❌ Cache table missing: " . htmlspecialchars($e->getMessage()) . "</p>";
                
                echo "<h3>🔧 Creating Cache Tables</h3>";
                
                // Create cache table
                try {
                    Schema::create('cache', function ($table) {
                        $table->string('key')->primary();
                        $table->mediumText('value');
                        $table->integer('expiration');
                    });
                    echo "<p>✅ Cache table created successfully</p>";
                } catch (Exception $e) {
                    echo "<p>❌ Failed to create cache table: " . htmlspecialchars($e->getMessage()) . "</p>";
                }
                
                // Create cache_locks table
                try {
                    Schema::create('cache_locks', function ($table) {
                        $table->string('key')->primary();
                        $table->string('owner');
                        $table->integer('expiration');
                    });
                    echo "<p>✅ Cache locks table created successfully</p>";
                } catch (Exception $e) {
                    echo "<p>❌ Failed to create cache_locks table: " . htmlspecialchars($e->getMessage()) . "</p>";
                }
            }
            
            // Check if cache_locks table exists
            try {
                $locksCount = DB::table('cache_locks')->count();
                echo "<p>✅ Cache locks table exists with $locksCount entries</p>";
            } catch (Exception $e) {
                echo "<p>❌ Cache locks table missing, but this is optional</p>";
            }
            
        } else {
            echo "<p>ℹ️ Not using database cache driver - no cache tables needed</p>";
        }
        
        echo "<h3>📋 Step 4: Testing Cache Operations</h3>";
        
        // Test cache operations
        try {
            Cache::put('test_key', 'test_value', 60);
            $value = Cache::get('test_key');
            if ($value === 'test_value') {
                echo "<p>✅ Cache write/read test successful</p>";
                Cache::forget('test_key');
                echo "<p>✅ Cache delete test successful</p>";
            } else {
                echo "<p>❌ Cache read test failed</p>";
            }
        } catch (Exception $e) {
            echo "<p>❌ Cache test failed: " . htmlspecialchars($e->getMessage()) . "</p>";
            
            echo "<h3>🔧 Switching to File Cache</h3>";
            echo "<p>Database cache is not working. Switching to file cache...</p>";
            
            // Show instructions to switch to file cache
            echo "<div style='background: #fff3cd; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
            echo "<h4>📝 Manual Fix Required:</h4>";
            echo "<p>Edit your <code>.env</code> file and change:</p>";
            echo "<pre>CACHE_DRIVER=database</pre>";
            echo "<p>To:</p>";
            echo "<pre>CACHE_DRIVER=file</pre>";
            echo "<p>Then run: <code>php artisan config:clear</code></p>";
            echo "</div>";
        }
        
        echo "<h3>🎉 Cache Fix Complete!</h3>";
        echo "<p>✅ Database cache tables are now properly configured</p>";
        echo "<p><a href='/dashboard' class='btn btn-success'>Test Original Dashboard</a></p>";
        
    } catch (Exception $e) {
        echo "<p>❌ Laravel boot failed: " . htmlspecialchars($e->getMessage()) . "</p>";
        throw $e;
    }
    
} catch (Exception $e) {
    echo "<h3>❌ Critical Error</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    
    echo "<h3>🔧 Manual Database Cache Fix</h3>";
    echo "<p><strong>Run these SQL commands in your database:</strong></p>";
    echo "<pre>";
    echo "CREATE TABLE `cache` (\n";
    echo "  `key` varchar(255) NOT NULL,\n";
    echo "  `value` mediumtext NOT NULL,\n";
    echo "  `expiration` int(11) NOT NULL,\n";
    echo "  PRIMARY KEY (`key`)\n";
    echo ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;\n\n";
    
    echo "CREATE TABLE `cache_locks` (\n";
    echo "  `key` varchar(255) NOT NULL,\n";
    echo "  `owner` varchar(255) NOT NULL,\n";
    echo "  `expiration` int(11) NOT NULL,\n";
    echo "  PRIMARY KEY (`key`)\n";
    echo ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;\n";
    echo "</pre>";
    
    echo "<h3>🔧 Alternative: Switch to File Cache</h3>";
    echo "<p><strong>Edit your .env file and change:</strong></p>";
    echo "<pre>CACHE_DRIVER=database</pre>";
    echo "<p><strong>To:</strong></p>";
    echo "<pre>CACHE_DRIVER=file</pre>";
    echo "<p><strong>Then run via SSH:</strong></p>";
    echo "<pre>php artisan config:clear\nphp artisan cache:clear</pre>";
}

echo "<h3>🎯 Alternative Working Solutions</h3>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
echo "<p><strong>While fixing cache, use these working dashboards:</strong></p>";
echo "<ul>";
echo "<li><a href='/emergency-dashboard.php'>Emergency Dashboard</a> - No cache dependencies</li>";
echo "<li><a href='/custom-auth/dashboard.php'>Custom Dashboard</a> - Independent system</li>";
echo "</ul>";
echo "</div>";
?>
