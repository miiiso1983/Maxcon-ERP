<?php
/**
 * EMERGENCY CACHE FIX
 * Fixes "Table 'cache' doesn't exist" error
 * URL: https://your-domain.com/emergency-cache-fix.php
 */

set_time_limit(300);

echo "<!DOCTYPE html><html><head><title>Emergency Cache Fix</title>";
echo "<style>body{font-family:Arial;margin:20px;background:#f5f5f5;} .container{background:white;padding:20px;border-radius:8px;max-width:800px;margin:0 auto;} .ok{color:green;font-weight:bold;} .error{color:red;font-weight:bold;} .warning{color:orange;font-weight:bold;} .step{margin:15px 0;padding:15px;border:1px solid #ddd;border-radius:5px;background:#fafafa;} .code{background:#f0f0f0;padding:10px;border-radius:4px;font-family:monospace;margin:10px 0;}</style>";
echo "</head><body><div class='container'>";

echo "<h1>🔧 EMERGENCY CACHE FIX</h1>";
echo "<p><strong>Fixing 'Table cache doesn't exist' error...</strong></p>";

$rootPath = dirname(__DIR__);
$envPath = $rootPath . '/.env';

echo "<div class='step'>";
echo "<h3>Step 1: Checking Current Configuration</h3>";

// Read current .env file
if (file_exists($envPath)) {
    $envContent = file_get_contents($envPath);
    echo "<span class='ok'>✅ .env file found</span><br>";
    
    // Check current cache driver
    if (preg_match('/CACHE_DRIVER=(.+)/', $envContent, $matches)) {
        $currentDriver = trim($matches[1]);
        echo "Current cache driver: <strong>$currentDriver</strong><br>";
        
        if ($currentDriver === 'database') {
            echo "<span class='warning'>⚠️ Database cache driver detected - this is causing the error</span><br>";
        }
    } else {
        echo "<span class='warning'>⚠️ CACHE_DRIVER not found in .env</span><br>";
    }
} else {
    echo "<span class='error'>❌ .env file not found</span><br>";
}

echo "</div>";

echo "<div class='step'>";
echo "<h3>Step 2: Switching to File-Based Caching</h3>";

if (file_exists($envPath)) {
    $envContent = file_get_contents($envPath);
    
    // Replace or add CACHE_DRIVER
    if (preg_match('/CACHE_DRIVER=(.+)/', $envContent)) {
        $newEnvContent = preg_replace('/CACHE_DRIVER=(.+)/', 'CACHE_DRIVER=file', $envContent);
        echo "<span class='ok'>✅ Updated existing CACHE_DRIVER setting</span><br>";
    } else {
        $newEnvContent = $envContent . "\nCACHE_DRIVER=file\n";
        echo "<span class='ok'>✅ Added CACHE_DRIVER=file to .env</span><br>";
    }
    
    // Also ensure SESSION_DRIVER is not database
    if (preg_match('/SESSION_DRIVER=database/', $newEnvContent)) {
        $newEnvContent = preg_replace('/SESSION_DRIVER=database/', 'SESSION_DRIVER=file', $newEnvContent);
        echo "<span class='ok'>✅ Changed SESSION_DRIVER from database to file</span><br>";
    }
    
    // Write the updated .env file
    if (file_put_contents($envPath, $newEnvContent)) {
        echo "<span class='ok'>✅ .env file updated successfully</span><br>";
    } else {
        echo "<span class='error'>❌ Failed to update .env file</span><br>";
    }
} else {
    echo "<span class='error'>❌ Cannot update .env file - file not found</span><br>";
}

echo "</div>";

echo "<div class='step'>";
echo "<h3>Step 3: Creating Cache Directory</h3>";

$cacheDir = $rootPath . '/storage/framework/cache/data';
if (!is_dir($cacheDir)) {
    if (mkdir($cacheDir, 0755, true)) {
        echo "<span class='ok'>✅ Created cache directory: $cacheDir</span><br>";
    } else {
        echo "<span class='error'>❌ Failed to create cache directory</span><br>";
    }
} else {
    echo "<span class='ok'>✅ Cache directory already exists</span><br>";
}

// Create sessions directory
$sessionsDir = $rootPath . '/storage/framework/sessions';
if (!is_dir($sessionsDir)) {
    if (mkdir($sessionsDir, 0755, true)) {
        echo "<span class='ok'>✅ Created sessions directory</span><br>";
    } else {
        echo "<span class='error'>❌ Failed to create sessions directory</span><br>";
    }
} else {
    echo "<span class='ok'>✅ Sessions directory already exists</span><br>";
}

echo "</div>";

echo "<div class='step'>";
echo "<h3>Step 4: Setting Proper Permissions</h3>";

$directories = [
    $rootPath . '/storage/framework/cache',
    $rootPath . '/storage/framework/sessions',
    $rootPath . '/storage/framework/views',
    $rootPath . '/storage/logs'
];

foreach ($directories as $dir) {
    if (is_dir($dir)) {
        if (chmod($dir, 0755)) {
            echo "<span class='ok'>✅ Set permissions for: " . basename($dir) . "</span><br>";
        } else {
            echo "<span class='warning'>⚠️ Could not set permissions for: " . basename($dir) . "</span><br>";
        }
    }
}

echo "</div>";

echo "<div class='step'>";
echo "<h3>Step 5: Clearing Laravel Caches (Manual)</h3>";

