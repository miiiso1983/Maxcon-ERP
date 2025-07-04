<?php
/**
 * SESSION FIX
 * Fix Laravel session configuration issues
 * URL: https://your-domain.com/session-fix.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><title>Session Fix</title>";
echo "<style>body{font-family:Arial;margin:20px;} .ok{color:green;font-weight:bold;} .error{color:red;font-weight:bold;} .section{margin:20px 0;padding:15px;border:1px solid #ddd;border-radius:5px;background:#fafafa;}</style>";
echo "</head><body>";

echo "<h1>🔧 Session Configuration Fix</h1>";

$rootPath = dirname(__DIR__);
$fixes = [];

// Check and fix session configuration
echo "<div class='section'>";
echo "<h2>Session Configuration Check</h2>";

// Check if session directories exist
$sessionPaths = [
    'storage/framework/sessions' => $rootPath . '/storage/framework/sessions',
    'storage/framework/cache' => $rootPath . '/storage/framework/cache',
    'storage/framework/views' => $rootPath . '/storage/framework/views',
    'storage/logs' => $rootPath . '/storage/logs'
];

foreach ($sessionPaths as $name => $path) {
    if (!is_dir($path)) {
        if (mkdir($path, 0755, true)) {
            echo "<span class='ok'>✅ Created directory: $name</span><br>";
            $fixes[] = "Created $name directory";
        } else {
            echo "<span class='error'>❌ Failed to create: $name</span><br>";
        }
    } else {
        echo "<span class='ok'>✅ Directory exists: $name</span><br>";
    }
    
    if (is_writable($path)) {
        echo "<span class='ok'>✅ Directory writable: $name</span><br>";
    } else {
        if (chmod($path, 0755)) {
            echo "<span class='ok'>✅ Fixed permissions: $name</span><br>";
            $fixes[] = "Fixed permissions for $name";
        } else {
            echo "<span class='error'>❌ Cannot fix permissions: $name</span><br>";
        }
    }
}

echo "</div>";

// Check .env session configuration
echo "<div class='section'>";
echo "<h2>.env Session Configuration</h2>";

$envPath = $rootPath . '/.env';
if (file_exists($envPath)) {
    $envContent = file_get_contents($envPath);
    echo "<span class='ok'>✅ .env file found</span><br>";
    
    // Check session driver
    if (strpos($envContent, 'SESSION_DRIVER=') === false) {
        $envContent .= "\nSESSION_DRIVER=file\n";
        $fixes[] = "Added SESSION_DRIVER=file";
    } else if (strpos($envContent, 'SESSION_DRIVER=database') !== false) {
        $envContent = str_replace('SESSION_DRIVER=database', 'SESSION_DRIVER=file', $envContent);
        $fixes[] = "Changed SESSION_DRIVER to file";
    }
    
    // Check session lifetime
    if (strpos($envContent, 'SESSION_LIFETIME=') === false) {
        $envContent .= "SESSION_LIFETIME=120\n";
        $fixes[] = "Added SESSION_LIFETIME=120";
    }
    
    // Check session encrypt
    if (strpos($envContent, 'SESSION_ENCRYPT=') === false) {
        $envContent .= "SESSION_ENCRYPT=false\n";
        $fixes[] = "Added SESSION_ENCRYPT=false";
    }
    
    // Write back if changes were made
    if (count($fixes) > 0) {
        if (file_put_contents($envPath, $envContent)) {
            echo "<span class='ok'>✅ Updated .env configuration</span><br>";
        } else {
            echo "<span class='error'>❌ Failed to update .env</span><br>";
        }
    } else {
        echo "<span class='ok'>✅ .env session configuration OK</span><br>";
    }
} else {
    echo "<span class='error'>❌ .env file not found</span><br>";
}

echo "</div>";

// Test Laravel bootstrap with session fix
echo "<div class='section'>";
echo "<h2>Laravel Bootstrap Test</h2>";

try {
    require_once $rootPath . '/vendor/autoload.php';
    $app = require_once $rootPath . '/bootstrap/app.php';
    
    echo "<span class='ok'>✅ Laravel application loaded</span><br>";
    
    // Test if session service is available
    try {
        $sessionManager = $app->make('session');
        echo "<span class='ok'>✅ Session service available</span><br>";
    } catch (Exception $e) {
        echo "<span class='error'>❌ Session service error: " . $e->getMessage() . "</span><br>";
    }
    
    // Test Auth facade
    if (class_exists('Illuminate\Support\Facades\Auth')) {
        echo "<span class='ok'>✅ Auth facade available</span><br>";
    }
    
} catch (Exception $e) {
    echo "<span class='error'>❌ Laravel bootstrap error: " . $e->getMessage() . "</span><br>";
}

echo "</div>";

// Summary
echo "<div class='section'>";
echo "<h2>📋 Summary</h2>";

if (count($fixes) > 0) {
    echo "<h3 style='color:green;'>✅ Fixes Applied:</h3>";
    foreach ($fixes as $fix) {
        echo "• $fix<br>";
    }
    echo "<br><p><strong>Please clear Laravel caches after these fixes:</strong></p>";
    echo "<code>php artisan config:clear<br>php artisan cache:clear<br>php artisan session:table</code>";
} else {
    echo "<span class='ok'>✅ No session configuration issues found</span><br>";
}

echo "<h3>Next Steps:</h3>";
echo "1. Clear all Laravel caches<br>";
echo "2. Test your main login page again<br>";
echo "3. Check if session errors are resolved<br>";

echo "</div>";

echo "</body></html>";
?>
