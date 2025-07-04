<?php
require_once 'Database.php';

echo "<h2>🔍 User Data Diagnostic</h2>";

try {
    $db = Database::getInstance();
    echo "<p>✅ Database connection successful</p>";
    
    // Check if users table exists
    echo "<h3>📋 Checking Users Table:</h3>";
    
    try {
        $users = $db->fetchAll("SELECT id, name, email, status, is_super_admin, created_at FROM users");
        echo "<p>✅ Users table exists with <strong>" . count($users) . "</strong> users</p>";
        
        if (count($users) > 0) {
            echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
            echo "<tr style='background: #f8f9fa;'>";
            echo "<th style='padding: 8px;'>ID</th>";
            echo "<th style='padding: 8px;'>Name</th>";
            echo "<th style='padding: 8px;'>Email</th>";
            echo "<th style='padding: 8px;'>Status</th>";
            echo "<th style='padding: 8px;'>Super Admin</th>";
            echo "<th style='padding: 8px;'>Created</th>";
            echo "</tr>";
            
            foreach ($users as $user) {
                echo "<tr>";
                echo "<td style='padding: 8px;'>{$user['id']}</td>";
                echo "<td style='padding: 8px;'>{$user['name']}</td>";
                echo "<td style='padding: 8px;'>{$user['email']}</td>";
                echo "<td style='padding: 8px;'>{$user['status']}</td>";
                echo "<td style='padding: 8px;'>" . ($user['is_super_admin'] ? 'Yes' : 'No') . "</td>";
                echo "<td style='padding: 8px;'>{$user['created_at']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } catch (Exception $e) {
        echo "<p>❌ Error checking users: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
    // Check specific demo user
    echo "<h3>🎯 Checking Demo User:</h3>";
    
    try {
        $demoUser = $db->fetch("SELECT * FROM users WHERE email = 'admin@maxcon-demo.com'");
        
        if ($demoUser) {
            echo "<p>✅ Demo user found!</p>";
            echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 10px 0;'>";
            echo "<strong>User Details:</strong><br>";
            echo "ID: {$demoUser['id']}<br>";
            echo "Name: {$demoUser['name']}<br>";
            echo "Email: {$demoUser['email']}<br>";
            echo "Status: {$demoUser['status']}<br>";
            echo "Super Admin: " . ($demoUser['is_super_admin'] ? 'Yes' : 'No') . "<br>";
            echo "Password Hash: " . substr($demoUser['password'], 0, 20) . "...<br>";
            echo "Created: {$demoUser['created_at']}<br>";
            echo "</div>";
            
            // Test password verification
            echo "<h3>🔐 Testing Password:</h3>";
            $testPassword = 'password';
            $storedHash = $demoUser['password'];
            
            echo "<p><strong>Testing password:</strong> '$testPassword'</p>";
            echo "<p><strong>Stored hash:</strong> " . substr($storedHash, 0, 30) . "...</p>";
            
            if (password_verify($testPassword, $storedHash)) {
                echo "<p>✅ Password verification: <strong>SUCCESS</strong></p>";
            } else {
                echo "<p>❌ Password verification: <strong>FAILED</strong></p>";
                echo "<p>🔧 Let's create a new password hash...</p>";
                
                // Create new password hash
                $newHash = password_hash($testPassword, PASSWORD_DEFAULT);
                echo "<p><strong>New hash:</strong> " . substr($newHash, 0, 30) . "...</p>";
                
                // Update user with new hash
                try {
                    $db->query("UPDATE users SET password = ? WHERE email = 'admin@maxcon-demo.com'", [$newHash]);
                    echo "<p>✅ Password updated successfully!</p>";
                    
                    // Test new hash
                    if (password_verify($testPassword, $newHash)) {
                        echo "<p>✅ New password verification: <strong>SUCCESS</strong></p>";
                    } else {
                        echo "<p>❌ New password verification: <strong>STILL FAILED</strong></p>";
                    }
                } catch (Exception $e) {
                    echo "<p>❌ Error updating password: " . htmlspecialchars($e->getMessage()) . "</p>";
                }
            }
            
        } else {
            echo "<p>❌ Demo user not found!</p>";
            echo "<p>🔧 Let's create the demo user...</p>";
            
            // Create demo user
            $newHash = password_hash('password', PASSWORD_DEFAULT);
            try {
                $db->query("INSERT INTO users (name, email, password, is_super_admin, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())", 
                    ['Admin User', 'admin@maxcon-demo.com', $newHash, 1, 'active']);
                echo "<p>✅ Demo user created successfully!</p>";
            } catch (Exception $e) {
                echo "<p>❌ Error creating demo user: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        }
    } catch (Exception $e) {
        echo "<p>❌ Error checking demo user: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
    echo "<h3>🎯 Next Steps:</h3>";
    echo "<p><a href='login.php' style='background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>Try Login Again</a>";
    echo "<a href='test.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Test System</a></p>";
    
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 8px; margin-top: 20px; border: 1px solid #ffeaa7;'>";
    echo "<h4>🔑 Demo Credentials:</h4>";
    echo "<p><strong>Email:</strong> admin@maxcon-demo.com</p>";
    echo "<p><strong>Password:</strong> password</p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
