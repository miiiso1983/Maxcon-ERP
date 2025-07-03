<?php
/**
 * Emergency Laravel Fix Script
 * Run this to fix critical Laravel deployment issues
 */

echo "🚨 Emergency Laravel Fix Script\n";
echo "===============================\n\n";

// Step 1: Clear all caches
echo "1. Clearing all caches...\n";
$commands = [
    'php artisan cache:clear',
    'php artisan config:clear', 
    'php artisan route:clear',
    'php artisan view:clear',
    'php artisan event:clear'
];

foreach ($commands as $cmd) {
    echo "   Running: $cmd\n";
    exec($cmd . ' 2>&1', $output);
    if (!empty($output)) {
        echo "   Output: " . implode("\n   ", $output) . "\n";
    }
    $output = [];
}

// Step 2: Remove problematic cache files
echo "\n2. Removing problematic cache files...\n";
$cacheFiles = [
    'bootstrap/cache/config.php',
    'bootstrap/cache/routes.php', 
    'bootstrap/cache/services.php',
    'bootstrap/cache/packages.php',
    'bootstrap/cache/compiled.php'
];

foreach ($cacheFiles as $file) {
    if (file_exists($file)) {
        unlink($file);
        echo "   Removed: $file\n";
    }
}

// Step 3: Fix config/app.php - Remove Sanctum if not installed
echo "\n3. Fixing config/app.php...\n";
$configPath = 'config/app.php';
if (file_exists($configPath)) {
    $config = file_get_contents($configPath);
    
    // Remove Sanctum provider if package not installed
    if (!file_exists('vendor/laravel/sanctum')) {
        $config = str_replace("Laravel\Sanctum\SanctumServiceProvider::class,", "// Laravel\Sanctum\SanctumServiceProvider::class,", $config);
        echo "   Commented out Sanctum provider\n";
    }
    
    // Remove Spatie Permission if not installed
    if (!file_exists('vendor/spatie/laravel-permission')) {
        $config = str_replace("Spatie\Permission\PermissionServiceProvider::class,", "// Spatie\Permission\PermissionServiceProvider::class,", $config);
        echo "   Commented out Spatie Permission provider\n";
    }
    
    file_put_contents($configPath, $config);
    echo "   Updated config/app.php\n";
}

// Step 4: Fix bootstrap/providers.php
echo "\n4. Fixing bootstrap/providers.php...\n";
$providersPath = 'bootstrap/providers.php';
if (file_exists($providersPath)) {
    $providers = "<?php\n\nreturn [\n    App\Providers\AppServiceProvider::class,\n];\n";
    file_put_contents($providersPath, $providers);
    echo "   Simplified providers.php\n";
}

// Step 5: Create minimal bootstrap/app.php
echo "\n5. Creating minimal bootstrap/app.php...\n";
$appBootstrap = '<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.\'/../routes/web.php\',
        commands: __DIR__.\'/../routes/console.php\',
        health: \'/up\',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            \'language\' => \App\Http\Middleware\LanguageMiddleware::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\LanguageMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Simple error handling for production
        $exceptions->render(function (Throwable $e, $request) {
            if (app()->environment(\'production\')) {
                return response(\'<h1>Service Temporarily Unavailable</h1><p>Please try again later.</p>\', 500);
            }
        });
    })->create();
';

file_put_contents('bootstrap/app.php', $appBootstrap);
echo "   Created minimal bootstrap/app.php\n";

// Step 6: Regenerate autoloader
echo "\n6. Regenerating autoloader...\n";
exec('composer dump-autoload --optimize 2>&1', $output);
echo "   " . implode("\n   ", $output) . "\n";

// Step 7: Create basic .env if missing
echo "\n7. Checking .env file...\n";
if (!file_exists('.env')) {
    $envContent = 'APP_NAME="Maxcon ERP"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_TIMEZONE=UTC
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=

CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

LOG_CHANNEL=single
LOG_LEVEL=error
';
    file_put_contents('.env', $envContent);
    echo "   Created basic .env file\n";
}

// Step 8: Generate app key if missing
echo "\n8. Generating application key...\n";
exec('php artisan key:generate --force 2>&1', $output);
echo "   " . implode("\n   ", $output) . "\n";

echo "\n✅ Emergency fix completed!\n";
echo "\nNext steps:\n";
echo "1. Update your .env file with correct database credentials\n";
echo "2. Run: php artisan migrate\n";
echo "3. Test your application\n";
echo "4. If issues persist, check server error logs\n";
?>
