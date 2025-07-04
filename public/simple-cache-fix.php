<?php
/**
 * SIMPLE CACHE FIX - No exec() functions
 * Fixes cache table error by switching to file caching
 * URL: https://your-domain.com/simple-cache-fix.php
 */

echo "<!DOCTYPE html><html><head><title>Simple Cache Fix</title>";
echo "<style>body{font-family:Arial;margin:20px;} .ok{color:green;} .error{color:red;} .warning{color:orange;}</style>";
echo "</head><body>";

echo "<h1>🔧 Simple Cache Fix</h1>";
echo "<p>Fixing cache table error...</p>";

$rootPath = dirname(__DIR__);
$envPath = $rootPath . '/.env';
$success = true;

// Step 1: Update .env file
echo "<h3>Step 1: Updating .env file</h3>";
if (file_exists($envPath)) {
    $envContent = file_get_contents($envPath);
    
    // Replace CACHE_DRIVER
    if (strpos($envContent, 'CACHE_DRIVER=database') !== false) {
        $newContent = str_replace('CACHE_DRIVER=database', 'CACHE_DRIVER=file', $envContent);
        if (file_put_contents($envPath, $newContent)) {
            echo "<span class='ok'>✅ Changed CACHE_DRIVER to file</span><br>";
        } else {
            echo "<span class='error'>❌ Failed to update .env</span><br>";
            $success = false;
        }
    } elseif (strpos($envContent, 'CACHE_DRIVER=') === false) {
        $newContent = $envContent . "\nCACHE_DRIVER=file\n";
        if (file_put_contents($envPath, $newContent)) {
            echo "<span class='ok'>✅ Added CACHE_DRIVER=file</span><br>";
        } else {
            echo "<span class='error'>❌ Failed to add CACHE_DRIVER</span><br>";
            $success = false;
        }
    } else {
        echo "<span class='ok'>✅ CACHE_DRIVER already set (not database)</span><br>";
    }
    
    // Also fix SESSION_DRIVER if it's database
    if (strpos($envContent, 'SESSION_DRIVER=database') !== false) {
        $newContent = str_replace('SESSION_DRIVER=database', 'SESSION_DRIVER=file', $newContent);
        if (file_put_contents($envPath, $newContent)) {
            echo "<span class='ok'>✅ Changed SESSION_DRIVER to file</span><br>";
        }
    }
} else {
    echo "<span class='error'>❌ .env file not found</span><br>";
    $success = false;
}

// Step 2: Create cache directories
echo "<h3>Step 2: Creating cache directories</h3>";
$directories = [
    $rootPath . '/storage/framework/cache',
    $rootPath . '/storage/framework/cache/data',
    $rootPath . '/storage/framework/sessions',
    $rootPath . '/storage/framework/views'
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "<span class='ok'>✅ Created: " . basename($dir) . "</span><br>";
        } else {
            echo "<span class='error'>❌ Failed to create: " . basename($dir) . "</span><br>";
        }
    } else {
        echo "<span class='ok'>✅ Exists: " . basename($dir) . "</span><br>";
    }
}

// Step 3: Clear cache files manually
echo "<h3>Step 3: Clearing cache files</h3>";
$cacheFiles = [
    $rootPath . '/bootstrap/cache/config.php',
    $rootPath . '/bootstrap/cache/routes-v7.php',
    $rootPath . '/bootstrap/cache/services.php'
];

foreach ($cacheFiles as $file) {
    if (file_exists($file)) {
        if (unlink($file)) {
            echo "<span class='ok'>✅ Cleared: " . basename($file) . "</span><br>";
        } else {
            echo "<span class='warning'>⚠️ Could not clear: " . basename($file) . "</span><br>";
        }
    } else {
        echo "<span class='ok'>✅ No cache file: " . basename($file) . "</span><br>";
    }
}

// Clear view cache directory
$viewCacheDir = $rootPath . '/storage/framework/views';
if (is_dir($viewCacheDir)) {
    $files = glob($viewCacheDir . '/*.php');
    $cleared = 0;
    foreach ($files as $file) {
        if (unlink($file)) {
            $cleared++;
        }
    }
    echo "<span class='ok'>✅ Cleared $cleared view cache files</span><br>";
}

// Step 4: Test the fix
echo "<h3>Step 4: Testing the fix</h3>";
if (file_exists($envPath)) {
    $envContent = file_get_contents($envPath);
    if (strpos($envContent, 'CACHE_DRIVER=file') !== false) {
        echo "<span class='ok'>✅ Cache driver is now set to file</span><br>";
    } else {
        echo "<span class='warning'>⚠️ Cache driver setting unclear</span><br>";
    }
}

if (is_dir($rootPath . '/storage/framework/cache/data')) {
    echo "<span class='ok'>✅ Cache directory exists</span><br>";
} else {
    echo "<span class='error'>❌ Cache directory missing</span><br>";
}

// Final result
echo "<h3>Result</h3>";
if ($success) {
    echo "<div style='padding:15px;background:#d4edda;border:1px solid #c3e6cb;border-radius:5px;'>";
    echo "<span class='ok'><strong>✅ FIX APPLIED SUCCESSFULLY!</strong></span><br>";
    echo "Your application should now work without cache table errors.<br>";
    echo "The application is now using file-based caching instead of database caching.";
    echo "</div>";
} else {
    echo "<div style='padding:15px;background:#f8d7da;border:1px solid #f5c6cb;border-radius:5px;'>";
    echo "<span class='error'><strong>❌ SOME ISSUES OCCURRED</strong></span><br>";
    echo "Please check file permissions and try again.";
    echo "</div>";
}

echo "<h3>Manual Commands (if needed)</h3>";
echo "<p>If the automatic fix didn't work, run these commands on your server:</p>";
echo "<pre style='background:#f0f0f0;padding:10px;border-radius:5px;'>";
echo "cd /home/1486247.cloudwaysapps.com/ufnpbxkvbd/public_html\n";
echo "sed -i 's/CACHE_DRIVER=database/CACHE_DRIVER=file/g' .env\n";
echo "mkdir -p storage/framework/cache/data\n";
echo "mkdir -p storage/framework/sessions\n";
echo "rm -f bootstrap/cache/config.php\n";
echo "rm -f storage/framework/views/*.php\n";
echo "</pre>";

echo "<p><strong>After applying the fix, test your application!</strong></p>";
echo "</body></html>";
?>
