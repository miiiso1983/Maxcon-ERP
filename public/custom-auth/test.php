<?php
require_once 'Database.php';
require_once 'User.php';
require_once 'Session.php';

// Test database connection
echo "<h2>🔍 Custom Auth System Test</h2>";

// Debug path information
echo "<h3>📁 Path Debug Info:</h3>";
echo "<p><strong>Current directory:</strong> " . __DIR__ . "</p>";
echo "<p><strong>Parent directory:</strong> " . dirname(__DIR__) . "</p>";
echo "<p><strong>Laravel root:</strong> " . dirname(dirname(__DIR__)) . "</p>";
echo "<p><strong>Expected .env path:</strong> " . dirname(dirname(__DIR__)) . '/.env' . "</p>";
echo "<p><strong>.env file exists:</strong> " . (file_exists(dirname(dirname(__DIR__)) . '/.env') ? '✅ Yes' : '❌ No') . "</p>";

try {
    $db = Database::getInstance();
    echo "<p>✅ Database connection successful</p>";
    
    // Test user query
    $users = $db->fetchAll("SELECT id, name, email, status FROM users LIMIT 5");
    echo "<p>✅ Found " . count($users) . " users in database</p>";
    
    if (!empty($users)) {
        echo "<h3>Sample Users:</h3>";
        echo "<ul>";
        foreach ($users as $user) {
            echo "<li>ID: {$user['id']}, Name: {$user['name']}, Email: {$user['email']}, Status: {$user['status']}</li>";
        }
        echo "</ul>";
    }
    
    // Test session
    Session::init();
    echo "<p>✅ Session system working</p>";
    
    // Test CSRF token
    $token = Session::setCsrfToken();
    echo "<p>✅ CSRF token generated: " . substr($token, 0, 10) . "...</p>";
    
    echo "<h3>🎯 Ready to Test!</h3>";
    echo "<p><a href='login.php' style='background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Login Page</a></p>";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Please check your .env file and database configuration.</p>";
}
?>
