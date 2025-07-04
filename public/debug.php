<?php
/**
 * Emergency Debug Tool for Internal Server Error
 * Upload this to public directory and access via browser
 */

// Prevent any output buffering
ob_start();

echo "<!DOCTYPE html><html><head><title>Emergency Debug</title>";
echo "<style>body{font-family:monospace;margin:20px;background:#f5f5f5;} .error{color:red;background:#ffe6e6;padding:10px;margin:10px 0;} .success{color:green;background:#e6ffe6;padding:10px;margin:10px 0;} .warning{color:orange;background:#fff3cd;padding:10px;margin:10px 0;} .info{background:#e6f3ff;padding:10px;margin:10px 0;} pre{background:white;padding:10px;border:1px solid #ddd;overflow:auto;}</style>";
echo "</head><body>";

echo "<h1>🚨 Emergency Debug Tool</h1>";
echo "<p>Time: " . date('Y-m-d H:i:s') . "</p>";

// Step 1: Basic PHP Info
echo "<div class='info'>";
echo "<h2>1. PHP Environment</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Server: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Script Path: " . __FILE__ . "<br>";
echo "</div>";

// Step 2: Check critical files
echo "<div class='info'>";
echo "<h2>2. Critical Files Check</h2>";
$criticalFiles = [
    '../.env' => '.env file',
    '../vendor/autoload.php' => 'Composer autoloader',
    '../bootstrap/app.php' => 'Laravel bootstrap',
    '../config/app.php' => 'App config',
    '../artisan' => 'Artisan CLI'
];

foreach ($criticalFiles as $file => $name) {
    if (file_exists($file)) {
        echo "✅ $name: EXISTS<br>";
    } else {
        echo "❌ $name: MISSING<br>";
    }
}
echo "</div>";

// Step 3: Check permissions
echo "<div class='info'>";
echo "<h2>3. Permissions Check</h2>";
$dirs = [
    '../storage' => 'Storage directory',
    '../bootstrap/cache' => 'Bootstrap cache',
    '../storage/logs' => 'Logs directory',
    '../storage/framework' => 'Framework cache'
];

foreach ($dirs as $dir => $name) {
    if (file_exists($dir)) {
        $perms = substr(sprintf('%o', fileperms($dir)), -4);
        $writable = is_writable($dir) ? '✅ WRITABLE' : '❌ NOT WRITABLE';
        echo "$name: $perms $writable<br>";
    } else {
        echo "$name: ❌ MISSING<br>";
    }
}
echo "</div>";

// Step 4: Try to load Laravel
echo "<div class='info'>";
echo "<h2>4. Laravel Loading Test</h2>";
try {
    if (file_exists('../vendor/autoload.php')) {
        require_once '../vendor/autoload.php';
        echo "✅ Autoloader loaded successfully<br>";
        
        if (file_exists('../bootstrap/app.php')) {
            echo "✅ Bootstrap file exists<br>";
            
            // Try to create app instance
            $app = require_once '../bootstrap/app.php';
            echo "✅ Laravel app created successfully<br>";
            
            // Try to get config
            $appName = $app->make('config')->get('app.name', 'Unknown');
            echo "✅ Config accessible: App name = $appName<br>";
            
        } else {
            echo "❌ Bootstrap file missing<br>";
        }
    } else {
        echo "❌ Autoloader missing - run 'composer install'<br>";
    }
} catch (Exception $e) {
    echo "<div class='error'>";
    echo "❌ Laravel Error: " . htmlspecialchars($e->getMessage()) . "<br>";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "<br>";
    echo "</div>";
} catch (Error $e) {
    echo "<div class='error'>";
    echo "❌ PHP Error: " . htmlspecialchars($e->getMessage()) . "<br>";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "<br>";
    echo "</div>";
}
echo "</div>";

