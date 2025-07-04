<?php
/**
 * SIMPLE LOGIN TEST
 * Basic login test without Laravel to isolate the issue
 * URL: https://your-domain.com/simple-login-test.php
 */

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><title>Simple Login Test</title>";
echo "<style>body{font-family:Arial;margin:20px;} .form{background:#f8f9fa;padding:20px;border-radius:8px;max-width:400px;margin:20px auto;} .ok{color:green;} .error{color:red;} input{width:100%;padding:10px;margin:5px 0;border:1px solid #ddd;border-radius:4px;} button{width:100%;padding:12px;background:#007bff;color:white;border:none;border-radius:4px;cursor:pointer;}</style>";
echo "</head><body>";

echo "<h1>🧪 Simple Login Test</h1>";
echo "<p>This test bypasses Laravel entirely to check basic PHP functionality.</p>";

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<div style='background:#d4edda;padding:15px;border-radius:5px;margin:20px 0;'>";
    echo "<h3>✅ POST Request Received Successfully!</h3>";
    echo "<p><strong>This means your server can handle POST requests.</strong></p>";
    
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    echo "<p>Email received: " . htmlspecialchars($email) . "</p>";
    echo "<p>Password received: " . (empty($password) ? 'Empty' : 'Provided') . "</p>";
    
    // Simple validation
    if ($email === 'admin@maxcon-demo.com' && $password === 'password') {
        $_SESSION['user_logged_in'] = true;
        $_SESSION['user_email'] = $email;
        
        echo "<div style='background:#d1ecf1;padding:15px;border-radius:5px;margin:10px 0;'>";
        echo "<h4>🎉 Login Successful!</h4>";
        echo "<p>Session created successfully. You would normally be redirected to dashboard now.</p>";
        echo "<p><a href='simple-dashboard-test.php' style='color:#007bff;'>Test Dashboard →</a></p>";
        echo "</div>";
    } else {
        echo "<div style='background:#f8d7da;padding:15px;border-radius:5px;margin:10px 0;'>";
        echo "<h4>❌ Invalid Credentials</h4>";
        echo "<p>Use: admin@maxcon-demo.com / password</p>";
        echo "</div>";
    }
    echo "</div>";
}

// Check if already logged in
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']) {
    echo "<div style='background:#d1ecf1;padding:15px;border-radius:5px;margin:20px 0;'>";
    echo "<h3>👤 Already Logged In</h3>";
    echo "<p>Email: " . htmlspecialchars($_SESSION['user_email']) . "</p>";
    echo "<p><a href='simple-dashboard-test.php' style='color:#007bff;'>Go to Dashboard →</a></p>";
    echo "<p><a href='?logout=1' style='color:#dc3545;'>Logout</a></p>";
    echo "</div>";
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    echo "<div style='background:#fff3cd;padding:15px;border-radius:5px;margin:20px 0;'>";
    echo "<h3>👋 Logged Out</h3>";
    echo "<p>Session destroyed successfully.</p>";
    echo "</div>";
}

// Show login form
echo "<div class='form'>";
echo "<h3>🔐 Simple Login Form</h3>";
echo "<form method='POST' action='" . $_SERVER['PHP_SELF'] . "'>";
echo "<label>Email:</label>";
echo "<input type='email' name='email' value='admin@maxcon-demo.com' required>";
echo "<label>Password:</label>";
echo "<input type='password' name='password' value='password' required>";
echo "<button type='submit'>Test Login</button>";
echo "</form>";
echo "</div>";

echo "<div style='background:#e2e3e5;padding:15px;border-radius:5px;margin:20px 0;'>";
echo "<h3>📋 What This Test Shows:</h3>";
echo "<ul>";
echo "<li><strong>If this works:</strong> Your server can handle POST requests and PHP sessions</li>";
echo "<li><strong>If this fails:</strong> There's a server-level issue with POST processing</li>";
echo "<li><strong>Next step:</strong> Compare this with your Laravel login to identify the problem</li>";
echo "</ul>";
echo "</div>";

echo "<div style='background:#f8f9fa;padding:15px;border-radius:5px;margin:20px 0;'>";
echo "<h3>🔧 Server Information:</h3>";
echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>";
echo "<p><strong>Server Software:</strong> " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "</p>";
echo "<p><strong>Request Method:</strong> " . $_SERVER['REQUEST_METHOD'] . "</p>";
echo "<p><strong>Session Status:</strong> " . (session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Inactive') . "</p>";
echo "</div>";

echo "</body></html>";
?>
