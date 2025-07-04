<?php
/**
 * EMERGENCY SESSION FIX
 * Direct fix for "Target class [session] does not exist" error
 * URL: https://your-domain.com/session-emergency-fix.php
 */

set_time_limit(300);
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><title>Emergency Session Fix</title>";
echo "<style>body{font-family:Arial;margin:20px;} .container{max-width:800px;margin:0 auto;} .ok{color:green;font-weight:bold;} .error{color:red;font-weight:bold;} .warning{color:orange;font-weight:bold;} .section{margin:20px 0;padding:15px;border:1px solid #ddd;border-radius:5px;background:#fafafa;} .button{display:inline-block;padding:10px 20px;background:#007bff;color:white;text-decoration:none;border-radius:4px;margin:5px;} .success{background:#d4edda;border:1px solid #c3e6cb;padding:15px;border-radius:5px;}</style>";
echo "</head><body><div class='container'>";

echo "<h1>🚨 Emergency Session Fix</h1>";
echo "<p>Direct fix for Laravel session service errors.</p>";

$rootPath = dirname(__DIR__);
$fixes = [];

// STEP 1: Force delete all cache files
echo "<div class='section'>";
echo "<h2>🗑️ Step 1: Force Delete All Cache Files</h2>";

$cacheFiles = [
    'bootstrap/cache/config.php',
    'bootstrap/cache/routes-v7.php',
    'bootstrap/cache/packages.php',
    'bootstrap/cache/services.php'
];

foreach ($cacheFiles as $file) {
    $fullPath = $rootPath . '/' . $file;
    if (file_exists($fullPath)) {
        if (unlink($fullPath)) {
            echo "<span class='ok'>✅ Deleted: $file</span><br>";
            $fixes[] = "Deleted $file";
        } else {
            echo "<span class='error'>❌ Failed to delete: $file</span><br>";
        }
    } else {
        echo "<span class='warning'>⚠️ Not found: $file</span><br>";
    }
}

// Clear storage cache directories
$storageDirs = [
    'storage/framework/cache/data',
    'storage/framework/views',
    'storage/framework/sessions'
];

foreach ($storageDirs as $dir) {
    $fullPath = $rootPath . '/' . $dir;
    if (is_dir($fullPath)) {
        $files = glob($fullPath . '/*');
        $deleted = 0;
        foreach ($files as $file) {
            if (is_file($file) && unlink($file)) {
                $deleted++;
            }
        }
        echo "<span class='ok'>✅ Cleared $dir ($deleted files)</span><br>";
        $fixes[] = "Cleared $dir";
    }
}

echo "</div>";

// STEP 2: Fix .env session configuration
echo "<div class='section'>";
echo "<h2>⚙️ Step 2: Fix .env Session Configuration</h2>";

$envPath = $rootPath . '/.env';
if (file_exists($envPath)) {
    $envContent = file_get_contents($envPath);
    $originalContent = $envContent;
    
    // Force session driver to file
    if (strpos($envContent, 'SESSION_DRIVER=') !== false) {
        $envContent = preg_replace('/SESSION_DRIVER=.*/', 'SESSION_DRIVER=file', $envContent);
    } else {
        $envContent .= "\nSESSION_DRIVER=file\n";
    }
    
    // Ensure session lifetime
    if (strpos($envContent, 'SESSION_LIFETIME=') !== false) {
        $envContent = preg_replace('/SESSION_LIFETIME=.*/', 'SESSION_LIFETIME=120', $envContent);
    } else {
        $envContent .= "SESSION_LIFETIME=120\n";
    }
    
    // Ensure session encrypt is false
    if (strpos($envContent, 'SESSION_ENCRYPT=') !== false) {
        $envContent = preg_replace('/SESSION_ENCRYPT=.*/', 'SESSION_ENCRYPT=false', $envContent);
    } else {
        $envContent .= "SESSION_ENCRYPT=false\n";
    }
    
    // Add session connection if missing
    if (strpos($envContent, 'SESSION_CONNECTION=') === false) {
        $envContent .= "SESSION_CONNECTION=null\n";
    }
    
    if ($envContent !== $originalContent) {
        if (file_put_contents($envPath, $envContent)) {
            echo "<span class='ok'>✅ Updated .env session configuration</span><br>";
            $fixes[] = "Updated .env session settings";
        } else {
            echo "<span class='error'>❌ Failed to update .env</span><br>";
        }
    } else {
        echo "<span class='ok'>✅ .env session configuration already correct</span><br>";
    }
} else {
    echo "<span class='error'>❌ .env file not found</span><br>";
}

