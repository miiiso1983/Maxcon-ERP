<?php
/**
 * Laravel Version Compatibility Fix
 * Fixes BadMethodCallException: configure does not exist
 */

echo "<!DOCTYPE html><html><head><title>Compatibility Fix</title>";
echo "<style>body{font-family:Arial;margin:20px;} .ok{color:green;} .error{color:red;} .step{margin:15px 0;padding:10px;border:1px solid #ddd;}</style>";
echo "</head><body>";

echo "<h1>🔧 Laravel Compatibility Fix</h1>";
echo "<p>Fixing Laravel 11 syntax compatibility issues...</p>";

$fixes = [];

// Step 1: Check Laravel version
echo "<div class='step'>";
echo "<h3>Step 1: Checking Laravel Version</h3>";
if (file_exists('../vendor/laravel/framework/src/Illuminate/Foundation/Application.php')) {
    $appFile = file_get_contents('../vendor/laravel/framework/src/Illuminate/Foundation/Application.php');
    if (strpos($appFile, 'function configure') !== false) {
        echo "<span class='ok'>✅ Laravel 11+ detected</span><br>";
    } else {
        echo "<span class='error'>⚠️ Laravel 10 or older detected - applying compatibility fixes</span><br>";
        $fixes[] = "Detected older Laravel version";
    }
} else {
    echo "<span class='error'>❌ Laravel framework not found</span><br>";
}
echo "</div>";

// Step 2: Create Laravel 10 compatible bootstrap/app.php
echo "<div class='step'>";
echo "<h3>Step 2: Creating Compatible Bootstrap</h3>";
$compatibleBootstrap = '<?php

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
*/

$app = new Illuminate\Foundation\Application(
    $_ENV[\'APP_BASE_PATH\'] ?? dirname(__DIR__)
);

/*
|--------------------------------------------------------------------------
| Bind Important Interfaces
|--------------------------------------------------------------------------
*/

$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    App\Http\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

/*
|--------------------------------------------------------------------------
| Return The Application
|--------------------------------------------------------------------------
*/

return $app;
';

if (file_put_contents('../bootstrap/app.php', $compatibleBootstrap)) {
    echo "<span class='ok'>✅ Created Laravel 10 compatible bootstrap</span><br>";
    $fixes[] = "Updated bootstrap/app.php for compatibility";
} else {
    echo "<span class='error'>❌ Failed to update bootstrap</span><br>";
}
echo "</div>";

// Step 3: Create required Kernel files
echo "<div class='step'>";
echo "<h3>Step 3: Creating Required Kernel Files</h3>";

// Check if App\Http\Kernel exists
if (!file_exists('../app/Http/Kernel.php')) {
    $httpKernel = '<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    protected $middleware = [
        \Illuminate\Http\Middleware\HandleCors::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    protected $middlewareGroups = [
        \'web\' => [
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
        \'api\' => [
            \Illuminate\Routing\Middleware\ThrottleRequests::class.\':api\',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    protected $middlewareAliases = [
        \'auth\' => \Illuminate\Auth\Middleware\Authenticate::class,
        \'guest\' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        \'throttle\' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
    ];
}
';
    
    if (file_put_contents('../app/Http/Kernel.php', $httpKernel)) {
        echo "<span class='ok'>✅ Created Http Kernel</span><br>";
        $fixes[] = "Created App\Http\Kernel";
    }
}

// Check if App\Console\Kernel exists
if (!file_exists('../app/Console/Kernel.php')) {
    $consoleKernel = '<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        //
    }

    protected function commands(): void
    {
        require base_path(\'routes/console.php\');
    }
}
';
    
    if (file_put_contents('../app/Console/Kernel.php', $consoleKernel)) {
        echo "<span class='ok'>✅ Created Console Kernel</span><br>";
        $fixes[] = "Created App\Console\Kernel";
    }
}

// Check if App\Exceptions\Handler exists
if (!file_exists('../app/Exceptions/Handler.php')) {
    $exceptionHandler = '<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        \'current_password\',
        \'password\',
        \'password_confirmation\',
    ];

    public function register(): void
    {
        //
    }

    public function render($request, Throwable $exception)
    {
        if (app()->environment(\'production\')) {
            return response(\'<h1>Service Unavailable</h1><p>Please try again later.</p>\', 500);
        }
        
        return parent::render($request, $exception);
    }
}
';
    
    if (file_put_contents('../app/Exceptions/Handler.php', $exceptionHandler)) {
        echo "<span class='ok'>✅ Created Exception Handler</span><br>";
        $fixes[] = "Created App\Exceptions\Handler";
    }
}

echo "</div>";

// Step 4: Clear caches
echo "<div class='step'>";
echo "<h3>Step 4: Clearing Caches</h3>";
$cacheFiles = [
    '../bootstrap/cache/config.php',
    '../bootstrap/cache/routes.php',
    '../bootstrap/cache/services.php'
];

foreach ($cacheFiles as $file) {
    if (file_exists($file)) {
        unlink($file);
        echo "<span class='ok'>✅ Cleared: " . basename($file) . "</span><br>";
    }
}
$fixes[] = "Cleared all cache files";
echo "</div>";

// Step 5: Test Laravel
echo "<div class='step'>";
echo "<h3>Step 5: Testing Laravel</h3>";
try {
    require_once '../vendor/autoload.php';
    $app = require_once '../bootstrap/app.php';
    echo "<span class='ok'>✅ Laravel loads successfully!</span><br>";
    $fixes[] = "Laravel application loads without errors";
} catch (Exception $e) {
    echo "<span class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</span><br>";
}
echo "</div>";

// Summary
echo "<div class='step'>";
echo "<h3>🎯 Fix Summary</h3>";
echo "<span class='ok'>Applied " . count($fixes) . " compatibility fixes:</span><br>";
foreach ($fixes as $fix) {
    echo "• $fix<br>";
}
echo "</div>";

echo "<div class='step'>";
echo "<h3>📋 Next Steps</h3>";
echo "<ol>";
echo "<li><a href='../'>Test your main application</a></li>";
echo "<li>If issues persist, run: <a href='debug.php'>Full Debug</a></li>";
echo "<li>Check error logs for any remaining issues</li>";
echo "</ol>";
echo "</div>";

echo "</body></html>";
?>
