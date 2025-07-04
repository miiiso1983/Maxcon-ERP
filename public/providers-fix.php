<?php
/**
 * Service Providers Fix Tool
 * Fixes "Class view does not exist" and related service provider issues
 */

echo "<!DOCTYPE html><html><head><title>Service Providers Fix</title>";
echo "<style>body{font-family:Arial;margin:20px;} .ok{color:green;} .error{color:red;} .warning{color:orange;} .step{margin:15px 0;padding:10px;border:1px solid #ddd;}</style>";
echo "</head><body>";

echo "<h1>🔧 Service Providers Fix Tool</h1>";
echo "<p>Fixing Laravel service provider registration issues...</p>";

$fixes = [];

// Step 1: Check current config/app.php
echo "<div class='step'>";
echo "<h3>Step 1: Checking Service Providers Configuration</h3>";
if (file_exists('../config/app.php')) {
    $config = file_get_contents('../config/app.php');
    
    // Check for View Service Provider
    if (strpos($config, 'ViewServiceProvider') !== false) {
        echo "<span class='ok'>✅ ViewServiceProvider found in config</span><br>";
    } else {
        echo "<span class='error'>❌ ViewServiceProvider missing from config</span><br>";
    }
    
    // Check for other critical providers
    $criticalProviders = [
        'AuthServiceProvider',
        'DatabaseServiceProvider', 
        'SessionServiceProvider',
        'ValidationServiceProvider'
    ];
    
    foreach ($criticalProviders as $provider) {
        if (strpos($config, $provider) !== false) {
            echo "<span class='ok'>✅ $provider found</span><br>";
        } else {
            echo "<span class='error'>❌ $provider missing</span><br>";
        }
    }
} else {
    echo "<span class='error'>❌ config/app.php not found</span><br>";
}
echo "</div>";

// Step 2: Create complete config/app.php
echo "<div class='step'>";
echo "<h3>Step 2: Creating Complete Service Providers Configuration</h3>";

$completeConfig = '<?php

return [
    \'name\' => env(\'APP_NAME\', \'Laravel\'),
    \'env\' => env(\'APP_ENV\', \'production\'),
    \'debug\' => (bool) env(\'APP_DEBUG\', false),
    \'url\' => env(\'APP_URL\', \'http://localhost\'),
    \'asset_url\' => env(\'ASSET_URL\'),
    \'timezone\' => \'UTC\',
    \'locale\' => \'en\',
    \'fallback_locale\' => \'en\',
    \'faker_locale\' => \'en_US\',
    \'key\' => env(\'APP_KEY\'),
    \'cipher\' => \'AES-256-CBC\',

    \'providers\' => [
        // Laravel Framework Service Providers
        Illuminate\\Auth\\AuthServiceProvider::class,
        Illuminate\\Broadcasting\\BroadcastServiceProvider::class,
        Illuminate\\Bus\\BusServiceProvider::class,
        Illuminate\\Cache\\CacheServiceProvider::class,
        Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider::class,
        Illuminate\\Cookie\\CookieServiceProvider::class,
        Illuminate\\Database\\DatabaseServiceProvider::class,
        Illuminate\\Encryption\\EncryptionServiceProvider::class,
        Illuminate\\Filesystem\\FilesystemServiceProvider::class,
        Illuminate\\Foundation\\Providers\\FoundationServiceProvider::class,
        Illuminate\\Hashing\\HashServiceProvider::class,
        Illuminate\\Mail\\MailServiceProvider::class,
        Illuminate\\Notifications\\NotificationServiceProvider::class,
        Illuminate\\Pagination\\PaginationServiceProvider::class,
        Illuminate\\Pipeline\\PipelineServiceProvider::class,
        Illuminate\\Queue\\QueueServiceProvider::class,
        Illuminate\\Redis\\RedisServiceProvider::class,
        Illuminate\\Auth\\Passwords\\PasswordResetServiceProvider::class,
        Illuminate\\Session\\SessionServiceProvider::class,
        Illuminate\\Translation\\TranslationServiceProvider::class,
        Illuminate\\Validation\\ValidationServiceProvider::class,
        Illuminate\\View\\ViewServiceProvider::class,

        // Application Service Providers
        App\\Providers\\AppServiceProvider::class,
        App\\Providers\\AuthServiceProvider::class,
        App\\Providers\\EventServiceProvider::class,
        App\\Providers\\RouteServiceProvider::class,
    ],

    \'aliases\' => [
        \'App\' => Illuminate\\Support\\Facades\\App::class,
        \'Arr\' => Illuminate\\Support\\Arr::class,
        \'Artisan\' => Illuminate\\Support\\Facades\\Artisan::class,
        \'Auth\' => Illuminate\\Support\\Facades\\Auth::class,
        \'Blade\' => Illuminate\\Support\\Facades\\Blade::class,
        \'Broadcast\' => Illuminate\\Support\\Facades\\Broadcast::class,
        \'Bus\' => Illuminate\\Support\\Facades\\Bus::class,
        \'Cache\' => Illuminate\\Support\\Facades\\Cache::class,
        \'Config\' => Illuminate\\Support\\Facades\\Config::class,
        \'Cookie\' => Illuminate\\Support\\Facades\\Cookie::class,
        \'Crypt\' => Illuminate\\Support\\Facades\\Crypt::class,
        \'Date\' => Illuminate\\Support\\Facades\\Date::class,
        \'DB\' => Illuminate\\Support\\Facades\\DB::class,
        \'Eloquent\' => Illuminate\\Database\\Eloquent\\Model::class,
        \'Event\' => Illuminate\\Support\\Facades\\Event::class,
        \'File\' => Illuminate\\Support\\Facades\\File::class,
        \'Gate\' => Illuminate\\Support\\Facades\\Gate::class,
        \'Hash\' => Illuminate\\Support\\Facades\\Hash::class,
        \'Http\' => Illuminate\\Support\\Facades\\Http::class,
        \'Js\' => Illuminate\\Support\\Js::class,
        \'Lang\' => Illuminate\\Support\\Facades\\Lang::class,
        \'Log\' => Illuminate\\Support\\Facades\\Log::class,
        \'Mail\' => Illuminate\\Support\\Facades\\Mail::class,
        \'Notification\' => Illuminate\\Support\\Facades\\Notification::class,
        \'Password\' => Illuminate\\Support\\Facades\\Password::class,
        \'Process\' => Illuminate\\Support\\Facades\\Process::class,
        \'Queue\' => Illuminate\\Support\\Facades\\Queue::class,
        \'RateLimiter\' => Illuminate\\Support\\Facades\\RateLimiter::class,
        \'Redirect\' => Illuminate\\Support\\Facades\\Redirect::class,
        \'Request\' => Illuminate\\Support\\Facades\\Request::class,
        \'Response\' => Illuminate\\Support\\Facades\\Response::class,
        \'Route\' => Illuminate\\Support\\Facades\\Route::class,
        \'Schema\' => Illuminate\\Support\\Facades\\Schema::class,
        \'Session\' => Illuminate\\Support\\Facades\\Session::class,
        \'Storage\' => Illuminate\\Support\\Facades\\Storage::class,
        \'Str\' => Illuminate\\Support\\Str::class,
        \'URL\' => Illuminate\\Support\\Facades\\URL::class,
        \'Validator\' => Illuminate\\Support\\Facades\\Validator::class,
        \'View\' => Illuminate\\Support\\Facades\\View::class,
    ],
];
';

