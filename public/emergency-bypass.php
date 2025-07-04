<?php
/**
 * EMERGENCY BYPASS - Minimal PHP Test
 * This bypasses Laravel entirely to test basic server functionality
 * URL: https://your-domain.com/emergency-bypass.php
 */

// Basic error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

echo "<!DOCTYPE html><html><head><title>Emergency Bypass Test</title>";
echo "<style>body{font-family:Arial;margin:20px;} .ok{color:green;} .error{color:red;} .warning{color:orange;} .section{margin:20px 0;padding:15px;border:1px solid #ddd;}</style>";
echo "</head><body>";

echo "<h1>🚨 EMERGENCY BYPASS TEST</h1>";
echo "<p>Testing server functionality without Laravel...</p>";

$rootPath = dirname(__DIR__);

// Test 1: Basic PHP functionality
echo "<div class='section'>";
echo "<h2>Test 1: Basic PHP Functionality</h2>";
echo "<span class='ok'>✅ PHP is working</span><br>";
echo "PHP Version: " . PHP_VERSION . "<br>";
echo "Server Time: " . date('Y-m-d H:i:s') . "<br>";
echo "Memory Limit: " . ini_get('memory_limit') . "<br>";
echo "</div>";

// Test 2: File system access
echo "<div class='section'>";
echo "<h2>Test 2: File System Access</h2>";
$testFile = __DIR__ . '/test-write.txt';
if (file_put_contents($testFile, 'test')) {
    echo "<span class='ok'>✅ Can write files</span><br>";
    unlink($testFile);
} else {
    echo "<span class='error'>❌ Cannot write files</span><br>";
}

if (is_readable($rootPath)) {
    echo "<span class='ok'>✅ Can read Laravel directory</span><br>";
} else {
    echo "<span class='error'>❌ Cannot read Laravel directory</span><br>";
}
echo "</div>";

// Test 3: Critical Laravel files
echo "<div class='section'>";
echo "<h2>Test 3: Laravel Files Check</h2>";
$criticalFiles = [
    '.env' => $rootPath . '/.env',
    'vendor/autoload.php' => $rootPath . '/vendor/autoload.php',
    'bootstrap/app.php' => $rootPath . '/bootstrap/app.php',
    'artisan' => $rootPath . '/artisan'
];

foreach ($criticalFiles as $name => $path) {
    if (file_exists($path) && is_readable($path)) {
        echo "<span class='ok'>✅ $name exists and readable</span><br>";
    } else {
        echo "<span class='error'>❌ $name missing or unreadable</span><br>";
    }
}
echo "</div>";

// Test 4: Environment configuration
echo "<div class='section'>";
echo "<h2>Test 4: Environment Configuration</h2>";
$envPath = $rootPath . '/.env';
if (file_exists($envPath)) {
    $envContent = file_get_contents($envPath);
    echo "<span class='ok'>✅ .env file readable</span><br>";
    
    // Check for problematic settings
    if (strpos($envContent, 'CACHE_DRIVER=database') !== false) {
        echo "<span class='error'>❌ CACHE_DRIVER still set to database</span><br>";
        
        // Try to fix it
        $newContent = str_replace('CACHE_DRIVER=database', 'CACHE_DRIVER=file', $envContent);
        if (file_put_contents($envPath, $newContent)) {
            echo "<span class='ok'>✅ Fixed: Changed CACHE_DRIVER to file</span><br>";
        } else {
            echo "<span class='error'>❌ Failed to fix CACHE_DRIVER</span><br>";
        }
    } else {
        echo "<span class='ok'>✅ CACHE_DRIVER not set to database</span><br>";
    }
    
    if (strpos($envContent, 'APP_DEBUG=true') !== false) {
        echo "<span class='warning'>⚠️ APP_DEBUG is true (should be false for production)</span><br>";
    } else {
        echo "<span class='ok'>✅ APP_DEBUG setting OK</span><br>";
    }
} else {
    echo "<span class='error'>❌ .env file not found</span><br>";
}
echo "</div>";

// Test 5: Try to load Laravel (carefully)
echo "<div class='section'>";
echo "<h2>Test 5: Laravel Bootstrap Test</h2>";

