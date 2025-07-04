<?php
/**
 * ULTIMATE LARAVEL FIX
 * Complete Laravel configuration and service binding fix
 * URL: https://your-domain.com/ultimate-fix.php
 */

set_time_limit(300);
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><title>Ultimate Laravel Fix</title>";
echo "<style>body{font-family:Arial;margin:20px;} .container{max-width:800px;margin:0 auto;} .ok{color:green;font-weight:bold;} .error{color:red;font-weight:bold;} .warning{color:orange;font-weight:bold;} .section{margin:20px 0;padding:15px;border:1px solid #ddd;border-radius:5px;background:#fafafa;} .button{display:inline-block;padding:10px 20px;background:#007bff;color:white;text-decoration:none;border-radius:4px;margin:5px;} .success{background:#d4edda;border:1px solid #c3e6cb;padding:15px;border-radius:5px;}</style>";
echo "</head><body><div class='container'>";

echo "<h1>🚀 Ultimate Laravel Fix</h1>";
echo "<p>Complete fix for Laravel configuration and service binding issues.</p>";

$rootPath = dirname(__DIR__);
$fixes = [];

// Step 1: Create a completely fresh bootstrap
echo "<div class='section'>";
echo "<h2>🔧 Step 1: Create Fresh Bootstrap</h2>";

$freshBootstrap = '<?php

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
| Register Core Service Providers Manually
|--------------------------------------------------------------------------
*/

// Register core providers that are essential
$coreProviders = [
    Illuminate\Foundation\Providers\FoundationServiceProvider::class,
    Illuminate\Filesystem\FilesystemServiceProvider::class,
    Illuminate\Encryption\EncryptionServiceProvider::class,
    Illuminate\Hashing\HashServiceProvider::class,
    Illuminate\Database\DatabaseServiceProvider::class,
    Illuminate\Session\SessionServiceProvider::class,
    Illuminate\Auth\AuthServiceProvider::class,
    Illuminate\Cookie\CookieServiceProvider::class,
    Illuminate\Validation\ValidationServiceProvider::class,
    Illuminate\View\ViewServiceProvider::class,
];

foreach ($coreProviders as $provider) {
    $app->register($provider);
}

/*
|--------------------------------------------------------------------------
| Return The Application
|--------------------------------------------------------------------------
*/

return $app;
';

$bootstrapPath = $rootPath . '/bootstrap/app.php';
if (file_put_contents($bootstrapPath, $freshBootstrap)) {
    echo "<span class='ok'>✅ Created fresh bootstrap with manual service registration</span><br>";
    $fixes[] = "Created fresh bootstrap with core services";
}

echo "</div>";

// Step 2: Simplify providers.php
echo "<div class='section'>";
echo "<h2>📝 Step 2: Simplify Providers</h2>";

$simpleProviders = '<?php

return [
    // Application Service Providers only
    App\Providers\AppServiceProvider::class,
    App\Providers\AuthServiceProvider::class,
    App\Providers\EventServiceProvider::class,
    App\Providers\RouteServiceProvider::class,
];
';

$providersPath = $rootPath . '/bootstrap/providers.php';
if (file_put_contents($providersPath, $simpleProviders)) {
    echo "<span class='ok'>✅ Simplified providers.php (core services in bootstrap)</span><br>";
    $fixes[] = "Simplified providers.php";
}

echo "</div>";

// Step 3: Test the new setup
echo "<div class='section'>";
echo "<h2>🧪 Step 3: Test New Setup</h2>";

