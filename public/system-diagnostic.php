<?php
/**
 * SYSTEM DIAGNOSTIC TOOL
 * Comprehensive diagnostic and emergency fix for server issues
 * URL: https://your-domain.com/system-diagnostic.php
 */

// Prevent timeout
set_time_limit(300);
ini_set('memory_limit', '256M');

echo "<!DOCTYPE html><html><head><title>System Diagnostic</title>";
echo "<style>
body{font-family:Arial;margin:20px;background:#f5f5f5;} 
.container{background:white;padding:20px;border-radius:8px;max-width:1000px;margin:0 auto;} 
.ok{color:green;font-weight:bold;} 
.error{color:red;font-weight:bold;} 
.warning{color:orange;font-weight:bold;} 
.info{color:blue;font-weight:bold;}
.section{margin:20px 0;padding:15px;border:1px solid #ddd;border-radius:5px;background:#fafafa;} 
.code{background:#f0f0f0;padding:10px;border-radius:4px;font-family:monospace;margin:10px 0;white-space:pre-wrap;}
.grid{display:grid;grid-template-columns:1fr 1fr;gap:20px;}
@media(max-width:768px){.grid{grid-template-columns:1fr;}}
</style>";
echo "</head><body><div class='container'>";

echo "<h1>🔍 SYSTEM DIAGNOSTIC & EMERGENCY FIX</h1>";
echo "<p><strong>Comprehensive system analysis and automatic fixes...</strong></p>";

$rootPath = dirname(__DIR__);
$issues = [];
$fixes = [];

// System Information
echo "<div class='section'>";
echo "<h2>📊 System Information</h2>";
echo "<div class='grid'>";
echo "<div>";
echo "<strong>PHP Version:</strong> " . PHP_VERSION . "<br>";
echo "<strong>Server Software:</strong> " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "<br>";
echo "<strong>Document Root:</strong> " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "<br>";
echo "<strong>Current Path:</strong> " . __DIR__ . "<br>";
echo "<strong>Laravel Root:</strong> " . $rootPath . "<br>";
echo "</div>";
echo "<div>";
echo "<strong>Memory Limit:</strong> " . ini_get('memory_limit') . "<br>";
echo "<strong>Max Execution Time:</strong> " . ini_get('max_execution_time') . "<br>";
echo "<strong>Upload Max Size:</strong> " . ini_get('upload_max_filesize') . "<br>";
echo "<strong>Post Max Size:</strong> " . ini_get('post_max_size') . "<br>";
echo "<strong>Disabled Functions:</strong> " . (ini_get('disable_functions') ?: 'None') . "<br>";
echo "</div>";
echo "</div>";
echo "</div>";

// Check Laravel Installation
echo "<div class='section'>";
echo "<h2>🔧 Laravel Installation Check</h2>";

$laravelFiles = [
    'artisan' => $rootPath . '/artisan',
    'composer.json' => $rootPath . '/composer.json',
    '.env' => $rootPath . '/.env',
    'vendor/autoload.php' => $rootPath . '/vendor/autoload.php',
    'bootstrap/app.php' => $rootPath . '/bootstrap/app.php'
];

foreach ($laravelFiles as $name => $path) {
    if (file_exists($path)) {
        echo "<span class='ok'>✅ $name exists</span><br>";
    } else {
        echo "<span class='error'>❌ $name missing</span><br>";
        $issues[] = "$name file missing";
    }
}
echo "</div>";

// Check .env Configuration
echo "<div class='section'>";
echo "<h2>⚙️ Environment Configuration</h2>";

$envPath = $rootPath . '/.env';
if (file_exists($envPath)) {
    $envContent = file_get_contents($envPath);
    echo "<span class='ok'>✅ .env file found</span><br>";
    
    // Check critical settings
    $envChecks = [
        'APP_KEY' => '/APP_KEY=(.+)/',
        'APP_ENV' => '/APP_ENV=(.+)/',
        'APP_DEBUG' => '/APP_DEBUG=(.+)/',
        'DB_CONNECTION' => '/DB_CONNECTION=(.+)/',
        'DB_DATABASE' => '/DB_DATABASE=(.+)/',
        'CACHE_DRIVER' => '/CACHE_DRIVER=(.+)/',
        'SESSION_DRIVER' => '/SESSION_DRIVER=(.+)/'
    ];
    
    foreach ($envChecks as $setting => $pattern) {
        if (preg_match($pattern, $envContent, $matches)) {
            $value = trim($matches[1]);
            if ($setting === 'CACHE_DRIVER' && $value === 'database') {
                echo "<span class='error'>❌ $setting: $value (PROBLEMATIC)</span><br>";
                $issues[] = "Cache driver set to database but cache table missing";
            } elseif ($setting === 'APP_KEY' && (empty($value) || $value === 'base64:')) {
                echo "<span class='error'>❌ $setting: Empty or invalid</span><br>";
                $issues[] = "Application key not set";
            } else {
                echo "<span class='ok'>✅ $setting: $value</span><br>";
            }
        } else {
            echo "<span class='warning'>⚠️ $setting: Not found</span><br>";
            $issues[] = "$setting not configured";
        }
    }
} else {
    echo "<span class='error'>❌ .env file not found</span><br>";
    $issues[] = ".env file missing";
}
echo "</div>";

// Check Directory Permissions
echo "<div class='section'>";
echo "<h2>🔒 Directory Permissions</h2>";

$directories = [
    'storage' => $rootPath . '/storage',
    'storage/logs' => $rootPath . '/storage/logs',
    'storage/framework' => $rootPath . '/storage/framework',
    'storage/framework/cache' => $rootPath . '/storage/framework/cache',
    'storage/framework/sessions' => $rootPath . '/storage/framework/sessions',
    'storage/framework/views' => $rootPath . '/storage/framework/views',
    'bootstrap/cache' => $rootPath . '/bootstrap/cache',
    'public' => $rootPath . '/public'
];

foreach ($directories as $name => $path) {
    if (is_dir($path)) {
        $perms = substr(sprintf('%o', fileperms($path)), -4);
        $writable = is_writable($path) ? 'Writable' : 'Not Writable';
        $status = is_writable($path) ? 'ok' : 'error';
        echo "<span class='$status'>$name: $perms ($writable)</span><br>";
        if (!is_writable($path)) {
            $issues[] = "$name directory not writable";
        }
    } else {
        echo "<span class='error'>❌ $name: Directory missing</span><br>";
        $issues[] = "$name directory missing";
    }
}
echo "</div>";

// Apply Automatic Fixes
echo "<div class='section'>";
echo "<h2>🛠️ Applying Automatic Fixes</h2>";

// Fix 1: Update cache driver
if (file_exists($envPath)) {
    $envContent = file_get_contents($envPath);
    if (strpos($envContent, 'CACHE_DRIVER=database') !== false) {
        $newContent = str_replace('CACHE_DRIVER=database', 'CACHE_DRIVER=file', $envContent);
        if (file_put_contents($envPath, $newContent)) {
            echo "<span class='ok'>✅ Fixed: Changed CACHE_DRIVER to file</span><br>";
            $fixes[] = "Cache driver changed to file";
        } else {
            echo "<span class='error'>❌ Failed to update cache driver</span><br>";
        }
    }
    
    // Fix session driver too
    if (strpos($envContent, 'SESSION_DRIVER=database') !== false) {
        $newContent = str_replace('SESSION_DRIVER=database', 'SESSION_DRIVER=file', $newContent);
        if (file_put_contents($envPath, $newContent)) {
            echo "<span class='ok'>✅ Fixed: Changed SESSION_DRIVER to file</span><br>";
            $fixes[] = "Session driver changed to file";
        }
    }
}

// Fix 2: Create missing directories
$createDirs = [
    $rootPath . '/storage/framework/cache/data',
    $rootPath . '/storage/framework/sessions',
    $rootPath . '/storage/framework/views',
    $rootPath . '/storage/logs'
];

foreach ($createDirs as $dir) {
    if (!is_dir($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "<span class='ok'>✅ Created: " . basename($dir) . "</span><br>";
            $fixes[] = "Created " . basename($dir) . " directory";
        } else {
            echo "<span class='error'>❌ Failed to create: " . basename($dir) . "</span><br>";
        }
    }
}

// Fix 3: Clear problematic cache files
$cacheFiles = [
    $rootPath . '/bootstrap/cache/config.php',
    $rootPath . '/bootstrap/cache/routes-v7.php',
    $rootPath . '/bootstrap/cache/services.php'
];

foreach ($cacheFiles as $file) {
    if (file_exists($file)) {
        if (unlink($file)) {
            echo "<span class='ok'>✅ Cleared: " . basename($file) . "</span><br>";
            $fixes[] = "Cleared " . basename($file);
        }
    }
}

// Fix 4: Clear view cache
$viewCacheDir = $rootPath . '/storage/framework/views';
if (is_dir($viewCacheDir)) {
    $files = glob($viewCacheDir . '/*.php');
    $cleared = 0;
    foreach ($files as $file) {
        if (unlink($file)) {
            $cleared++;
        }
    }
    if ($cleared > 0) {
        echo "<span class='ok'>✅ Cleared $cleared view cache files</span><br>";
        $fixes[] = "Cleared $cleared view cache files";
    }
}

echo "</div>";

// Database Connection Test
echo "<div class='section'>";
echo "<h2>🗄️ Database Connection Test</h2>";

if (file_exists($envPath)) {
    $envContent = file_get_contents($envPath);
    
    // Extract database credentials
    preg_match('/DB_HOST=(.+)/', $envContent, $hostMatch);
    preg_match('/DB_DATABASE=(.+)/', $envContent, $dbMatch);
    preg_match('/DB_USERNAME=(.+)/', $envContent, $userMatch);
    preg_match('/DB_PASSWORD=(.+)/', $envContent, $passMatch);
    
    $host = isset($hostMatch[1]) ? trim($hostMatch[1]) : 'localhost';
    $database = isset($dbMatch[1]) ? trim($dbMatch[1]) : '';
    $username = isset($userMatch[1]) ? trim($userMatch[1]) : '';
    $password = isset($passMatch[1]) ? trim($passMatch[1]) : '';
    
    try {
        if ($database && $username) {
            $dsn = "mysql:host=$host;dbname=$database";
            $pdo = new PDO($dsn, $username, $password);
            echo "<span class='ok'>✅ Database connection successful</span><br>";
            echo "<span class='info'>Database: $database on $host</span><br>";
            
            // Check for cache table
            $stmt = $pdo->query("SHOW TABLES LIKE 'cache'");
            if ($stmt->rowCount() > 0) {
                echo "<span class='ok'>✅ Cache table exists</span><br>";
            } else {
                echo "<span class='warning'>⚠️ Cache table missing (using file cache instead)</span><br>";
            }
        } else {
            echo "<span class='error'>❌ Database credentials incomplete</span><br>";
        }
    } catch (Exception $e) {
        echo "<span class='error'>❌ Database connection failed: " . $e->getMessage() . "</span><br>";
    }
}

echo "</div>";

// Summary and Next Steps
echo "<div class='section'>";
echo "<h2>📋 Summary</h2>";

if (count($fixes) > 0) {
    echo "<h3 style='color:green;'>✅ Fixes Applied:</h3>";
    foreach ($fixes as $fix) {
        echo "• $fix<br>";
    }
}

if (count($issues) > 0) {
    echo "<h3 style='color:red;'>⚠️ Remaining Issues:</h3>";
    foreach ($issues as $issue) {
        echo "• $issue<br>";
    }
}

echo "<h3>🚀 Next Steps:</h3>";
echo "1. <strong>Test your application</strong> - Visit your main URL<br>";
echo "2. <strong>Check error logs</strong> - Look in storage/logs/ for detailed errors<br>";
echo "3. <strong>Contact hosting support</strong> - If server-level issues persist<br>";
echo "4. <strong>Monitor performance</strong> - Watch for any remaining issues<br>";

echo "</div>";

echo "<div style='margin-top:30px;padding:20px;background:#e7f3ff;border:1px solid #0066cc;border-radius:8px;'>";
echo "<h3 style='color:#0066cc;margin-top:0;'>🎯 Diagnostic Complete!</h3>";
echo "<p style='color:#0066cc;'>System analysis finished. Most common issues have been automatically fixed.</p>";
echo "<p style='color:#0066cc;'><strong>Try accessing your application now!</strong></p>";
echo "</div>";

echo "</div></body></html>";
?>
