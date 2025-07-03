<?php
// PHP Environment Check for Laravel
echo "<h2>PHP Environment Check</h2>";

echo "<h3>PHP Version</h3>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Required: >= 8.1<br><br>";

echo "<h3>Required Extensions</h3>";
$required_extensions = [
    'bcmath',
    'ctype', 
    'curl',
    'dom',
    'fileinfo',
    'json',
    'mbstring',
    'openssl',
    'pcre',
    'pdo',
    'tokenizer',
    'xml',
    'zip'
];

foreach ($required_extensions as $ext) {
    $status = extension_loaded($ext) ? '✅ Loaded' : '❌ Missing';
    echo "{$ext}: {$status}<br>";
}

echo "<h3>Disabled Functions Check</h3>";
$disabled = ini_get('disable_functions');
if (empty($disabled)) {
    echo "✅ No functions disabled<br>";
} else {
    echo "❌ Disabled functions: " . $disabled . "<br>";
}

echo "<h3>Critical Functions</h3>";
$critical_functions = [
    'highlight_file',
    'file_get_contents',
    'file_put_contents',
    'exec',
    'shell_exec',
    'proc_open'
];

foreach ($critical_functions as $func) {
    $status = function_exists($func) ? '✅ Available' : '❌ Disabled';
    echo "{$func}: {$status}<br>";
}

echo "<h3>Memory & Limits</h3>";
echo "Memory Limit: " . ini_get('memory_limit') . "<br>";
echo "Max Execution Time: " . ini_get('max_execution_time') . "<br>";
echo "Upload Max Filesize: " . ini_get('upload_max_filesize') . "<br>";
echo "Post Max Size: " . ini_get('post_max_size') . "<br>";

echo "<h3>Laravel Requirements</h3>";
echo "OpenSSL: " . (extension_loaded('openssl') ? '✅' : '❌') . "<br>";
echo "PDO: " . (extension_loaded('pdo') ? '✅' : '❌') . "<br>";
echo "Mbstring: " . (extension_loaded('mbstring') ? '✅' : '❌') . "<br>";
echo "Tokenizer: " . (extension_loaded('tokenizer') ? '✅' : '❌') . "<br>";
echo "XML: " . (extension_loaded('xml') ? '✅' : '❌') . "<br>";
echo "Ctype: " . (extension_loaded('ctype') ? '✅' : '❌') . "<br>";
echo "JSON: " . (extension_loaded('json') ? '✅' : '❌') . "<br>";
echo "BCMath: " . (extension_loaded('bcmath') ? '✅' : '❌') . "<br>";
?>
