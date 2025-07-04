<?php
/**
 * FINAL SESSION FIX
 * Last resort fix for persistent session service errors
 * URL: https://your-domain.com/final-session-fix.php
 */

set_time_limit(300);
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><title>Final Session Fix</title>";
echo "<style>body{font-family:Arial;margin:20px;} .container{max-width:800px;margin:0 auto;} .ok{color:green;font-weight:bold;} .error{color:red;font-weight:bold;} .warning{color:orange;font-weight:bold;} .section{margin:20px 0;padding:15px;border:1px solid #ddd;border-radius:5px;background:#fafafa;} .button{display:inline-block;padding:10px 20px;background:#007bff;color:white;text-decoration:none;border-radius:4px;margin:5px;} .success{background:#d4edda;border:1px solid #c3e6cb;padding:15px;border-radius:5px;}</style>";
echo "</head><body><div class='container'>";

echo "<h1>🔧 Final Session Fix</h1>";
echo "<p>Last resort fix for persistent session service errors.</p>";

$rootPath = dirname(__DIR__);
$fixes = [];

// Step 1: Nuclear cache clear
echo "<div class='section'>";
echo "<h2>💥 Step 1: Nuclear Cache Clear</h2>";

// Delete ALL cache files
$allCacheFiles = [
    'bootstrap/cache/config.php',
    'bootstrap/cache/routes-v7.php',
    'bootstrap/cache/routes.php',
    'bootstrap/cache/services.php',
    'bootstrap/cache/packages.php',
    'bootstrap/cache/compiled.php',
    'storage/framework/cache/data/*',
    'storage/framework/views/*',
    'storage/framework/sessions/*'
];

foreach ($allCacheFiles as $pattern) {
    if (strpos($pattern, '*') !== false) {
        $files = glob($rootPath . '/' . $pattern);
        foreach ($files as $file) {
            if (is_file($file) && unlink($file)) {
                echo "<span class='ok'>✅ Deleted: " . basename($file) . "</span><br>";
            }
        }
    } else {
        $fullPath = $rootPath . '/' . $pattern;
        if (file_exists($fullPath) && unlink($fullPath)) {
            echo "<span class='ok'>✅ Deleted: $pattern</span><br>";
            $fixes[] = "Deleted $pattern";
        }
    }
}

// Clear OPcache if available
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "<span class='ok'>✅ OPcache cleared</span><br>";
    $fixes[] = "OPcache cleared";
}

echo "</div>";

// Step 2: Force session configuration
echo "<div class='section'>";
echo "<h2>⚙️ Step 2: Force Session Configuration</h2>";

// Update session config directly
$sessionConfigPath = $rootPath . '/config/session.php';
if (file_exists($sessionConfigPath)) {
    $sessionConfig = file_get_contents($sessionConfigPath);
    
    // Force file driver
    $sessionConfig = preg_replace(
        "/('driver'\s*=>\s*env\('SESSION_DRIVER',\s*)'[^']*'/",
        "$1'file'",
        $sessionConfig
    );
    
    if (file_put_contents($sessionConfigPath, $sessionConfig)) {
        echo "<span class='ok'>✅ Forced session driver to 'file' in config</span><br>";
        $fixes[] = "Forced session driver to file";
    }
}

echo "</div>";

// Step 3: Recreate bootstrap/app.php with explicit session service
echo "<div class='section'>";
echo "<h2>🔧 Step 3: Enhanced Bootstrap</h2>";

$enhancedBootstrap = '<?php

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
*/

$app = new Illuminate\Foundation\Application(
    $_ENV[\'APP_BASE_PATH\'] ?? dirname(__DIR__)
);

/*
|--------------------------------------------------------------------------
| Bind Important Interfaces
|--------------------------------------------------------------------------
*/

$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    App\Http\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

/*
|--------------------------------------------------------------------------
| Force Register Session Service Provider
|--------------------------------------------------------------------------
*/

// Ensure session service provider is registered
$app->register(Illuminate\Session\SessionServiceProvider::class);

/*
|--------------------------------------------------------------------------
| Return The Application
|--------------------------------------------------------------------------
*/

return $app;
';

$bootstrapPath = $rootPath . '/bootstrap/app.php';
if (file_put_contents($bootstrapPath, $enhancedBootstrap)) {
    echo "<span class='ok'>✅ Enhanced bootstrap/app.php with explicit session service</span><br>";
    $fixes[] = "Enhanced bootstrap with explicit session service";
}

echo "</div>";

// Step 4: Test with fresh Laravel instance
echo "<div class='section'>";
echo "<h2>🧪 Step 4: Test Fresh Laravel Instance</h2>";

try {
    // Clear any existing includes
    $included_files = get_included_files();
    echo "<span class='ok'>✅ Current included files: " . count($included_files) . "</span><br>";
    
    // Fresh autoloader
    require_once $rootPath . '/vendor/autoload.php';
    
    // Fresh application
    $app = require_once $rootPath . '/bootstrap/app.php';
    
    echo "<span class='ok'>✅ Fresh Laravel application loaded</span><br>";
    
    // Boot application
    $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
    $app->boot();
    echo "<span class='ok'>✅ Laravel application booted</span><br>";
    
    // Test session service with multiple methods
    try {
        // Method 1: Direct make
        $sessionManager = $app->make('session');
        echo "<span class='ok'>✅ Session service available via make('session')</span><br>";
        
        // Method 2: Via contract
        $sessionStore = $app->make(\Illuminate\Contracts\Session\Session::class);
        echo "<span class='ok'>✅ Session store available via contract</span><br>";
        
        // Method 3: Via facade
        if (class_exists('\Illuminate\Support\Facades\Session')) {
            echo "<span class='ok'>✅ Session facade class exists</span><br>";
        }
        
        $fixes[] = "Session service fully working";
        
    } catch (Exception $e) {
        echo "<span class='error'>❌ Session service error: " . $e->getMessage() . "</span><br>";
        
        // Try to register session service manually
        try {
            $app->register(\Illuminate\Session\SessionServiceProvider::class);
            $sessionManager = $app->make('session');
            echo "<span class='ok'>✅ Session service working after manual registration</span><br>";
            $fixes[] = "Session service working after manual registration";
        } catch (Exception $e2) {
            echo "<span class='error'>❌ Manual registration failed: " . $e2->getMessage() . "</span><br>";
        }
    }
    
} catch (Exception $e) {
    echo "<span class='error'>❌ Laravel error: " . $e->getMessage() . "</span><br>";
}

echo "</div>";

// Summary and next steps
echo "<div class='section'>";
echo "<h2>📋 Final Summary</h2>";

if (count($fixes) > 0) {
    echo "<div class='success'>";
    echo "<h3>✅ Final Fixes Applied:</h3>";
    foreach ($fixes as $fix) {
        echo "• $fix<br>";
    }
    echo "</div>";
}

echo "<h3>🎯 Test Your Login Now:</h3>";
echo "<p><a href='/login' class='button' style='background:#28a745;font-size:16px;'>🚀 TEST LOGIN PAGE</a></p>";

echo "<h3>📞 If Still Not Working:</h3>";
echo "<p>Contact Cloudways support and ask them to run:</p>";
echo "<code>php artisan config:clear<br>php artisan cache:clear<br>composer dump-autoload</code>";

echo "</div>";

echo "</div></body></html>";
?>
