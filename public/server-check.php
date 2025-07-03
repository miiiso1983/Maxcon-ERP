<?php
/**
 * Server Compatibility Check for Laravel
 * Upload to public directory and access via browser
 */

echo "<!DOCTYPE html><html><head><title>Server Check</title>";
echo "<style>body{font-family:Arial;margin:20px;} .ok{color:green;} .error{color:red;} .warning{color:orange;} .section{margin:20px 0;padding:15px;border:1px solid #ddd;}</style>";
echo "</head><body>";

echo "<h1>🔍 Laravel Server Compatibility Check</h1>";

// PHP Version
echo "<div class='section'>";
echo "<h2>PHP Version</h2>";
$phpVersion = phpversion();
echo "Current: $phpVersion ";
if (version_compare($phpVersion, '8.1.0', '>=')) {
    echo "<span class='ok'>✅ Compatible</span>";
} else {
    echo "<span class='error'>❌ Requires PHP 8.1+</span>";
}
echo "</div>";

// Required Extensions
echo "<div class='section'>";
echo "<h2>Required Extensions</h2>";
$required = ['bcmath', 'ctype', 'curl', 'dom', 'fileinfo', 'json', 'mbstring', 'openssl', 'pcre', 'pdo', 'tokenizer', 'xml'];
foreach ($required as $ext) {
    $status = extension_loaded($ext) ? "<span class='ok'>✅</span>" : "<span class='error'>❌</span>";
    echo "$ext: $status<br>";
}
echo "</div>";

// Critical Functions
echo "<div class='section'>";
echo "<h2>Critical Functions</h2>";
$functions = ['file_get_contents', 'file_put_contents', 'fopen', 'fwrite', 'fclose'];
foreach ($functions as $func) {
    $status = function_exists($func) ? "<span class='ok'>✅</span>" : "<span class='error'>❌</span>";
    echo "$func: $status<br>";
}

// Check highlight_file specifically
$highlight = function_exists('highlight_file') ? "<span class='ok'>✅ Available</span>" : "<span class='warning'>⚠️ Disabled (will cause errors)</span>";
echo "highlight_file: $highlight<br>";
echo "</div>";

// File Permissions
echo "<div class='section'>";
echo "<h2>File Permissions</h2>";
$paths = [
    '../storage' => 'Storage Directory',
    '../bootstrap/cache' => 'Bootstrap Cache',
    '../.env' => 'Environment File'
];

foreach ($paths as $path => $name) {
    if (file_exists($path)) {
        $writable = is_writable($path) ? "<span class='ok'>✅ Writable</span>" : "<span class='error'>❌ Not Writable</span>";
        echo "$name: $writable<br>";
    } else {
        echo "$name: <span class='error'>❌ Not Found</span><br>";
    }
}
echo "</div>";

// Laravel Files
echo "<div class='section'>";
echo "<h2>Laravel Installation</h2>";
$files = [
    '../vendor/autoload.php' => 'Composer Autoloader',
    '../bootstrap/app.php' => 'Laravel Bootstrap',
    '../.env' => 'Environment File',
    '../artisan' => 'Artisan CLI'
];

foreach ($files as $file => $name) {
    $exists = file_exists($file) ? "<span class='ok'>✅</span>" : "<span class='error'>❌</span>";
    echo "$name: $exists<br>";
}
echo "</div>";

// Memory and Limits
echo "<div class='section'>";
echo "<h2>System Limits</h2>";
echo "Memory Limit: " . ini_get('memory_limit') . "<br>";
echo "Max Execution Time: " . ini_get('max_execution_time') . "s<br>";
echo "Upload Max: " . ini_get('upload_max_filesize') . "<br>";
echo "Post Max: " . ini_get('post_max_size') . "<br>";

$disabled = ini_get('disable_functions');
if ($disabled) {
    echo "<br><strong>Disabled Functions:</strong><br>";
    echo "<span class='warning'>" . str_replace(',', ', ', $disabled) . "</span>";
}
echo "</div>";

// Quick Test
echo "<div class='section'>";
echo "<h2>Laravel Quick Test</h2>";
try {
    if (file_exists('../vendor/autoload.php')) {
        require_once '../vendor/autoload.php';
        echo "<span class='ok'>✅ Autoloader works</span><br>";
        
        if (file_exists('../bootstrap/app.php')) {
            // Try to create Laravel app instance
            $app = require_once '../bootstrap/app.php';
            echo "<span class='ok'>✅ Laravel app boots</span><br>";
        }
    }
} catch (Exception $e) {
    echo "<span class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</span><br>";
}
echo "</div>";

// Recommendations
echo "<div class='section'>";
echo "<h2>🔧 Recommendations</h2>";
echo "<h3>If you see errors:</h3>";
echo "<ol>";
echo "<li><strong>highlight_file errors:</strong> Contact hosting provider or set APP_DEBUG=false</li>";
echo "<li><strong>Permission errors:</strong> Run: chmod -R 775 storage bootstrap/cache</li>";
echo "<li><strong>Sanctum errors:</strong> Remove Laravel\\Sanctum\\SanctumServiceProvider from config</li>";
echo "<li><strong>Memory errors:</strong> Increase memory_limit in .htaccess or contact host</li>";
echo "</ol>";

echo "<h3>Quick fixes to try:</h3>";
echo "<ul>";
echo "<li>Run: <code>php artisan config:clear</code></li>";
echo "<li>Run: <code>php artisan cache:clear</code></li>";
echo "<li>Run: <code>composer dump-autoload</code></li>";
echo "<li>Set APP_DEBUG=false in .env</li>";
echo "</ul>";
echo "</div>";

echo "<p><small>Check completed at: " . date('Y-m-d H:i:s') . "</small></p>";
echo "</body></html>";
?>
