<?php
/**
 * Laravel Provider Repository Fix
 * Run this script to fix provider loading issues
 */

echo "🔧 Fixing Laravel Provider Repository Issues...\n\n";

// Clear all caches
echo "1. Clearing all caches...\n";
exec('php artisan cache:clear 2>&1', $output1);
echo implode("\n", $output1) . "\n\n";

echo "2. Clearing config cache...\n";
exec('php artisan config:clear 2>&1', $output2);
echo implode("\n", $output2) . "\n\n";

echo "3. Clearing route cache...\n";
exec('php artisan route:clear 2>&1', $output3);
echo implode("\n", $output3) . "\n\n";

echo "4. Clearing view cache...\n";
exec('php artisan view:clear 2>&1', $output4);
echo implode("\n", $output4) . "\n\n";

// Remove bootstrap cache files
echo "5. Removing bootstrap cache files...\n";
$cacheFiles = [
    'bootstrap/cache/config.php',
    'bootstrap/cache/routes.php',
    'bootstrap/cache/services.php',
    'bootstrap/cache/packages.php'
];

foreach ($cacheFiles as $file) {
    if (file_exists($file)) {
        unlink($file);
        echo "   Removed: $file\n";
    }
}

// Regenerate autoloader
echo "\n6. Regenerating autoloader...\n";
exec('composer dump-autoload 2>&1', $output5);
echo implode("\n", $output5) . "\n\n";

// Check for problematic providers
echo "7. Checking for problematic providers...\n";
$configApp = file_get_contents('config/app.php');

// Common problematic providers
$problematicProviders = [
    'Laravel\Sanctum\SanctumServiceProvider',
    'Spatie\Permission\PermissionServiceProvider',
    'Stancl\Tenancy\TenancyServiceProvider'
];

foreach ($problematicProviders as $provider) {
    if (strpos($configApp, $provider) !== false) {
        echo "   Found: $provider\n";
    }
}

echo "\n8. Creating optimized config cache...\n";
exec('php artisan config:cache 2>&1', $output6);
echo implode("\n", $output6) . "\n\n";

echo "✅ Provider Repository fix completed!\n";
echo "\nIf issues persist, check:\n";
echo "- Composer dependencies are properly installed\n";
echo "- All service providers exist and are properly configured\n";
echo "- PHP version compatibility\n";
?>