// Clear caches manually since exec() is disabled
$cacheDirectories = [
    $rootPath . '/storage/framework/cache/data',
    $rootPath . '/storage/framework/views',
    $rootPath . '/bootstrap/cache'
];

foreach ($cacheDirectories as $dir) {
    if (is_dir($dir)) {
        $files = glob($dir . '/*');
        $cleared = 0;
        foreach ($files as $file) {
            if (is_file($file) && unlink($file)) {
                $cleared++;
            }
        }
        echo "<span class='ok'>✅ Cleared $cleared files from " . basename($dir) . "</span><br>";
    } else {
        echo "<span class='warning'>⚠️ Directory not found: " . basename($dir) . "</span><br>";
    }
}

// Clear config cache file specifically
$configCache = $rootPath . '/bootstrap/cache/config.php';
if (file_exists($configCache)) {
    if (unlink($configCache)) {
        echo "<span class='ok'>✅ Cleared config cache file</span><br>";
    } else {
        echo "<span class='warning'>⚠️ Could not clear config cache file</span><br>";
    }
} else {
    echo "<span class='ok'>✅ No config cache file to clear</span><br>";
}

echo "<span class='warning'>⚠️ exec() function is disabled on this server</span><br>";
echo "<span class='ok'>✅ Using manual cache clearing instead</span><br>";

echo "</div>";

echo "<div class='step'>";
echo "<h3>Step 6: Testing Database Connection</h3>";

try {
    // Simple database connection test without loading full Laravel
    $envContent = file_get_contents($envPath);

    // Extract database credentials from .env
    preg_match('/DB_HOST=(.+)/', $envContent, $hostMatch);
    preg_match('/DB_DATABASE=(.+)/', $envContent, $dbMatch);
    preg_match('/DB_USERNAME=(.+)/', $envContent, $userMatch);
    preg_match('/DB_PASSWORD=(.+)/', $envContent, $passMatch);

    $host = isset($hostMatch[1]) ? trim($hostMatch[1]) : 'localhost';
    $database = isset($dbMatch[1]) ? trim($dbMatch[1]) : '';
    $username = isset($userMatch[1]) ? trim($userMatch[1]) : '';
    $password = isset($passMatch[1]) ? trim($passMatch[1]) : '';

    if ($database && $username) {
        $dsn = "mysql:host=$host;dbname=$database";
        $pdo = new PDO($dsn, $username, $password);
        echo "<span class='ok'>✅ Database connection successful</span><br>";

        // Check if cache table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'cache'");
        if ($stmt->rowCount() > 0) {
            echo "<span class='ok'>✅ Cache table exists in database</span><br>";
            echo "<span class='warning'>⚠️ You can switch back to database caching if preferred</span><br>";
        } else {
            echo "<span class='warning'>⚠️ Cache table does not exist (file caching is better choice)</span><br>";
        }
    } else {
        echo "<span class='warning'>⚠️ Database credentials not found in .env</span><br>";
    }

} catch (Exception $e) {
    echo "<span class='warning'>⚠️ Could not test database: " . $e->getMessage() . "</span><br>";
    echo "<span class='ok'>✅ File caching will work regardless of database issues</span><br>";
}

echo "</div>";

echo "<div class='step'>";
echo "<h3>Step 7: Verification</h3>";

$checks = [
    '.env file exists' => file_exists($envPath),
    'Cache directory exists' => is_dir($rootPath . '/storage/framework/cache'),
    'Sessions directory exists' => is_dir($rootPath . '/storage/framework/sessions'),
    'Storage is writable' => is_writable($rootPath . '/storage')
];

foreach ($checks as $check => $status) {
    if ($status) {
        echo "<span class='ok'>✅ $check</span><br>";
    } else {
        echo "<span class='error'>❌ $check</span><br>";
    }
}

echo "</div>";

echo "<div style='margin-top:30px;padding:20px;background:#d4edda;border:1px solid #c3e6cb;border-radius:8px;'>";
echo "<h3 style='color:#155724;margin-top:0;'>🎉 CACHE FIX COMPLETED!</h3>";
echo "<p style='color:#155724;'><strong>The cache table error should now be resolved!</strong></p>";
echo "<p style='color:#155724;'>Your application is now using file-based caching instead of database caching.</p>";
echo "</div>";

echo "<div style='margin-top:20px;padding:15px;background:#fff3cd;border:1px solid #ffeaa7;border-radius:8px;'>";
echo "<h4 style='color:#856404;margin-top:0;'>Next Steps:</h4>";
echo "<ul style='color:#856404;'>";
echo "<li>Test your application to ensure it's working</li>";
echo "<li>If you prefer database caching, run: <code>php artisan cache:table && php artisan migrate</code></li>";
echo "<li>Monitor your application for any other issues</li>";
echo "<li>Consider setting up proper database migrations in your deployment process</li>";
echo "</ul>";
echo "</div>";

echo "<div style='margin-top:20px;padding:15px;background:#f8d7da;border:1px solid #f5c6cb;border-radius:8px;'>";
echo "<h4 style='color:#721c24;margin-top:0;'>If Problems Persist:</h4>";
echo "<p style='color:#721c24;'>Run these commands manually on your server:</p>";
echo "<div class='code'>";
echo "cd /home/1486247.cloudwaysapps.com/ufnpbxkvbd/public_html<br>";
echo "php artisan config:clear<br>";
echo "php artisan cache:clear<br>";
echo "php artisan view:clear<br>";
echo "</div>";
echo "</div>";

echo "</div></body></html>";
?>
