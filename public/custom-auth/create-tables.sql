-- Create essential tables for custom auth system
-- Run this SQL script if Laravel migrations don't work

-- Create users table
CREATE TABLE IF NOT EXISTS `users` (
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
    UNIQUE KEY `users_email_unique` (`email`),
    KEY `users_tenant_id_foreign` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create tenants table
CREATE TABLE IF NOT EXISTS `tenants` (
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
    UNIQUE KEY `tenants_license_key_unique` (`license_key`),
    KEY `tenants_admin_user_id_foreign` (`admin_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create sessions table
CREATE TABLE IF NOT EXISTS `sessions` (
    `id` varchar(255) NOT NULL,
    `user_id` bigint(20) unsigned DEFAULT NULL,
    `ip_address` varchar(45) DEFAULT NULL,
    `user_agent` text DEFAULT NULL,
    `payload` longtext NOT NULL,
    `last_activity` int(11) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `sessions_user_id_index` (`user_id`),
    KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create password_reset_tokens table
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
    `email` varchar(255) NOT NULL,
    `token` varchar(255) NOT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add foreign key constraints
ALTER TABLE `users` ADD CONSTRAINT `users_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;
ALTER TABLE `tenants` ADD CONSTRAINT `tenants_admin_user_id_foreign` FOREIGN KEY (`admin_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

-- Insert demo user
INSERT IGNORE INTO `users` (`id`, `name`, `email`, `password`, `is_super_admin`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Admin User', 'admin@maxcon-demo.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'active', NOW(), NOW());

-- Insert demo tenant
INSERT IGNORE INTO `tenants` (`id`, `name`, `email`, `status`, `license_type`, `created_at`, `updated_at`) VALUES
('demo-tenant', 'Demo Company', 'demo@maxcon-demo.com', 'active', 'premium', NOW(), NOW());

-- Update user with tenant
UPDATE `users` SET `tenant_id` = 'demo-tenant' WHERE `email` = 'admin@maxcon-demo.com';
