<?php
/**
 * SESSION SERVICE PROVIDER FIX
 * Direct fix for missing session service provider registration
 * URL: https://your-domain.com/session-service-fix.php
 */

set_time_limit(300);
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><title>Session Service Provider Fix</title>";
echo "<style>body{font-family:Arial;margin:20px;} .container{max-width:800px;margin:0 auto;} .ok{color:green;font-weight:bold;} .error{color:red;font-weight:bold;} .warning{color:orange;font-weight:bold;} .section{margin:20px 0;padding:15px;border:1px solid #ddd;border-radius:5px;background:#fafafa;} .button{display:inline-block;padding:10px 20px;background:#007bff;color:white;text-decoration:none;border-radius:4px;margin:5px;} .success{background:#d4edda;border:1px solid #c3e6cb;padding:15px;border-radius:5px;}</style>";
echo "</head><body><div class='container'>";

echo "<h1>🔧 Session Service Provider Fix</h1>";
echo "<p>Direct fix for 'Target class [session] does not exist' error.</p>";

$rootPath = dirname(__DIR__);
$fixes = [];

// Check current bootstrap/providers.php
echo "<div class='section'>";
echo "<h2>🔍 Step 1: Check Current Service Provider Registration</h2>";

$providersPath = $rootPath . '/bootstrap/providers.php';
if (file_exists($providersPath)) {
    $currentProviders = file_get_contents($providersPath);
    echo "<span class='ok'>✅ bootstrap/providers.php exists</span><br>";
    echo "<pre style='background:#f8f9fa;padding:10px;border-radius:4px;'>" . htmlspecialchars($currentProviders) . "</pre>";
    
    if (strpos($currentProviders, 'SessionServiceProvider') === false) {
        echo "<span class='error'>❌ SessionServiceProvider NOT found in bootstrap/providers.php</span><br>";
        echo "<p><strong>This is the root cause of the session error!</strong></p>";
    } else {
        echo "<span class='ok'>✅ SessionServiceProvider found</span><br>";
    }
} else {
    echo "<span class='error'>❌ bootstrap/providers.php not found</span><br>";
}

echo "</div>";

// Fix bootstrap/providers.php
echo "<div class='section'>";
echo "<h2>🔧 Step 2: Fix Service Provider Registration</h2>";

$fixedProviders = '<?php

return [
    // Core Laravel Service Providers
    Illuminate\Auth\AuthServiceProvider::class,
    Illuminate\Broadcasting\BroadcastServiceProvider::class,
    Illuminate\Bus\BusServiceProvider::class,
    Illuminate\Cache\CacheServiceProvider::class,
    Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
    Illuminate\Cookie\CookieServiceProvider::class,
    Illuminate\Database\DatabaseServiceProvider::class,
    Illuminate\Encryption\EncryptionServiceProvider::class,
    Illuminate\Filesystem\FilesystemServiceProvider::class,
    Illuminate\Foundation\Providers\FoundationServiceProvider::class,
    Illuminate\Hashing\HashServiceProvider::class,
    Illuminate\Mail\MailServiceProvider::class,
    Illuminate\Notifications\NotificationServiceProvider::class,
    Illuminate\Pagination\PaginationServiceProvider::class,
    Illuminate\Pipeline\PipelineServiceProvider::class,
    Illuminate\Queue\QueueServiceProvider::class,
    Illuminate\Redis\RedisServiceProvider::class,
    Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
    Illuminate\Session\SessionServiceProvider::class,
    Illuminate\Translation\TranslationServiceProvider::class,
    Illuminate\Validation\ValidationServiceProvider::class,
    Illuminate\View\ViewServiceProvider::class,

    // Application Service Providers
    App\Providers\AppServiceProvider::class,
    App\Providers\AuthServiceProvider::class,
    App\Providers\EventServiceProvider::class,
    App\Providers\RouteServiceProvider::class,
    App\Providers\BladeServiceProvider::class,
];
';