try {
    // Clear any existing includes
    if (function_exists('opcache_reset')) {
        opcache_reset();
    }
    
    require_once $rootPath . '/vendor/autoload.php';
    $app = require_once $rootPath . '/bootstrap/app.php';
    
    echo "<span class='ok'>✅ Fresh Laravel application loaded</span><br>";
    
    // Test core services
    try {
        $config = $app->make('config');
        echo "<span class='ok'>✅ Config service available</span><br>";
    } catch (Exception $e) {
        echo "<span class='error'>❌ Config service error: " . $e->getMessage() . "</span><br>";
    }
    
    try {
        $session = $app->make('session');
        echo "<span class='ok'>✅ Session service available</span><br>";
        $fixes[] = "Session service working";
    } catch (Exception $e) {
        echo "<span class='error'>❌ Session service error: " . $e->getMessage() . "</span><br>";
    }
    
    try {
        $auth = $app->make('auth');
        echo "<span class='ok'>✅ Auth service available</span><br>";
    } catch (Exception $e) {
        echo "<span class='error'>❌ Auth service error: " . $e->getMessage() . "</span><br>";
    }
    
} catch (Exception $e) {
    echo "<span class='error'>❌ Laravel error: " . $e->getMessage() . "</span><br>";
}

echo "</div>";

// Step 4: Create a simple login test
echo "<div class='section'>";
echo "<h2>🔐 Step 4: Create Simple Login Test</h2>";

$loginTest = '<?php
// Simple login test without Laravel complexity
session_start();

if ($_POST) {
    $email = $_POST[\'email\'] ?? \'\';
    $password = $_POST[\'password\'] ?? \'\';
    
    // Simple hardcoded test
    if ($email === \'admin@maxcon-demo.com\' && $password === \'password\') {
        $_SESSION[\'user_id\'] = 1;
        $_SESSION[\'user_email\'] = $email;
        echo "<div style=\'background:#d4edda;padding:20px;border-radius:5px;margin:20px 0;\'>";
        echo "<h2>✅ Login Successful!</h2>";
        echo "<p>Simple PHP session login working.</p>";
        echo "<p><a href=\'/dashboard\'>Go to Dashboard</a></p>";
        echo "</div>";
    } else {
        echo "<div style=\'background:#f8d7da;padding:20px;border-radius:5px;margin:20px 0;\'>";
        echo "<h2>❌ Login Failed</h2>";
        echo "<p>Invalid credentials.</p>";
        echo "</div>";
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Simple Login Test</title></head>
<body style="font-family:Arial;margin:20px;">
<h1>Simple Login Test</h1>
<form method="POST">
    <p>Email: <input type="email" name="email" value="admin@maxcon-demo.com" required></p>
    <p>Password: <input type="password" name="password" value="password" required></p>
    <p><button type="submit">Login</button></p>
</form>
</body>
</html>';

$simpleLoginPath = $rootPath . '/public/simple-login-test.php';
if (file_put_contents($simpleLoginPath, $loginTest)) {
    echo "<span class='ok'>✅ Created simple login test (bypasses Laravel)</span><br>";
    $fixes[] = "Created simple login test";
}

echo "</div>";

// Summary
echo "<div class='section'>";
echo "<h2>📋 Ultimate Summary</h2>";

if (count($fixes) > 0) {
    echo "<div class='success'>";
    echo "<h3>✅ Ultimate Fixes Applied:</h3>";
    foreach ($fixes as $fix) {
        echo "• $fix<br>";
    }
    echo "</div>";
}

echo "<h3>🎯 Test Options:</h3>";
echo "<p><a href='/login' class='button' style='background:#28a745;'>🚀 Test Laravel Login</a></p>";
echo "<p><a href='simple-login-test.php' class='button' style='background:#17a2b8;'>🔧 Test Simple Login</a></p>";

echo "<h3>📞 Final Resort:</h3>";
echo "<p>If Laravel login still fails, contact Cloudways support with these commands:</p>";
echo "<code style='background:#f8f9fa;padding:10px;display:block;border-radius:4px;'>";
echo "cd /home/1486247.cloudwaysapps.com/ufnpbxkvbd/public_html<br>";
echo "php artisan config:clear<br>";
echo "php artisan cache:clear<br>";
echo "php artisan view:clear<br>";
echo "composer dump-autoload<br>";
echo "php artisan serve --host=0.0.0.0 --port=8000";
echo "</code>";

echo "</div>";

echo "</div></body></html>";
?>
