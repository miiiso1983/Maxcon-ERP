<?php
/**
 * CACHE CLEAR TOOL
 * Clear Laravel caches via browser when SSH is not available
 * URL: https://your-domain.com/cache-clear.php
 */

set_time_limit(300);
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><title>Cache Clear Tool</title>";
echo "<style>body{font-family:Arial;margin:20px;} .container{max-width:800px;margin:0 auto;} .ok{color:green;font-weight:bold;} .error{color:red;font-weight:bold;} .warning{color:orange;font-weight:bold;} .section{margin:20px 0;padding:15px;border:1px solid #ddd;border-radius:5px;background:#fafafa;} .button{display:inline-block;padding:10px 20px;background:#007bff;color:white;text-decoration:none;border-radius:4px;margin:5px;} .success{background:#d4edda;border:1px solid #c3e6cb;}</style>";
echo "</head><body><div class='container'>";

echo "<h1>🧹 Laravel Cache Clear Tool</h1>";
echo "<p>Clear Laravel caches without SSH access.</p>";

$rootPath = dirname(__DIR__);
$cleared = [];
$errors = [];

// Handle cache clearing
if (isset($_GET['action'])) {
    echo "<div class='section'>";
    echo "<h2>🔄 Clearing Caches...</h2>";
    
    try {
        // Bootstrap Laravel
        require_once $rootPath . '/vendor/autoload.php';
        $app = require_once $rootPath . '/bootstrap/app.php';
        
        $action = $_GET['action'];
        
        switch ($action) {
            case 'config':
                try {
                    \Illuminate\Support\Facades\Artisan::call('config:clear');
                    $cleared[] = "Configuration cache cleared";
                    echo "<span class='ok'>✅ Configuration cache cleared</span><br>";
                } catch (Exception $e) {
                    $errors[] = "Config clear failed: " . $e->getMessage();
                    echo "<span class='error'>❌ Config clear failed: " . $e->getMessage() . "</span><br>";
                }
                break;
                
            case 'cache':
                try {
                    \Illuminate\Support\Facades\Artisan::call('cache:clear');
                    $cleared[] = "Application cache cleared";
                    echo "<span class='ok'>✅ Application cache cleared</span><br>";
                } catch (Exception $e) {
                    $errors[] = "Cache clear failed: " . $e->getMessage();
                    echo "<span class='error'>❌ Cache clear failed: " . $e->getMessage() . "</span><br>";
                }
                break;
                
            case 'view':
                try {
                    \Illuminate\Support\Facades\Artisan::call('view:clear');
                    $cleared[] = "View cache cleared";
                    echo "<span class='ok'>✅ View cache cleared</span><br>";
                } catch (Exception $e) {
                    $errors[] = "View clear failed: " . $e->getMessage();
                    echo "<span class='error'>❌ View clear failed: " . $e->getMessage() . "</span><br>";
                }
                break;
                
            case 'route':
                try {
                    \Illuminate\Support\Facades\Artisan::call('route:clear');
                    $cleared[] = "Route cache cleared";
                    echo "<span class='ok'>✅ Route cache cleared</span><br>";
                } catch (Exception $e) {
                    $errors[] = "Route clear failed: " . $e->getMessage();
                    echo "<span class='error'>❌ Route clear failed: " . $e->getMessage() . "</span><br>";
                }
                break;
                
            case 'all':
                $commands = ['config:clear', 'cache:clear', 'view:clear', 'route:clear'];
                foreach ($commands as $command) {
                    try {
                        \Illuminate\Support\Facades\Artisan::call($command);
                        $cleared[] = "$command executed";
                        echo "<span class='ok'>✅ $command executed</span><br>";
                    } catch (Exception $e) {
                        $errors[] = "$command failed: " . $e->getMessage();
                        echo "<span class='error'>❌ $command failed: " . $e->getMessage() . "</span><br>";
                    }
                }
                break;
                
            case 'files':
                // Clear cache files manually
                $cacheFiles = [
                    'bootstrap/cache/*.php',
                    'storage/framework/cache/data/*',
                    'storage/framework/views/*.php',
                    'storage/framework/sessions/*'
                ];
                
                foreach ($cacheFiles as $pattern) {
                    $files = glob($rootPath . '/' . $pattern);
                    foreach ($files as $file) {
                        if (is_file($file) && unlink($file)) {
                            $cleared[] = "Deleted: " . basename($file);
                        }
                    }
                }
                echo "<span class='ok'>✅ Cache files manually deleted</span><br>";
                break;
        }
        
    } catch (Exception $e) {
        echo "<span class='error'>❌ Laravel error: " . $e->getMessage() . "</span><br>";
    }
    
    echo "</div>";
}

