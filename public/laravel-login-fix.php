<?php
/**
 * LARAVEL LOGIN FIX
 * Direct Laravel login processor to bypass routing issues
 * URL: https://your-domain.com/laravel-login-fix.php
 */

set_time_limit(300);
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><title>Laravel Login Fix</title>";
echo "<style>body{font-family:Arial;margin:20px;} .container{max-width:800px;margin:0 auto;} .ok{color:green;font-weight:bold;} .error{color:red;font-weight:bold;} .warning{color:orange;font-weight:bold;} .section{margin:20px 0;padding:15px;border:1px solid #ddd;border-radius:5px;background:#fafafa;} .form{background:#f8f9fa;padding:20px;border-radius:8px;margin:20px 0;} input{width:100%;padding:10px;margin:5px 0;border:1px solid #ddd;border-radius:4px;box-sizing:border-box;} button{width:100%;padding:12px;background:#007bff;color:white;border:none;border-radius:4px;cursor:pointer;}</style>";
echo "</head><body><div class='container'>";

echo "<h1>🔧 Laravel Login Fix</h1>";
echo "<p>Direct Laravel authentication bypass to fix login issues.</p>";

$rootPath = dirname(__DIR__);

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<div class='section'>";
    echo "<h2>🔄 Processing Laravel Login...</h2>";
    
    try {
        // Bootstrap Laravel
        require_once $rootPath . '/vendor/autoload.php';
        $app = require_once $rootPath . '/bootstrap/app.php';
        
        // Handle the request through Laravel
        $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
        
        // Create a request object
        $request = Illuminate\Http\Request::capture();
        
        // Override request data for login
        $request->merge([
            'email' => $_POST['email'] ?? '',
            'password' => $_POST['password'] ?? '',
            '_token' => csrf_token()
        ]);
        
        // Set the request method and URI
        $request->setMethod('POST');
        $request->server->set('REQUEST_URI', '/login');
        
        echo "<span class='ok'>✅ Laravel bootstrapped successfully</span><br>";
        echo "<span class='ok'>✅ Request created successfully</span><br>";
        
        // Try to authenticate directly
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if ($email && $password) {
            // Use Laravel's Auth facade
            $credentials = ['email' => $email, 'password' => $password];
            
            if (Auth::attempt($credentials)) {
                echo "<span class='ok'>✅ Authentication successful!</span><br>";
                echo "<div style='background:#d4edda;padding:15px;border-radius:5px;margin:10px 0;'>";
                echo "<h3>🎉 Login Successful!</h3>";
                echo "<p>Laravel authentication is working. Redirecting to dashboard...</p>";
                echo "<script>setTimeout(function(){ window.location.href = '/dashboard'; }, 2000);</script>";
                echo "</div>";
            } else {
                echo "<span class='error'>❌ Invalid credentials</span><br>";
                echo "<p>Please check your email and password.</p>";
            }
        }
        
    } catch (Exception $e) {
        echo "<span class='error'>❌ Laravel error: " . $e->getMessage() . "</span><br>";
        echo "<div style='background:#f8d7da;padding:15px;border-radius:5px;margin:10px 0;'>";
        echo "<h4>Error Details:</h4>";
        echo "<p><strong>File:</strong> " . $e->getFile() . "</p>";
        echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
        echo "<p><strong>Message:</strong> " . $e->getMessage() . "</p>";
        echo "</div>";
    }
    echo "</div>";
}

// Show login form
echo "<div class='form'>";
echo "<h3>🔐 Direct Laravel Login</h3>";
echo "<p>This form processes login directly through Laravel's authentication system.</p>";
echo "<form method='POST' action='" . $_SERVER['PHP_SELF'] . "'>";
echo "<label>Email:</label>";
echo "<input type='email' name='email' value='admin@maxcon-demo.com' required>";
echo "<label>Password:</label>";
echo "<input type='password' name='password' value='password' required>";
echo "<button type='submit'>Process Laravel Login</button>";
echo "</form>";
echo "</div>";

// Check Laravel configuration
echo "<div class='section'>";
echo "<h2>🔍 Laravel Configuration Check</h2>";

try {
    require_once $rootPath . '/vendor/autoload.php';
    $app = require_once $rootPath . '/bootstrap/app.php';
    
    echo "<span class='ok'>✅ Laravel application loaded</span><br>";
    
    // Check if Auth facade is available
    if (class_exists('Illuminate\Support\Facades\Auth')) {
        echo "<span class='ok'>✅ Auth facade available</span><br>";
    }
    
    // Check database connection
    try {
        $pdo = DB::connection()->getPdo();
        echo "<span class='ok'>✅ Database connection working</span><br>";
    } catch (Exception $e) {
        echo "<span class='error'>❌ Database connection failed: " . $e->getMessage() . "</span><br>";
    }
    
    // Check if User model exists
    if (class_exists('App\Models\User')) {
        echo "<span class='ok'>✅ User model available</span><br>";
        
        // Try to count users
        try {
            $userCount = App\Models\User::count();
            echo "<span class='ok'>✅ Found $userCount users in database</span><br>";
        } catch (Exception $e) {
            echo "<span class='error'>❌ User query failed: " . $e->getMessage() . "</span><br>";
        }
    }
    
} catch (Exception $e) {
    echo "<span class='error'>❌ Laravel configuration error: " . $e->getMessage() . "</span><br>";
}
echo "</div>";

// Show recommendations
echo "<div class='section'>";
echo "<h2>📋 Recommendations</h2>";

echo "<h3>If Laravel login works here but not on main site:</h3>";
echo "<ul>";
echo "<li>The issue is with Laravel's routing system</li>";
echo "<li>Check routes/web.php and routes/auth.php for conflicts</li>";
echo "<li>Clear route cache: <code>php artisan route:clear</code></li>";
echo "<li>Check middleware configuration</li>";
echo "</ul>";

echo "<h3>If Laravel login fails here too:</h3>";
echo "<ul>";
echo "<li>Check Laravel error logs in storage/logs/</li>";
echo "<li>Verify database connection and user table</li>";
echo "<li>Check .env configuration</li>";
echo "<li>Verify Laravel installation integrity</li>";
echo "</ul>";

echo "</div>";

echo "<div style='background:#e7f3ff;padding:15px;border-radius:5px;margin:20px 0;'>";
echo "<h3>🎯 Next Steps</h3>";
echo "<p>1. Try the Laravel login form above</p>";
echo "<p>2. Compare results with your main login page</p>";
echo "<p>3. Check the error details if login fails</p>";
echo "<p>4. Apply the recommended fixes based on results</p>";
echo "</div>";

echo "</div></body></html>";
?>