echo "</div>";

// STEP 3: Create session directories
echo "<div class='section'>";
echo "<h2>📁 Step 3: Create Session Directories</h2>";

$sessionDirs = [
    'storage/framework/sessions',
    'storage/framework/cache',
    'storage/framework/cache/data',
    'storage/framework/views',
    'storage/logs'
];

foreach ($sessionDirs as $dir) {
    $fullPath = $rootPath . '/' . $dir;
    if (!is_dir($fullPath)) {
        if (mkdir($fullPath, 0755, true)) {
            echo "<span class='ok'>✅ Created: $dir</span><br>";
            $fixes[] = "Created $dir";
        } else {
            echo "<span class='error'>❌ Failed to create: $dir</span><br>";
        }
    } else {
        echo "<span class='ok'>✅ Exists: $dir</span><br>";
    }
    
    // Fix permissions
    if (is_dir($fullPath) && !is_writable($fullPath)) {
        if (chmod($fullPath, 0755)) {
            echo "<span class='ok'>✅ Fixed permissions: $dir</span><br>";
            $fixes[] = "Fixed permissions for $dir";
        }
    }
}

echo "</div>";

// STEP 4: Test Laravel bootstrap
echo "<div class='section'>";
echo "<h2>🧪 Step 4: Test Laravel Bootstrap</h2>";

try {
    // Clear any existing autoloader cache
    if (function_exists('opcache_reset')) {
        opcache_reset();
    }
    
    require_once $rootPath . '/vendor/autoload.php';
    $app = require_once $rootPath . '/bootstrap/app.php';
    
    echo "<span class='ok'>✅ Laravel application loaded</span><br>";
    
    // Try to boot the application
    try {
        $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
        $app->boot();
        echo "<span class='ok'>✅ Laravel application booted</span><br>";
        
        // Test session service
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
        echo "<span class='error'>❌ Laravel boot error: " . $e->getMessage() . "</span><br>";
    }
    
} catch (Exception $e) {
    echo "<span class='error'>❌ Laravel load error: " . $e->getMessage() . "</span><br>";
}

echo "</div>";

// STEP 5: Summary and next steps
echo "<div class='section'>";
echo "<h2>📋 Summary</h2>";

if (count($fixes) > 0) {
    echo "<div class='success'>";
    echo "<h3>✅ Emergency Fixes Applied:</h3>";
    foreach ($fixes as $fix) {
        echo "• $fix<br>";
    }
    echo "</div>";
} else {
    echo "<span class='warning'>⚠️ No fixes were needed or applied</span><br>";
}

echo "<h3>🎯 Next Steps:</h3>";
echo "<ol>";
echo "<li><strong>Test session fix again:</strong> <a href='session-fix.php' target='_blank'>Run Session Fix</a></li>";
echo "<li><strong>Test your login:</strong> <a href='/login' target='_blank'>Go to Login Page</a></li>";
echo "<li><strong>Test Laravel login:</strong> <a href='laravel-login-fix.php' target='_blank'>Test Laravel Login</a></li>";
echo "</ol>";

echo "<div style='background:#e7f3ff;padding:15px;border-radius:5px;margin:20px 0;'>";
echo "<h3>🔍 Expected Results:</h3>";
echo "<p>After this emergency fix:</p>";
echo "<p>✅ Session service should be available</p>";
echo "<p>✅ Login form should work without errors</p>";
echo "<p>✅ No more 'Target class [session] does not exist'</p>";
echo "<p>✅ No more 'Service Unavailable' errors</p>";
echo "</div>";

echo "</div>";

echo "</div></body></html>";
?>