if (file_put_contents($providersPath, $fixedProviders)) {
    echo "<span class='ok'>✅ Fixed bootstrap/providers.php with all core service providers</span><br>";
    $fixes[] = "Added SessionServiceProvider to bootstrap/providers.php";
} else {
    echo "<span class='error'>❌ Failed to update bootstrap/providers.php</span><br>";
}

echo "</div>";

// Force clear any cached service providers
echo "<div class='section'>";
echo "<h2>🗑️ Step 3: Clear Service Provider Cache</h2>";

$cacheFiles = [
    'bootstrap/cache/services.php',
    'bootstrap/cache/packages.php',
    'bootstrap/cache/config.php'
];

foreach ($cacheFiles as $file) {
    $fullPath = $rootPath . '/' . $file;
    if (file_exists($fullPath)) {
        if (unlink($fullPath)) {
            echo "<span class='ok'>✅ Deleted: $file</span><br>";
            $fixes[] = "Deleted $file";
        }
    }
}

echo "</div>";

// Test the fix
echo "<div class='section'>";
echo "<h2>🧪 Step 4: Test Session Service</h2>";

try {
    // Clear any existing autoloader cache
    if (function_exists('opcache_reset')) {
        opcache_reset();
    }
    
    require_once $rootPath . '/vendor/autoload.php';
    $app = require_once $rootPath . '/bootstrap/app.php';
    
    echo "<span class='ok'>✅ Laravel application loaded</span><br>";
    
    // Boot the application
    try {
        $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
        $app->boot();
        echo "<span class='ok'>✅ Laravel application booted</span><br>";
        
        // Test session service
        try {
            $sessionManager = $app->make('session');
            echo "<span class='ok'>✅ Session service available!</span><br>";
            echo "<span class='ok'>✅ Session driver: " . $sessionManager->getDefaultDriver() . "</span><br>";
            $fixes[] = "Session service now working";
        } catch (Exception $e) {
            echo "<span class='error'>❌ Session service error: " . $e->getMessage() . "</span><br>";
        }
        
        // Test Auth facade
        if (class_exists('Illuminate\Support\Facades\Auth')) {
            echo "<span class='ok'>✅ Auth facade available</span><br>";
        }
        
    } catch (Exception $e) {
        echo "<span class='error'>❌ Laravel boot error: " . $e->getMessage() . "</span><br>";
    }
    
} catch (Exception $e) {
    echo "<span class='error'>❌ Laravel load error: " . $e->getMessage() . "</span><br>";
}

echo "</div>";

// Summary
echo "<div class='section'>";
echo "<h2>📋 Summary</h2>";

if (count($fixes) > 0) {
    echo "<div class='success'>";
    echo "<h3>✅ Service Provider Fixes Applied:</h3>";
    foreach ($fixes as $fix) {
        echo "• $fix<br>";
    }
    echo "</div>";
} else {
    echo "<span class='warning'>⚠️ No fixes were applied</span><br>";
}

echo "<h3>🎯 Next Steps:</h3>";
echo "<ol>";
echo "<li><strong>Test session fix:</strong> <a href='session-fix.php' target='_blank'>Run Session Fix</a></li>";
echo "<li><strong>Test your login:</strong> <a href='/login' target='_blank'>Go to Login Page</a></li>";
echo "<li><strong>Test Laravel login:</strong> <a href='laravel-login-fix.php' target='_blank'>Test Laravel Login</a></li>";
echo "</ol>";

echo "<div style='background:#e7f3ff;padding:15px;border-radius:5px;margin:20px 0;'>";
echo "<h3>🔍 What This Fix Does:</h3>";
echo "<p>The issue was that <code>bootstrap/providers.php</code> only contained application service providers, but was missing the core Laravel service providers including <code>SessionServiceProvider</code>.</p>";
echo "<p>This fix adds all the necessary core Laravel service providers to ensure the session service is properly registered.</p>";
echo "</div>";

echo "</div>";

echo "</div></body></html>";
?>
