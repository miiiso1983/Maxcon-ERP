<?php
/**
 * Emergency Laravel Index
 * Use this if the main index.php is causing issues
 */

// Error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Define paths
define('LARAVEL_START', microtime(true));

// Check if Laravel is properly installed
if (!file_exists(__DIR__.'/../vendor/autoload.php')) {
    die('
    <!DOCTYPE html>
    <html>
    <head><title>Installation Required</title></head>
    <body style="font-family:Arial;margin:50px;text-align:center;">
        <h1>🚨 Installation Required</h1>
        <p>Laravel dependencies are not installed.</p>
        <p>Please run: <code>composer install</code></p>
        <p><a href="debug.php">Debug Tools</a> | <a href="simple-test.php">Simple Test</a></p>
    </body>
    </html>
    ');
}

try {
    // Register the Composer autoloader
    require __DIR__.'/../vendor/autoload.php';
    
    // Bootstrap Laravel application
    $app = require_once __DIR__.'/../bootstrap/app.php';
    
    // Handle the request
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    
    $request = Illuminate\Http\Request::capture();
    $response = $kernel->handle($request);
    
    $response->send();
    
    $kernel->terminate($request, $response);
    
} catch (Exception $e) {
    // Emergency error page
    http_response_code(500);
    echo '
    <!DOCTYPE html>
    <html>
    <head>
        <title>Service Unavailable</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 50px; text-align: center; background: #f8f9fa; }
            .container { max-width: 600px; margin: 0 auto; background: white; padding: 40px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            .error { color: #dc3545; }
            .tools { margin-top: 30px; }
            .tools a { display: inline-block; margin: 10px; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>🚨 Service Temporarily Unavailable</h1>
            <p>We are experiencing technical difficulties.</p>
            <p class="error">Error: ' . htmlspecialchars($e->getMessage()) . '</p>
            
            <div class="tools">
                <h3>Diagnostic Tools:</h3>
                <a href="debug.php">Full Debug</a>
                <a href="simple-test.php">Simple Test</a>
                <a href="fix-now.php">Auto Fix</a>
            </div>
            
            <p><small>Error occurred at: ' . date('Y-m-d H:i:s') . '</small></p>
        </div>
    </body>
    </html>
    ';
} catch (Error $e) {
    // PHP Fatal Error
    http_response_code(500);
    echo '
    <!DOCTYPE html>
    <html>
    <head><title>Fatal Error</title></head>
    <body style="font-family:Arial;margin:50px;text-align:center;">
        <h1>🚨 Fatal Error</h1>
        <p>A critical error occurred.</p>
        <p style="color:red;">Error: ' . htmlspecialchars($e->getMessage()) . '</p>
        <p><a href="debug.php">Debug Tools</a></p>
    </body>
    </html>
    ';
}
?>