try {
    // Clear any existing output buffers
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    // Try to include autoloader
    $autoloadPath = $rootPath . '/vendor/autoload.php';
    if (file_exists($autoloadPath)) {
        require_once $autoloadPath;
        echo "<span class='ok'>✅ Autoloader loaded successfully</span><br>";
        
        // Try to bootstrap Laravel
        $appPath = $rootPath . '/bootstrap/app.php';
        if (file_exists($appPath)) {
            $app = require_once $appPath;
            echo "<span class='ok'>✅ Laravel app bootstrapped</span><br>";
            
            // Try to get Laravel version
            if (method_exists($app, 'version')) {
                echo "Laravel Version: " . $app->version() . "<br>";
            }
            
        } else {
            echo "<span class='error'>❌ bootstrap/app.php not found</span><br>";
        }
    } else {
        echo "<span class='error'>❌ vendor/autoload.php not found</span><br>";
    }
    
} catch (Exception $e) {
    echo "<span class='error'>❌ Laravel bootstrap failed: " . $e->getMessage() . "</span><br>";
    echo "<div style='background:#f0f0f0;padding:10px;margin:10px 0;'>";
    echo "Error Details:<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
    echo "Message: " . $e->getMessage() . "<br>";
    echo "</div>";
} catch (Error $e) {
    echo "<span class='error'>❌ PHP Error: " . $e->getMessage() . "</span><br>";
}
echo "</div>";

// Test 6: Database connection (direct)
echo "<div class='section'>";
echo "<h2>Test 6: Direct Database Test</h2>";

if (file_exists($envPath)) {
    $envContent = file_get_contents($envPath);
    
    // Extract database credentials
    preg_match('/DB_HOST=(.+)/', $envContent, $hostMatch);
    preg_match('/DB_DATABASE=(.+)/', $envContent, $dbMatch);
    preg_match('/DB_USERNAME=(.+)/', $envContent, $userMatch);
    preg_match('/DB_PASSWORD=(.+)/', $envContent, $passMatch);
    
    $host = isset($hostMatch[1]) ? trim($hostMatch[1]) : '';
    $database = isset($dbMatch[1]) ? trim($dbMatch[1]) : '';
    $username = isset($userMatch[1]) ? trim($userMatch[1]) : '';
    $password = isset($passMatch[1]) ? trim($passMatch[1]) : '';
    
    if ($host && $database && $username) {
        try {
            $dsn = "mysql:host=$host;dbname=$database";
            $pdo = new PDO($dsn, $username, $password);
            echo "<span class='ok'>✅ Direct database connection successful</span><br>";
            echo "Connected to: $database on $host<br>";
        } catch (Exception $e) {
            echo "<span class='error'>❌ Database connection failed: " . $e->getMessage() . "</span><br>";
        }
    } else {
        echo "<span class='warning'>⚠️ Database credentials incomplete</span><br>";
    }
}
echo "</div>";

// Test 7: Server configuration
echo "<div class='section'>";
echo "<h2>Test 7: Server Configuration</h2>";
echo "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "<br>";
echo "Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "<br>";
echo "Script Name: " . ($_SERVER['SCRIPT_NAME'] ?? 'Unknown') . "<br>";
echo "Request URI: " . ($_SERVER['REQUEST_URI'] ?? 'Unknown') . "<br>";
echo "HTTP Host: " . ($_SERVER['HTTP_HOST'] ?? 'Unknown') . "<br>";

// Check for .htaccess
$htaccessPath = $rootPath . '/public/.htaccess';
if (file_exists($htaccessPath)) {
    echo "<span class='ok'>✅ .htaccess file exists</span><br>";
} else {
    echo "<span class='error'>❌ .htaccess file missing</span><br>";
}
echo "</div>";

// Recommendations
echo "<div class='section'>";
echo "<h2>🎯 Recommendations</h2>";

echo "<h3>If Laravel bootstrap failed:</h3>";
echo "1. Check composer dependencies: <code>composer install</code><br>";
echo "2. Clear all caches: <code>rm -rf bootstrap/cache/* storage/framework/cache/*</code><br>";
echo "3. Check file permissions: <code>chmod -R 755 storage bootstrap/cache</code><br>";

echo "<h3>If this test works but main site doesn't:</h3>";
echo "1. The issue is in Laravel routing or configuration<br>";
echo "2. Check <code>routes/web.php</code> for syntax errors<br>";
echo "3. Check service providers for issues<br>";

echo "<h3>If nothing works:</h3>";
echo "1. Contact Cloudways support immediately<br>";
echo "2. Check server error logs<br>";
echo "3. Verify domain DNS settings<br>";

echo "</div>";

echo "<div style='margin-top:30px;padding:20px;background:#e7f3ff;border:1px solid #0066cc;'>";
echo "<h3>🔍 Emergency Bypass Complete</h3>";
echo "<p>This test bypassed Laravel entirely. If you can see this page, your server is working.</p>";
echo "<p><strong>Next:</strong> Check the test results above to identify the specific issue.</p>";
echo "</div>";

echo "</body></html>";
?>
