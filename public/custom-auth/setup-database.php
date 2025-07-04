<?php
require_once 'Database.php';

echo "<h2>🔧 Database Setup Tool</h2>";

try {
    $db = Database::getInstance();
    echo "<p>✅ Database connection successful</p>";
    
    // Read SQL file
    $sqlFile = __DIR__ . '/create-tables.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception('SQL file not found: ' . $sqlFile);
    }
    
    $sql = file_get_contents($sqlFile);
    echo "<p>✅ SQL file loaded</p>";
    
    // Split SQL into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));

    echo "<h3>📋 Executing SQL Statements:</h3>";
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 10px 0; font-family: monospace; font-size: 12px;'>";

    $successCount = 0;
    $errorCount = 0;

    foreach ($statements as $index => $statement) {
        if (empty($statement) || strpos($statement, '--') === 0) {
            continue; // Skip empty statements and comments
        }

        echo "<div style='margin: 10px 0; padding: 10px; border-left: 3px solid #007bff; background: white;'>";
        echo "<strong>Statement " . ($index + 1) . ":</strong><br>";
        echo "<code>" . htmlspecialchars(substr($statement, 0, 100)) . (strlen($statement) > 100 ? '...' : '') . "</code><br>";

        try {
            $result = $db->query($statement);

            // Extract table name for display
            if (preg_match('/CREATE TABLE.*?`(\w+)`/i', $statement, $matches)) {
                echo "<span style='color: green;'>✅ Created table: <strong>{$matches[1]}</strong></span>";
            } elseif (preg_match('/INSERT.*?INTO.*?`(\w+)`/i', $statement, $matches)) {
                echo "<span style='color: green;'>✅ Inserted data into: <strong>{$matches[1]}</strong></span>";
            } elseif (preg_match('/ALTER TABLE.*?`(\w+)`/i', $statement, $matches)) {
                echo "<span style='color: green;'>✅ Added constraints to: <strong>{$matches[1]}</strong></span>";
            } elseif (preg_match('/UPDATE.*?`(\w+)`/i', $statement, $matches)) {
                echo "<span style='color: green;'>✅ Updated data in: <strong>{$matches[1]}</strong></span>";
            } else {
                echo "<span style='color: green;'>✅ Executed SQL statement</span>";
            }

            $successCount++;
        } catch (Exception $e) {
            echo "<span style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</span>";
            $errorCount++;
        }
        echo "</div>";
    }

    echo "</div>";
    
    echo "<h3>📊 Summary:</h3>";
    echo "<p>✅ <strong>$successCount</strong> statements executed successfully</p>";
    if ($errorCount > 0) {
        echo "<p>⚠️ <strong>$errorCount</strong> statements had warnings (likely tables already exist)</p>";
    }
    
    // Test the setup
    echo "<h3>🧪 Testing Setup:</h3>";
    
    // Check if users table exists and has data
    try {
        $userCount = $db->fetch("SELECT COUNT(*) as count FROM users");
        echo "<p>✅ Users table: <strong>{$userCount['count']}</strong> users found</p>";
        
        // Check for demo user
        $demoUser = $db->fetch("SELECT * FROM users WHERE email = 'admin@maxcon-demo.com'");
        if ($demoUser) {
            echo "<p>✅ Demo user exists: <strong>{$demoUser['name']}</strong> ({$demoUser['email']})</p>";
        } else {
            echo "<p>⚠️ Demo user not found</p>";
        }
    } catch (Exception $e) {
        echo "<p>❌ Error checking users table: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
    // Check tenants table
    try {
        $tenantCount = $db->fetch("SELECT COUNT(*) as count FROM tenants");
        echo "<p>✅ Tenants table: <strong>{$tenantCount['count']}</strong> tenants found</p>";
    } catch (Exception $e) {
        echo "<p>❌ Error checking tenants table: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
    echo "<h3>🎯 Next Steps:</h3>";
    echo "<p><a href='test.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>Test System</a>";
    echo "<a href='login.php' style='background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Login</a></p>";
    
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin-top: 20px;'>";
    echo "<h4>Demo Credentials:</h4>";
    echo "<p><strong>Email:</strong> admin@maxcon-demo.com</p>";
    echo "<p><strong>Password:</strong> password</p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Please check your database configuration and try again.</p>";
}
?>
