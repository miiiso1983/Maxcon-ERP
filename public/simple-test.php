<?php
/**
 * Simple PHP Test - No Laravel Dependencies
 */

echo "<!DOCTYPE html><html><head><title>Simple Test</title>";
echo "<style>body{font-family:Arial;margin:20px;} .ok{color:green;} .error{color:red;}</style>";
echo "</head><body>";

echo "<h1>🧪 Simple PHP Test</h1>";

// Basic PHP test
echo "<h2>PHP Status</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Server Time: " . date('Y-m-d H:i:s') . "<br>";
echo "Memory Limit: " . ini_get('memory_limit') . "<br>";

// File system test
echo "<h2>File System Test</h2>";
if (is_writable('.')) {
    echo "<span class='ok'>✅ Current directory is writable</span><br>";
} else {
    echo "<span class='error'>❌ Current directory is not writable</span><br>";
}

// Create test file
$testFile = 'test_write.txt';
if (file_put_contents($testFile, 'Test content')) {
    echo "<span class='ok'>✅ Can create files</span><br>";
    unlink($testFile);
} else {
    echo "<span class='error'>❌ Cannot create files</span><br>";
}

// Database test (if credentials available)
echo "<h2>Database Test</h2>";
if (file_exists('../.env')) {
    $env = file_get_contents('../.env');
    preg_match('/DB_HOST=(.*)/', $env, $host);
    preg_match('/DB_DATABASE=(.*)/', $env, $database);
    preg_match('/DB_USERNAME=(.*)/', $env, $username);
    preg_match('/DB_PASSWORD=(.*)/', $env, $password);
    
    if (isset($host[1]) && isset($database[1]) && isset($username[1])) {
        $host = trim($host[1]);
        $database = trim($database[1]);
        $username = trim($username[1]);
        $password = isset($password[1]) ? trim($password[1]) : '';
        
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
            echo "<span class='ok'>✅ Database connection successful</span><br>";
        } catch (PDOException $e) {
            echo "<span class='error'>❌ Database connection failed: " . $e->getMessage() . "</span><br>";
        }
    } else {
        echo "<span class='error'>❌ Database credentials not found in .env</span><br>";
    }
} else {
    echo "<span class='error'>❌ .env file not found</span><br>";
}

echo "<h2>Next Steps</h2>";
echo "<ul>";
echo "<li><a href='debug.php'>Run Full Debug</a></li>";
echo "<li><a href='fix-now.php'>Try Auto Fix</a></li>";
echo "<li><a href='../'>Back to Main Site</a></li>";
echo "</ul>";

echo "</body></html>";
?>
