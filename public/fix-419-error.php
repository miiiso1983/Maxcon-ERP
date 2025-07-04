<?php
/**
 * 419 PAGE EXPIRED Error Fix Tool
 * Diagnoses and fixes CSRF token and session issues
 */

// Get the Laravel root path
$laravelRoot = dirname(__DIR__);

echo "<!DOCTYPE html>";
echo "<html><head><title>419 Error Fix Tool</title>";
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
.container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
.success { color: #28a745; background: #d4edda; padding: 10px; border-radius: 4px; margin: 10px 0; }
.error { color: #dc3545; background: #f8d7da; padding: 10px; border-radius: 4px; margin: 10px 0; }
.info { color: #0c5460; background: #d1ecf1; padding: 10px; border-radius: 4px; margin: 10px 0; }
.warning { color: #856404; background: #fff3cd; padding: 10px; border-radius: 4px; margin: 10px 0; }
.btn { display: inline-block; padding: 8px 16px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; margin: 5px; }
.btn:hover { background: #0056b3; }
pre { background: #f8f9fa; padding: 15px; border-radius: 4px; overflow-x: auto; }
</style></head><body>";

echo "<div class='container'>";
echo "<h1>🔧 419 PAGE EXPIRED Error Fix Tool</h1>";

// Check if Laravel exists
if (!file_exists($laravelRoot . '/artisan')) {
    echo "<div class='error'>❌ Laravel installation not found in: $laravelRoot</div>";
    exit;
}

echo "<div class='success'>✅ Laravel installation found</div>";

// Check .env file
$envFile = $laravelRoot . '/.env';
if (!file_exists($envFile)) {
    echo "<div class='error'>❌ .env file not found</div>";
    exit;
}

echo "<div class='success'>✅ .env file found</div>";

// Read .env file
$envContent = file_get_contents($envFile);

// Check session configuration
echo "<h2>📋 Session Configuration Analysis</h2>";

if (preg_match('/SESSION_DRIVER=(.+)/', $envContent, $matches)) {
    $sessionDriver = trim($matches[1]);
    echo "<div class='info'>Current SESSION_DRIVER: <strong>$sessionDriver</strong></div>";
    
    if ($sessionDriver === 'redis') {
        echo "<div class='warning'>⚠️ Redis session driver detected. This might cause 419 errors if Redis is not running.</div>";
        echo "<div class='info'>💡 Recommendation: Switch to 'file' driver for local development</div>";
    } elseif ($sessionDriver === 'file') {
        echo "<div class='success'>✅ File session driver is good for local development</div>";
    }
} else {
    echo "<div class='warning'>⚠️ SESSION_DRIVER not found in .env</div>";
}

// Check session lifetime
if (preg_match('/SESSION_LIFETIME=(.+)/', $envContent, $matches)) {
    $sessionLifetime = trim($matches[1]);
    echo "<div class='info'>Session lifetime: <strong>$sessionLifetime minutes</strong></div>";
    
    if ($sessionLifetime < 60) {
        echo "<div class='warning'>⚠️ Short session lifetime might cause frequent 419 errors</div>";
    }
}

// Check APP_KEY
if (preg_match('/APP_KEY=(.+)/', $envContent, $matches)) {
    $appKey = trim($matches[1]);
    if (empty($appKey) || $appKey === 'base64:') {
        echo "<div class='error'>❌ APP_KEY is missing or invalid</div>";
        echo "<div class='info'>💡 Run: php artisan key:generate</div>";
    } else {
        echo "<div class='success'>✅ APP_KEY is set</div>";
    }
}

// Check storage permissions
echo "<h2>📁 Storage Permissions Check</h2>";

$storagePath = $laravelRoot . '/storage';
$sessionPath = $storagePath . '/framework/sessions';

if (is_writable($storagePath)) {
    echo "<div class='success'>✅ Storage directory is writable</div>";
} else {
    echo "<div class='error'>❌ Storage directory is not writable</div>";
    echo "<div class='info'>💡 Run: chmod -R 775 storage</div>";
}

if (is_dir($sessionPath)) {
    if (is_writable($sessionPath)) {
        echo "<div class='success'>✅ Session directory is writable</div>";
    } else {
        echo "<div class='error'>❌ Session directory is not writable</div>";
    }
} else {
    echo "<div class='warning'>⚠️ Session directory doesn't exist</div>";
    echo "<div class='info'>💡 Laravel will create it automatically</div>";
}

// Quick fixes section
echo "<h2>🔧 Quick Fixes</h2>";

echo "<div class='info'>";
echo "<h3>1. Clear Browser Data</h3>";
echo "<p>• Press Ctrl+Shift+Delete (or Cmd+Shift+Delete on Mac)</p>";
echo "<p>• Clear cookies and cached data</p>";
echo "<p>• Refresh the page</p>";
echo "</div>";

echo "<div class='info'>";
echo "<h3>2. Laravel Cache Clear</h3>";
echo "<p>Run these commands in your Laravel directory:</p>";
echo "<pre>";
echo "php artisan config:clear\n";
echo "php artisan cache:clear\n";
echo "php artisan session:clear\n";
echo "php artisan view:clear";
echo "</pre>";
echo "</div>";

echo "<div class='info'>";
echo "<h3>3. Fix Session Driver</h3>";
echo "<p>If using Redis but Redis is not running, change in .env:</p>";
echo "<pre>SESSION_DRIVER=file</pre>";
echo "</div>";

echo "<div class='info'>";
echo "<h3>4. Increase Session Lifetime</h3>";
echo "<p>In .env file, increase session lifetime:</p>";
echo "<pre>SESSION_LIFETIME=1440  # 24 hours</pre>";
echo "</div>";

// Auto-fix button
echo "<h2>🚀 Auto-Fix Options</h2>";

if (isset($_GET['fix']) && $_GET['fix'] === 'session') {
    // Fix session driver
    $newEnvContent = preg_replace('/SESSION_DRIVER=.+/', 'SESSION_DRIVER=file', $envContent);
    $newEnvContent = preg_replace('/SESSION_LIFETIME=.+/', 'SESSION_LIFETIME=1440', $newEnvContent);
    
    if (file_put_contents($envFile, $newEnvContent)) {
        echo "<div class='success'>✅ Session configuration updated to use file driver with 24-hour lifetime</div>";
        echo "<div class='info'>💡 Please run: php artisan config:clear</div>";
    } else {
        echo "<div class='error'>❌ Failed to update .env file</div>";
    }
}

echo "<a href='?fix=session' class='btn'>Fix Session Configuration</a>";
echo "<a href='?' class='btn'>Refresh Analysis</a>";

// Test URLs
echo "<h2>🧪 Test Your Application</h2>";
echo "<a href='../dashboard' class='btn'>Test Dashboard</a>";
echo "<a href='../sales' class='btn'>Test Sales</a>";
echo "<a href='../inventory' class='btn'>Test Inventory</a>";
echo "<a href='../reports' class='btn'>Test Reports</a>";

echo "<h2>📊 Current Status</h2>";
echo "<div class='info'>";
echo "<p><strong>Laravel Root:</strong> $laravelRoot</p>";
echo "<p><strong>Current Time:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>";
echo "</div>";

echo "</div></body></html>";
?>
