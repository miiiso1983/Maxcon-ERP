<?php
/**
 * Laravel Deployment Diagnostic Tool
 * Place this file in your public directory and access via browser
 */

echo "<h1>🔍 Laravel Deployment Diagnostics</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; }
    .error { color: red; }
    .warning { color: orange; }
    .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
    .code { background: #f5f5f5; padding: 10px; border-radius: 3px; font-family: monospace; }
</style>";

// Check PHP Version
echo "<div class='section'>";
echo "<h2>📋 PHP Environment</h2>";
echo "<strong>PHP Version:</strong> " . phpversion();
if (version_compare(phpversion(), '8.1.0', '>=')) {
    echo " <span class='success'>✅ Compatible</span>";
} else {
    echo " <span class='error'>❌ Requires PHP 8.1+</span>";
}
echo "<br>";

// Check critical functions
$critical_functions = ['highlight_file', 'file_get_contents', 'exec', 'shell_exec'];
echo "<strong>Critical Functions:</strong><br>";
foreach ($critical_functions as $func) {
    $status = function_exists($func) ? "<span class='success'>✅</span>" : "<span class='error'>❌</span>";
    echo "&nbsp;&nbsp;{$func}: {$status}<br>";
}

// Check disabled functions
$disabled = ini_get('disable_functions');
if (!empty($disabled)) {
    echo "<strong>Disabled Functions:</strong> <span class='warning'>{$disabled}</span><br>";
}

echo "</div>";

// Check Laravel Requirements
echo "<div class='section'>";
echo "<h2>🚀 Laravel Requirements</h2>";
$extensions = [
    'bcmath' => 'BCMath',
    'ctype' => 'Ctype', 
    'curl' => 'cURL',
    'dom' => 'DOM',
    'fileinfo' => 'Fileinfo',
    'json' => 'JSON',
    'mbstring' => 'Mbstring',
    'openssl' => 'OpenSSL',
    'pcre' => 'PCRE',
    'pdo' => 'PDO',
    'tokenizer' => 'Tokenizer',
    'xml' => 'XML'
];

foreach ($extensions as $ext => $name) {
    $status = extension_loaded($ext) ? "<span class='success'>✅</span>" : "<span class='error'>❌</span>";
    echo "{$name}: {$status}<br>";
}
echo "</div>";

// Check File Permissions
echo "<div class='section'>";
echo "<h2>📁 File Permissions</h2>";
$paths = [
    '../storage' => 'Storage Directory',
    '../bootstrap/cache' => 'Bootstrap Cache',
    '../.env' => 'Environment File'
];

foreach ($paths as $path => $name) {
    if (file_exists($path)) {
        $perms = substr(sprintf('%o', fileperms($path)), -4);
        $writable = is_writable($path) ? "<span class='success'>✅ Writable</span>" : "<span class='error'>❌ Not Writable</span>";
        echo "{$name}: {$perms} {$writable}<br>";
    } else {
        echo "{$name}: <span class='error'>❌ Not Found</span><br>";
    }
}
echo "</div>";

// Check Environment
echo "<div class='section'>";
echo "<h2>🌍 Environment Check</h2>";
if (file_exists('../.env')) {
    echo ".env file: <span class='success'>✅ Found</span><br>";
    
    // Check for critical env variables
    $env_content = file_get_contents('../.env');
    $critical_vars = ['APP_KEY', 'DB_DATABASE', 'DB_USERNAME'];
    
    foreach ($critical_vars as $var) {
        if (strpos($env_content, $var . '=') !== false) {
            echo "{$var}: <span class='success'>✅ Set</span><br>";
        } else {
            echo "{$var}: <span class='error'>❌ Missing</span><br>";
        }
    }
} else {
    echo ".env file: <span class='error'>❌ Not Found</span><br>";
}
echo "</div>";

// Check Laravel Installation
echo "<div class='section'>";
echo "<h2>🎯 Laravel Installation</h2>";
if (file_exists('../vendor/autoload.php')) {
    echo "Composer Dependencies: <span class='success'>✅ Installed</span><br>";
} else {
    echo "Composer Dependencies: <span class='error'>❌ Missing</span><br>";
    echo "<div class='code'>Run: composer install</div>";
}

if (file_exists('../bootstrap/cache/config.php')) {
    echo "Config Cache: <span class='success'>✅ Cached</span><br>";
} else {
    echo "Config Cache: <span class='warning'>⚠️ Not Cached</span><br>";
}
echo "</div>";

// Memory and Limits
echo "<div class='section'>";
echo "<h2>💾 System Limits</h2>";
echo "Memory Limit: " . ini_get('memory_limit') . "<br>";
echo "Max Execution Time: " . ini_get('max_execution_time') . "s<br>";
echo "Upload Max Filesize: " . ini_get('upload_max_filesize') . "<br>";
echo "Post Max Size: " . ini_get('post_max_size') . "<br>";
echo "</div>";

// Quick Fixes
echo "<div class='section'>";
echo "<h2>🔧 Quick Fixes</h2>";
echo "<h3>If you see 'highlight_file' errors:</h3>";
echo "<div class='code'>";
echo "1. Contact your hosting provider to enable highlight_file() function<br>";
echo "2. Or add this to your .htaccess:<br>";
echo "&nbsp;&nbsp;php_flag display_errors Off<br>";
echo "&nbsp;&nbsp;php_flag log_errors On<br>";
echo "3. Set APP_DEBUG=false in .env file<br>";
echo "</div>";

echo "<h3>If you see permission errors:</h3>";
echo "<div class='code'>";
echo "chmod -R 775 storage<br>";
echo "chmod -R 775 bootstrap/cache<br>";
echo "</div>";

echo "<h3>If you see 500 errors:</h3>";
echo "<div class='code'>";
echo "php artisan config:clear<br>";
echo "php artisan cache:clear<br>";
echo "php artisan route:clear<br>";
echo "</div>";
echo "</div>";

echo "<div class='section'>";
echo "<h2>📞 Support Information</h2>";
echo "If you continue to experience issues:<br>";
echo "1. Check your hosting provider's error logs<br>";
echo "2. Ensure all PHP extensions are installed<br>";
echo "3. Verify file permissions are correct<br>";
echo "4. Contact your hosting provider for PHP function restrictions<br>";
echo "</div>";

echo "<p><small>Diagnostic completed at: " . date('Y-m-d H:i:s') . "</small></p>";
?>