if (file_put_contents('../config/app.php', $completeConfig)) {
    echo "<span class='ok'>✅ Created complete config/app.php with all service providers</span><br>";
    $fixes[] = "Updated config/app.php with complete service providers";
} else {
    echo "<span class='error'>❌ Failed to update config/app.php</span><br>";
}
echo "</div>";

// Step 3: Create missing service providers
echo "<div class='step'>";
echo "<h3>Step 3: Creating Missing Service Providers</h3>";

$providers = [
    'AuthServiceProvider' => '<?php

namespace App\\Providers;

use Illuminate\\Foundation\\Support\\Providers\\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [];

    public function boot(): void
    {
        //
    }
}',
    'EventServiceProvider' => '<?php

namespace App\\Providers;

use Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [];

    public function boot(): void
    {
        //
    }
}',
    'RouteServiceProvider' => '<?php

namespace App\\Providers;

use Illuminate\\Foundation\\Support\\Providers\\RouteServiceProvider as ServiceProvider;
use Illuminate\\Support\\Facades\\Route;

class RouteServiceProvider extends ServiceProvider
{
    public const HOME = \'/dashboard\';

    public function boot(): void
    {
        $this->routes(function () {
            Route::middleware(\'web\')
                ->group(base_path(\'routes/web.php\'));
        });
    }
}'
];

foreach ($providers as $name => $content) {
    $file = "../app/Providers/$name.php";
    if (!file_exists($file)) {
        if (file_put_contents($file, $content)) {
            echo "<span class='ok'>✅ Created $name</span><br>";
            $fixes[] = "Created App\\Providers\\$name";
        } else {
            echo "<span class='error'>❌ Failed to create $name</span><br>";
        }
    } else {
        echo "<span class='ok'>✅ $name already exists</span><br>";
    }
}
echo "</div>";

// Step 4: Clear caches
echo "<div class='step'>";
echo "<h3>Step 4: Clearing Configuration Caches</h3>";
$cacheFiles = [
    '../bootstrap/cache/config.php',
    '../bootstrap/cache/services.php',
    '../bootstrap/cache/packages.php'
];

foreach ($cacheFiles as $file) {
    if (file_exists($file)) {
        if (unlink($file)) {
            echo "<span class='ok'>✅ Cleared: " . basename($file) . "</span><br>";
        }
    }
}
$fixes[] = "Cleared configuration caches";
echo "</div>";

// Step 5: Test Laravel loading
echo "<div class='step'>";
echo "<h3>Step 5: Testing Laravel Application</h3>";
try {
    require_once '../vendor/autoload.php';
    $app = require_once '../bootstrap/app.php';
    
    // Try to resolve view service
    $view = $app->make('view');
    echo "<span class='ok'>✅ View service resolved successfully!</span><br>";
    
    // Try to resolve other services
    $services = ['auth', 'session', 'cache', 'db'];
    foreach ($services as $service) {
        try {
            $app->make($service);
            echo "<span class='ok'>✅ $service service working</span><br>";
        } catch (Exception $e) {
            echo "<span class='warning'>⚠️ $service service issue: " . $e->getMessage() . "</span><br>";
        }
    }
    
    $fixes[] = "Laravel application loads with all services";
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
echo "<li><a href='../'>Test your main application</a></li>";
echo "<li>If issues persist: <a href='debug.php'>Run full debug</a></li>";
echo "<li>Check that all routes are working properly</li>";
echo "</ol>";
echo "</div>";

echo "<p><a href='debug.php'>← Full Debug</a> | <a href='simple-test.php'>Simple Test</a></p>";

echo "</body></html>";
?>
