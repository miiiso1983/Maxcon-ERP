<?php
require_once 'Database.php';

echo "<h2>🔧 Direct Database Table Creation</h2>";

try {
    $db = Database::getInstance();
    echo "<p>✅ Database connection successful</p>";
    
    // Create users table
    echo "<h3>Creating users table...</h3>";
    $usersSQL = "CREATE TABLE IF NOT EXISTS `users` (
        `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        `name` varchar(255) NOT NULL,
        `email` varchar(255) NOT NULL,
        `email_verified_at` timestamp NULL DEFAULT NULL,
        `password` varchar(255) NOT NULL,
        `phone` varchar(255) DEFAULT NULL,
        `address` text DEFAULT NULL,
        `department` varchar(255) DEFAULT NULL,
        `position` varchar(255) DEFAULT NULL,
        `is_super_admin` tinyint(1) NOT NULL DEFAULT 0,
        `tenant_id` varchar(255) DEFAULT NULL,
        `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
        `remember_token` varchar(100) DEFAULT NULL,
        `created_at` timestamp NULL DEFAULT NULL,
        `updated_at` timestamp NULL DEFAULT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `users_email_unique` (`email`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    try {
        $db->query($usersSQL);
        echo "<p>✅ Users table created successfully</p>";
    } catch (Exception $e) {
        echo "<p>❌ Error creating users table: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
    // Create tenants table
    echo "<h3>Creating tenants table...</h3>";
    $tenantsSQL = "CREATE TABLE IF NOT EXISTS `tenants` (
        `id` varchar(255) NOT NULL,
        `name` varchar(255) DEFAULT NULL,
        `email` varchar(255) DEFAULT NULL,
        `phone` varchar(255) DEFAULT NULL,
        `address` text DEFAULT NULL,
        `license_key` varchar(255) DEFAULT NULL,
        `license_type` enum('basic','standard','premium','enterprise') NOT NULL DEFAULT 'basic',
        `license_expires_at` timestamp NULL DEFAULT NULL,
        `features` json DEFAULT NULL,
        `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
        `max_users` int(11) NOT NULL DEFAULT 10,
        `current_users` int(11) NOT NULL DEFAULT 0,
        `max_warehouses` int(11) NOT NULL DEFAULT 3,
        `current_warehouses` int(11) NOT NULL DEFAULT 0,
        `max_storage` int(11) NOT NULL DEFAULT 1000,
        `current_storage` int(11) NOT NULL DEFAULT 0,
        `enabled_modules` json DEFAULT NULL,
        `api_calls_limit` int(11) NOT NULL DEFAULT 1000,
        `api_calls_used` int(11) NOT NULL DEFAULT 0,
        `api_calls_reset_at` timestamp NULL DEFAULT NULL,
        `max_products` int(11) NOT NULL DEFAULT 1000,
        `current_products` int(11) NOT NULL DEFAULT 0,
        `max_customers` int(11) NOT NULL DEFAULT 500,
        `current_customers` int(11) NOT NULL DEFAULT 0,
        `admin_user_id` bigint(20) unsigned DEFAULT NULL,
        `admin_name` varchar(255) DEFAULT NULL,
        `admin_email` varchar(255) DEFAULT NULL,
        `last_login_at` timestamp NULL DEFAULT NULL,
        `monthly_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
        `next_billing_date` timestamp NULL DEFAULT NULL,
        `billing_status` enum('active','overdue','suspended') NOT NULL DEFAULT 'active',
        `created_at` timestamp NULL DEFAULT NULL,
        `updated_at` timestamp NULL DEFAULT NULL,
        `data` json DEFAULT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `tenants_license_key_unique` (`license_key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    try {
        $db->query($tenantsSQL);
        echo "<p>✅ Tenants table created successfully</p>";
    } catch (Exception $e) {
        echo "<p>❌ Error creating tenants table: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
    // Create sessions table
    echo "<h3>Creating sessions table...</h3>";
    $sessionsSQL = "CREATE TABLE IF NOT EXISTS `sessions` (
        `id` varchar(255) NOT NULL,
        `user_id` bigint(20) unsigned DEFAULT NULL,
        `ip_address` varchar(45) DEFAULT NULL,
        `user_agent` text DEFAULT NULL,
        `payload` longtext NOT NULL,
        `last_activity` int(11) NOT NULL,
        PRIMARY KEY (`id`),
        KEY `sessions_user_id_index` (`user_id`),
        KEY `sessions_last_activity_index` (`last_activity`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    try {
        $db->query($sessionsSQL);
        echo "<p>✅ Sessions table created successfully</p>";
    } catch (Exception $e) {
        echo "<p>❌ Error creating sessions table: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
    // Insert demo user
    echo "<h3>Inserting demo user...</h3>";
    $insertUserSQL = "INSERT IGNORE INTO `users` (`name`, `email`, `password`, `is_super_admin`, `status`, `created_at`, `updated_at`) VALUES
        ('Admin User', 'admin@maxcon-demo.com', '$2y$12\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'active', NOW(), NOW())";
    
    try {
        $db->query($insertUserSQL);
        echo "<p>✅ Demo user inserted successfully</p>";
    } catch (Exception $e) {
        echo "<p>❌ Error inserting demo user: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
    // Insert demo tenant
    echo "<h3>Inserting demo tenant...</h3>";
    $insertTenantSQL = "INSERT IGNORE INTO `tenants` (`id`, `name`, `email`, `status`, `license_type`, `created_at`, `updated_at`) VALUES
        ('demo-tenant', 'Demo Company', 'demo@maxcon-demo.com', 'active', 'premium', NOW(), NOW())";
    
    try {
        $db->query($insertTenantSQL);
        echo "<p>✅ Demo tenant inserted successfully</p>";
    } catch (Exception $e) {
        echo "<p>❌ Error inserting demo tenant: " . htmlspecialchars($e->getMessage()) . "</p>";
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
