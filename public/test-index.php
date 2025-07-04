<?php
/**
 * TEST INDEX - Alternative to Laravel's index.php
 * This helps identify if the issue is with Laravel's bootstrap process
 */

echo "<!DOCTYPE html><html><head><title>Test Index</title>";
echo "<style>body{font-family:Arial;margin:20px;} .ok{color:green;} .error{color:red;}</style>";
echo "</head><body>";

echo "<h1>🧪 TEST INDEX PAGE</h1>";
echo "<p>This is a test page to verify basic server functionality.</p>";

echo "<h2>Server Information:</h2>";
echo "PHP Version: " . PHP_VERSION . "<br>";
echo "Server Time: " . date('Y-m-d H:i:s') . "<br>";
echo "Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "<br>";

echo "<h2>Available Test Pages:</h2>";
echo "<ul>";
echo "<li><a href='emergency-bypass.php'>Emergency Bypass Test</a> - Complete system diagnostic</li>";
echo "<li><a href='system-diagnostic.php'>System Diagnostic</a> - Laravel-specific tests</li>";
echo "<li><a href='simple-cache-fix.php'>Simple Cache Fix</a> - Fix cache issues</li>";
echo "<li><a href='emergency-fix-vite.php'>Vite Fix</a> - Fix asset issues</li>";
echo "</ul>";

echo "<h2>Laravel Status:</h2>";
$rootPath = dirname(__DIR__);

// Check if Laravel files exist
if (file_exists($rootPath . '/artisan')) {
    echo "<span class='ok'>✅ Laravel installation detected</span><br>";
} else {
    echo "<span class='error'>❌ Laravel installation not found</span><br>";
}

// Try to access the main Laravel index
echo "<h2>Main Application:</h2>";
echo "<p>Try accessing the main application:</p>";
echo "<a href='index.php' style='padding:10px 20px;background:#007bff;color:white;text-decoration:none;border-radius:4px;'>Access Main Application</a>";

echo "<div style='margin-top:30px;padding:15px;background:#fff3cd;border:1px solid #ffeaa7;'>";
echo "<h3>If you can see this page:</h3>";
echo "<p>Your server is working correctly. The issue is likely with Laravel's configuration or bootstrap process.</p>";
echo "<p>Use the diagnostic tools above to identify and fix the specific issue.</p>";
echo "</div>";

echo "</body></html>";
?>
