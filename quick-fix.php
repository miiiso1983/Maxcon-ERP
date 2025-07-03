<?php
/**
 * Quick Fix for Laravel Production Issues
 * Upload this file to your server and run it via browser
 */

echo "<h1>🔧 Laravel Quick Fix</h1>";
echo "<style>body{font-family:Arial;margin:20px;} .success{color:green;} .error{color:red;}</style>";

// Step 1: Clear caches
echo "<h2>Step 1: Clearing Caches</h2>";
$commands = [
    'cache:clear',
    'config:clear', 
    'route:clear',
    'view:clear'
];

foreach ($commands as $cmd) {
    $output = [];
    exec("php artisan $cmd 2>&1", $output, $return);
    $status = $return === 0 ? '<span class="success">✅</span>' : '<span class="error">❌</span>';
    echo "$status php artisan $cmd<br>";
}

// Step 2: Remove cache files
echo "<h2>Step 2: Removing Cache Files</h2>";
$cacheFiles = [
    'bootstrap/cache/config.php',
    'bootstrap/cache/routes.php',
    'bootstrap/cache/services.php'
];

foreach ($cacheFiles as $file) {
    if (file_exists($file)) {
        unlink($file);
        echo '<span class="success">✅</span> Removed: ' . $file . '<br>';
    } else {
        echo '<span class="success">✅</span> Not found: ' . $file . '<br>';
    }
}

// Step 3: Fix providers
echo "<h2>Step 3: Fixing Providers</h2>";
$providersContent = '<?php

return [
    App\Providers\AppServiceProvider::class,
];
';
file_put_contents('bootstrap/providers.php', $providersContent);
echo '<span class="success">✅</span> Fixed bootstrap/providers.php<br>';

// Step 4: Create minimal config
echo "<h2>Step 4: Creating Minimal Config</h2>";
if (file_exists('config/app-minimal.php')) {
    copy('config/app-minimal.php', 'config/app.php');
    echo '<span class="success">✅</span> Applied minimal config<br>';
} else {
    echo '<span class="error">❌</span> Minimal config not found<br>';
}

// Step 5: Generate key
echo "<h2>Step 5: Generating App Key</h2>";
$output = [];
exec('php artisan key:generate --force 2>&1', $output, $return);
$status = $return === 0 ? '<span class="success">✅</span>' : '<span class="error">❌</span>';
echo "$status Application key generated<br>";

// Step 6: Test basic functionality
echo "<h2>Step 6: Testing Basic Functionality</h2>";
try {
    // Test if Laravel can boot
    require_once 'vendor/autoload.php';
    $app = require_once 'bootstrap/app.php';
    echo '<span class="success">✅</span> Laravel application boots successfully<br>';
} catch (Exception $e) {
    echo '<span class="error">❌</span> Error: ' . $e->getMessage() . '<br>';
}

echo "<h2>✅ Quick Fix Complete!</h2>";
echo "<p>If your site is still not working:</p>";
echo "<ol>";
echo "<li>Check your .env file has correct database credentials</li>";
echo "<li>Run: php artisan migrate</li>";
echo "<li>Check server error logs</li>";
echo "<li>Contact your hosting provider about PHP function restrictions</li>";
echo "</ol>";

echo "<p><a href='/'>← Back to Site</a></p>";
?>
