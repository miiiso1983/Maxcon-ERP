<?php
/**
 * Emergency Auto-Fix Tool
 * Attempts to fix common Laravel deployment issues
 */

echo "<!DOCTYPE html><html><head><title>Auto Fix</title>";
echo "<style>body{font-family:Arial;margin:20px;} .ok{color:green;} .error{color:red;} .warning{color:orange;} .step{margin:15px 0;padding:10px;border:1px solid #ddd;}</style>";
echo "</head><body>";

echo "<h1>🔧 Emergency Auto-Fix Tool</h1>";
echo "<p>Attempting to fix common Laravel issues...</p>";

$fixes = [];

// Fix 1: Clear cache files
echo "<div class='step'>";
echo "<h3>Step 1: Clearing Cache Files</h3>";
$cacheFiles = [
    '../bootstrap/cache/config.php',
    '../bootstrap/cache/routes.php',
    '../bootstrap/cache/services.php',
    '../bootstrap/cache/packages.php'
];

foreach ($cacheFiles as $file) {
    if (file_exists($file)) {
        if (unlink($file)) {
            echo "<span class='ok'>✅ Deleted: " . basename($file) . "</span><br>";
            $fixes[] = "Deleted cache file: " . basename($file);
        } else {
            echo "<span class='error'>❌ Failed to delete: " . basename($file) . "</span><br>";
        }
    } else {
        echo "<span class='ok'>✅ Not found: " . basename($file) . "</span><br>";
    }
}
echo "</div>";

// Fix 2: Create/fix directories
echo "<div class='step'>";
echo "<h3>Step 2: Creating Required Directories</h3>";
$dirs = [
    '../storage/logs',
    '../storage/framework',
    '../storage/framework/cache',
    '../storage/framework/sessions',
    '../storage/framework/views',
    '../bootstrap/cache'
];

foreach ($dirs as $dir) {
    if (!file_exists($dir)) {
        if (mkdir($dir, 0775, true)) {
            echo "<span class='ok'>✅ Created: " . basename($dir) . "</span><br>";
            $fixes[] = "Created directory: " . basename($dir);
        } else {
            echo "<span class='error'>❌ Failed to create: " . basename($dir) . "</span><br>";
        }
    } else {
        echo "<span class='ok'>✅ Exists: " . basename($dir) . "</span><br>";
    }
}
echo "</div>";

// Fix 3: Fix permissions
echo "<div class='step'>";
echo "<h3>Step 3: Fixing Permissions</h3>";
$permDirs = ['../storage', '../bootstrap/cache'];
foreach ($permDirs as $dir) {
    if (file_exists($dir)) {
        if (chmod($dir, 0775)) {
            echo "<span class='ok'>✅ Fixed permissions: " . basename($dir) . "</span><br>";
            $fixes[] = "Fixed permissions: " . basename($dir);
        } else {
            echo "<span class='warning'>⚠️ Could not change permissions: " . basename($dir) . "</span><br>";
        }
    }
}
echo "</div>";

// Fix 4: Create minimal .env if missing
echo "<div class='step'>";
echo "<h3>Step 4: Checking Environment File</h3>";
if (!file_exists('../.env')) {
    $envContent = 'APP_NAME="Maxcon ERP"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_TIMEZONE=UTC
APP_URL=' . (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
LOG_CHANNEL=single
LOG_LEVEL=error
';
    
    if (file_put_contents('../.env', $envContent)) {
        echo "<span class='ok'>✅ Created basic .env file</span><br>";
        $fixes[] = "Created basic .env file";
    } else {
        echo "<span class='error'>❌ Failed to create .env file</span><br>";
    }
} else {
    echo "<span class='ok'>✅ .env file exists</span><br>";
}
echo "</div>";

// Fix 5: Create minimal bootstrap/app.php
echo "<div class='step'>";
echo "<h3>Step 5: Creating Minimal Bootstrap</h3>";
$minimalBootstrap = '<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.\'/../routes/web.php\',
        health: \'/up\',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e, $request) {
            return response(\'<h1>Service Temporarily Unavailable</h1><p>Please try again later.</p>\', 500);
        });
    })->create();
';

if (file_put_contents('../bootstrap/app.php', $minimalBootstrap)) {
    echo "<span class='ok'>✅ Created minimal bootstrap file</span><br>";
    $fixes[] = "Created minimal bootstrap file";
} else {
    echo "<span class='error'>❌ Failed to create bootstrap file</span><br>";
}
echo "</div>";

// Fix 6: Create minimal providers
echo "<div class='step'>";
echo "<h3>Step 6: Creating Minimal Providers</h3>";
$minimalProviders = '<?php

return [
    App\Providers\AppServiceProvider::class,
];
';

if (file_put_contents('../bootstrap/providers.php', $minimalProviders)) {
    echo "<span class='ok'>✅ Created minimal providers file</span><br>";
    $fixes[] = "Created minimal providers file";
} else {
    echo "<span class='error'>❌ Failed to create providers file</span><br>";
}
echo "</div>";

// Fix 7: Test Laravel loading
echo "<div class='step'>";
echo "<h3>Step 7: Testing Laravel</h3>";
try {
    if (file_exists('../vendor/autoload.php')) {
        require_once '../vendor/autoload.php';
        echo "<span class='ok'>✅ Autoloader works</span><br>";
        
        $app = require_once '../bootstrap/app.php';
        echo "<span class='ok'>✅ Laravel app loads</span><br>";
        $fixes[] = "Laravel application loads successfully";
    } else {
        echo "<span class='error'>❌ Composer autoloader missing - run 'composer install'</span><br>";
    }
} catch (Exception $e) {
    echo "<span class='error'>❌ Laravel error: " . htmlspecialchars($e->getMessage()) . "</span><br>";
}
echo "</div>";

// Summary
echo "<div class='step'>";
echo "<h3>🎯 Fix Summary</h3>";
if (count($fixes) > 0) {
    echo "<span class='ok'>Applied " . count($fixes) . " fixes:</span><br>";
    foreach ($fixes as $fix) {
        echo "• $fix<br>";
    }
} else {
    echo "<span class='warning'>No fixes were applied</span><br>";
}
echo "</div>";

echo "<div class='step'>";
echo "<h3>📋 Next Steps</h3>";
echo "<ol>";
echo "<li>Update your .env file with correct database credentials</li>";
echo "<li>Run: <code>composer install --no-dev</code></li>";
echo "<li>Run: <code>php artisan key:generate</code></li>";
echo "<li>Run: <code>php artisan migrate</code></li>";
echo "<li><a href='../'>Test your main site</a></li>";
echo "</ol>";
echo "</div>";

echo "<p><a href='debug.php'>← Run Debug Again</a> | <a href='simple-test.php'>Simple Test</a></p>";

echo "</body></html>";
?>
