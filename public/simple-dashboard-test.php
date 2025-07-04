<?php
/**
 * SIMPLE DASHBOARD TEST
 * Test dashboard page to verify login flow
 * URL: https://your-domain.com/simple-dashboard-test.php
 */

session_start();

echo "<!DOCTYPE html><html><head><title>Simple Dashboard Test</title>";
echo "<style>body{font-family:Arial;margin:20px;} .dashboard{background:#f8f9fa;padding:20px;border-radius:8px;max-width:600px;margin:20px auto;} .ok{color:green;} .error{color:red;}</style>";
echo "</head><body>";

echo "<h1>📊 Simple Dashboard Test</h1>";

// Check if logged in
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']) {
    echo "<div class='dashboard'>";
    echo "<h2>🎉 Welcome to Dashboard!</h2>";
    echo "<p class='ok'><strong>Login flow working correctly!</strong></p>";
    echo "<p>Logged in as: " . htmlspecialchars($_SESSION['user_email']) . "</p>";
    echo "<p>Session ID: " . session_id() . "</p>";
    echo "<p>Login time: " . date('Y-m-d H:i:s') . "</p>";
    
    echo "<h3>✅ This Proves:</h3>";
    echo "<ul>";
    echo "<li>POST requests work on your server</li>";
    echo "<li>PHP sessions work correctly</li>";
    echo "<li>Login → Dashboard redirect flow is possible</li>";
    echo "<li>The issue is specifically with Laravel, not the server</li>";
    echo "</ul>";
    
    echo "<h3>🔧 Next Steps for Laravel:</h3>";
    echo "<ul>";
    echo "<li>Check Laravel error logs in storage/logs/</li>";
    echo "<li>Verify Laravel routes are properly configured</li>";
    echo "<li>Check if Laravel middleware is causing issues</li>";
    echo "<li>Test Laravel authentication controllers</li>";
    echo "</ul>";
    
    echo "<p><a href='simple-login-test.php?logout=1' style='color:#dc3545;'>← Back to Login Test</a></p>";
    echo "</div>";
} else {
    echo "<div style='background:#f8d7da;padding:15px;border-radius:5px;margin:20px 0;'>";
    echo "<h3>❌ Not Logged In</h3>";
    echo "<p>You need to login first using the simple login test.</p>";
    echo "<p><a href='simple-login-test.php' style='color:#007bff;'>← Go to Login Test</a></p>";
    echo "</div>";
}

echo "<div style='background:#e2e3e5;padding:15px;border-radius:5px;margin:20px 0;'>";
echo "<h3>🔍 Diagnostic Information:</h3>";
echo "<p><strong>Session Status:</strong> " . (session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Inactive') . "</p>";
echo "<p><strong>Session Variables:</strong></p>";
echo "<pre style='background:#f8f9fa;padding:10px;border-radius:4px;'>";
print_r($_SESSION);
echo "</pre>";
echo "</div>";

echo "</body></html>";
?>
