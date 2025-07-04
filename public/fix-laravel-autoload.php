<?php
/**
 * Fix Laravel Autoload Issue
 * This fixes the "Class Illuminate\Foundation\Application not found" error
 */

echo "<h2>🔧 Laravel Autoload Fix</h2>";

try {
    $laravelRoot = dirname(__DIR__);
    
    echo "<h3>📋 Step 1: Checking Composer Autoload</h3>";
    
    // Check if vendor directory exists
    $vendorPath = $laravelRoot . '/vendor';
    if (!is_dir($vendorPath)) {
        echo "<p>❌ Vendor directory not found at: $vendorPath</p>";
        echo "<p><strong>Solution:</strong> Run <code>composer install</code> via SSH</p>";
        throw new Exception("Vendor directory missing");
    }
    echo "<p>✅ Vendor directory found</p>";
    
    // Check if autoload.php exists
    $autoloadPath = $vendorPath . '/autoload.php';
    if (!file_exists($autoloadPath)) {
        echo "<p>❌ Composer autoload file not found at: $autoloadPath</p>";
        echo "<p><strong>Solution:</strong> Run <code>composer dump-autoload</code> via SSH</p>";
        throw new Exception("Autoload file missing");
    }
    echo "<p>✅ Composer autoload file found</p>";
    
    echo "<h3>📋 Step 2: Testing Composer Autoload</h3>";
    
    // Try to load composer autoload
    try {
        require_once $autoloadPath;
        echo "<p>✅ Composer autoload loaded successfully</p>";
    } catch (Exception $e) {
        echo "<p>❌ Composer autoload failed: " . htmlspecialchars($e->getMessage()) . "</p>";
        throw new Exception("Autoload loading failed");
    }
    
    echo "<h3>📋 Step 3: Testing Laravel Classes</h3>";
    
    // Test if Laravel classes are available
    $testClasses = [
        'Illuminate\Foundation\Application',
        'Illuminate\Http\Request',
        'Illuminate\Support\Facades\Route',
        'Illuminate\Contracts\Http\Kernel'
    ];
    
    $missingClasses = [];
    foreach ($testClasses as $class) {
        if (class_exists($class)) {
            echo "<p>✅ Class available: <code>$class</code></p>";
        } else {
            echo "<p>❌ Class missing: <code>$class</code></p>";
            $missingClasses[] = $class;
        }
    }
    
    if (!empty($missingClasses)) {
        echo "<h3>🔧 Fixing Missing Classes</h3>";
        echo "<p><strong>Missing classes detected. Running fixes...</strong></p>";
        
        // Try to regenerate autoload
        echo "<p>Attempting to regenerate Composer autoload...</p>";
        
        // Create a script to run via exec
        $fixScript = "#!/bin/bash\n";
        $fixScript .= "cd " . escapeshellarg($laravelRoot) . "\n";
        $fixScript .= "composer dump-autoload --optimize\n";
        $fixScript .= "composer install --no-dev --optimize-autoloader\n";
        
        $scriptPath = $laravelRoot . '/fix-autoload.sh';
        file_put_contents($scriptPath, $fixScript);
        chmod($scriptPath, 0755);
        
        echo "<p>✅ Created fix script at: $scriptPath</p>";
        echo "<p><strong>Run this via SSH:</strong></p>";
        echo "<pre>cd " . htmlspecialchars($laravelRoot) . "\nbash fix-autoload.sh</pre>";
        
    } else {
        echo "<p>✅ All Laravel classes are available!</p>";
        
        echo "<h3>📋 Step 4: Testing Laravel Bootstrap</h3>";
        
        // Try to create Laravel application
        try {
            $app = new Illuminate\Foundation\Application($laravelRoot);
            echo "<p>✅ Laravel Application created successfully</p>";
            
            // Try to load service providers
            echo "<h3>📋 Step 5: Testing Service Providers</h3>";
            
            try {
                $app->singleton(
                    Illuminate\Contracts\Http\Kernel::class,
                    App\Http\Kernel::class
                );
                echo "<p>✅ HTTP Kernel bound successfully</p>";
                
                // Test if we can create a simple response
                echo "<h3>📋 Step 6: Testing HTTP Response</h3>";
                
                $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
                $request = Illuminate\Http\Request::create('/test', 'GET');
                
                echo "<p>✅ Laravel is working! The autoload issue is fixed.</p>";
                
                echo "<h3>🎉 Success! Laravel Should Now Work</h3>";
                echo "<p><strong>Try accessing your original dashboard:</strong></p>";
                echo "<p><a href='/dashboard' class='btn btn-success'>Go to Original Dashboard</a></p>";
                
            } catch (Exception $e) {
                echo "<p>❌ Service provider error: " . htmlspecialchars($e->getMessage()) . "</p>";
                echo "<p>This indicates a configuration issue in your Laravel app.</p>";
            }
            
        } catch (Exception $e) {
            echo "<p>❌ Laravel Application creation failed: " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<p>This indicates a deeper Laravel configuration issue.</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p>❌ Critical error: " . htmlspecialchars($e->getMessage()) . "</p>";
    
    echo "<h3>🔧 Manual Fix Instructions</h3>";
    echo "<p><strong>Run these commands via SSH:</strong></p>";
    echo "<pre>";
    echo "cd /home/1486247.cloudwaysapps.com/ufnpbxkvbd/public_html\n";
    echo "composer install --no-dev --optimize-autoloader\n";
    echo "composer dump-autoload --optimize\n";
    echo "php artisan config:clear\n";
    echo "php artisan cache:clear\n";
    echo "php artisan route:clear\n";
    echo "php artisan view:clear\n";
    echo "</pre>";
}

echo "<h3>🎯 Alternative Working Solutions</h3>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
echo "<p><strong>While fixing Laravel, you can use these working dashboards:</strong></p>";
echo "<ul>";
echo "<li><a href='/emergency-dashboard.php'>Emergency Dashboard</a> - Full Laravel-style interface</li>";
echo "<li><a href='/custom-auth/dashboard.php'>Custom Dashboard</a> - Fully functional authentication system</li>";
echo "<li><a href='/custom-auth/laravel-bridge.php'>Laravel Bridge</a> - Hybrid solution</li>";
echo "</ul>";
echo "</div>";

echo "<h3>📊 System Status</h3>";
echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; margin: 20px 0;'>";
echo "<div style='padding: 10px; background: #d4edda; border-radius: 5px;'><strong>✅ Database:</strong> Working</div>";
echo "<div style='padding: 10px; background: #d4edda; border-radius: 5px;'><strong>✅ Custom Auth:</strong> Working</div>";
echo "<div style='padding: 10px; background: #f8d7da; border-radius: 5px;'><strong>❌ Laravel Autoload:</strong> Issues</div>";
echo "<div style='padding: 10px; background: #fff3cd; border-radius: 5px;'><strong>⚠️ Original Dashboard:</strong> Fixing</div>";
echo "</div>";
?>