// Show cache clearing options
echo "<div class='section'>";
echo "<h2>🎯 Cache Clearing Options</h2>";
echo "<p>Click the buttons below to clear different types of caches:</p>";

echo "<a href='?action=config' class='button'>Clear Config Cache</a>";
echo "<a href='?action=cache' class='button'>Clear App Cache</a>";
echo "<a href='?action=view' class='button'>Clear View Cache</a>";
echo "<a href='?action=route' class='button'>Clear Route Cache</a>";
echo "<a href='?action=files' class='button'>Clear Cache Files</a>";
echo "<br><br>";
echo "<a href='?action=all' class='button' style='background:#28a745;font-size:16px;'>🚀 CLEAR ALL CACHES</a>";

echo "</div>";

// Show current status
echo "<div class='section'>";
echo "<h2>📊 Current Status</h2>";

if (count($cleared) > 0) {
    echo "<div class='success' style='padding:15px;border-radius:5px;margin:10px 0;'>";
    echo "<h4>✅ Successfully Cleared:</h4>";
    foreach ($cleared as $item) {
        echo "• $item<br>";
    }
    echo "</div>";
}

if (count($errors) > 0) {
    echo "<div style='background:#f8d7da;padding:15px;border-radius:5px;margin:10px 0;'>";
    echo "<h4>❌ Errors:</h4>";
    foreach ($errors as $error) {
        echo "• $error<br>";
    }
    echo "</div>";
}

// Check cache directories
$cacheDirectories = [
    'bootstrap/cache' => $rootPath . '/bootstrap/cache',
    'storage/framework/cache' => $rootPath . '/storage/framework/cache',
    'storage/framework/views' => $rootPath . '/storage/framework/views',
    'storage/framework/sessions' => $rootPath . '/storage/framework/sessions'
];

echo "<h3>Cache Directory Status:</h3>";
foreach ($cacheDirectories as $name => $path) {
    if (is_dir($path)) {
        $fileCount = count(glob($path . '/*'));
        echo "<span class='ok'>✅ $name: $fileCount files</span><br>";
    } else {
        echo "<span class='error'>❌ $name: Directory missing</span><br>";
    }
}

echo "</div>";

// Next steps
echo "<div class='section'>";
echo "<h2>🎯 Next Steps</h2>";
echo "<ol>";
echo "<li><strong>Click 'CLEAR ALL CACHES' above</strong> if you haven't already</li>";
echo "<li><strong>Test your login:</strong> <a href='/login' target='_blank'>Go to Login Page</a></li>";
echo "<li><strong>Check session fix:</strong> <a href='session-fix.php' target='_blank'>Run Session Fix Again</a></li>";
echo "<li><strong>Test Laravel fix:</strong> <a href='laravel-login-fix.php' target='_blank'>Test Laravel Login</a></li>";
echo "</ol>";

echo "<div style='background:#e7f3ff;padding:15px;border-radius:5px;margin:20px 0;'>";
echo "<h3>🔍 After Clearing Caches:</h3>";
echo "<p>1. The session error should be resolved</p>";
echo "<p>2. Your login form should work without 'Service Unavailable'</p>";
echo "<p>3. You should be able to log in and reach the dashboard</p>";
echo "</div>";

echo "</div>";

echo "</div></body></html>";
?>
