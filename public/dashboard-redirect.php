<?php
/**
 * Dashboard Redirect - Bypass Laravel Issues
 * This file redirects users to the working custom authentication dashboard
 * when Laravel's dashboard fails with "Service Unavailable"
 */

// Check if custom auth system is available
$customAuthPath = __DIR__ . '/custom-auth/laravel-bridge.php';

if (file_exists($customAuthPath)) {
    // Redirect to working custom auth bridge
    header('Location: /custom-auth/laravel-bridge.php');
    exit;
} else {
    // Fallback to custom dashboard
    header('Location: /custom-auth/dashboard.php');
    exit;
}
?>