// Step 5: Check .env file
echo "<div class='info'>";
echo "<h2>5. Environment File Check</h2>";
if (file_exists('../.env')) {
    echo "✅ .env file exists<br>";
    $envContent = file_get_contents('../.env');
    $lines = explode("\n", $envContent);
    
    $criticalVars = ['APP_KEY', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'];
    foreach ($criticalVars as $var) {
        $found = false;
        foreach ($lines as $line) {
            if (strpos($line, $var . '=') === 0) {
                $value = substr($line, strlen($var) + 1);
                if (!empty(trim($value))) {
                    echo "✅ $var: SET<br>";
                } else {
                    echo "⚠️ $var: EMPTY<br>";
                }
                $found = true;
                break;
            }
        }
        if (!$found) {
            echo "❌ $var: MISSING<br>";
        }
    }
} else {
    echo "❌ .env file missing<br>";
}
echo "</div>";

// Step 6: Check error logs
echo "<div class='info'>";
echo "<h2>6. Recent Error Logs</h2>";
$logFiles = [
    '../storage/logs/laravel.log',
    '/var/log/apache2/error.log',
    '/var/log/nginx/error.log',
    ini_get('error_log')
];

foreach ($logFiles as $logFile) {
    if ($logFile && file_exists($logFile) && is_readable($logFile)) {
        echo "<h3>$logFile (last 20 lines):</h3>";
        $lines = file($logFile);
        $recentLines = array_slice($lines, -20);
        echo "<pre>" . htmlspecialchars(implode('', $recentLines)) . "</pre>";
        break;
    }
}
echo "</div>";

// Step 7: Check .htaccess
echo "<div class='info'>";
echo "<h2>7. .htaccess Configuration</h2>";
if (file_exists('.htaccess')) {
    echo "✅ .htaccess file exists<br>";
    $htaccess = file_get_contents('.htaccess');

    // Check for problematic directives
    $problems = [];
    if (strpos($htaccess, 'FcgidInitialEnv') !== false) {
        $problems[] = 'FcgidInitialEnv (causes server errors)';
    }
    if (strpos($htaccess, 'FcgidMaxRequestLen') !== false) {
        $problems[] = 'FcgidMaxRequestLen (not allowed in .htaccess)';
    }

    if (count($problems) > 0) {
        echo "<span class='error'>❌ .htaccess Problems Found:</span><br>";
        foreach ($problems as $problem) {
            echo "&nbsp;&nbsp;• $problem<br>";
        }
        echo "<br><strong>Solution:</strong> <a href='htaccess-fix.php'>Fix .htaccess automatically</a><br>";
    } else {
        echo "✅ .htaccess appears to be clean<br>";
    }
} else {
    echo "❌ .htaccess file missing<br>";
}
echo "</div>";

// Step 8: Quick fixes
echo "<div class='warning'>";
echo "<h2>8. Quick Fixes to Try</h2>";
echo "<ol>";
echo "<li><strong>Fix .htaccess:</strong> <a href='htaccess-fix.php'>Run .htaccess Fix Tool</a></li>";
echo "<li><strong>Clear caches:</strong> Delete bootstrap/cache/* files</li>";
echo "<li><strong>Fix permissions:</strong> chmod 775 storage bootstrap/cache</li>";
echo "<li><strong>Test minimal .htaccess:</strong> Rename .htaccess to .htaccess.bak</li>";
echo "<li><strong>Composer:</strong> Run 'composer install --no-dev'</li>";
echo "<li><strong>Generate key:</strong> Run 'php artisan key:generate'</li>";
echo "</ol>";
echo "</div>";

// Step 8: Auto-fix attempt
echo "<div class='warning'>";
echo "<h2>8. Auto-Fix Attempt</h2>";

// Try to clear cache files
$cacheFiles = glob('../bootstrap/cache/*.php');
foreach ($cacheFiles as $file) {
    if (unlink($file)) {
        echo "✅ Deleted: " . basename($file) . "<br>";
    }
}

// Try to create storage directories
$dirs = ['../storage/logs', '../storage/framework/cache', '../storage/framework/sessions', '../storage/framework/views'];
foreach ($dirs as $dir) {
    if (!file_exists($dir)) {
        if (mkdir($dir, 0775, true)) {
            echo "✅ Created: $dir<br>";
        }
    }
}

echo "</div>";

echo "<div class='success'>";
echo "<h2>✅ Debug Complete</h2>";
echo "<p>If the issue persists:</p>";
echo "<ul>";
echo "<li>Check your hosting provider's error logs</li>";
echo "<li>Contact support with the information above</li>";
echo "<li>Try accessing: <a href='simple-test.php'>simple-test.php</a></li>";
echo "</ul>";
echo "</div>";

echo "</body></html>";

// Flush output
ob_end_flush();
?>
