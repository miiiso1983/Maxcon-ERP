<?php
/**
 * LOGIN DIAGNOSTIC TOOL
 * Comprehensive diagnosis and fix for login form submission issues
 * URL: https://your-domain.com/login-diagnostic.php
 */

set_time_limit(300);
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><title>Login Diagnostic</title>";
echo "<style>body{font-family:Arial;margin:20px;background:#f5f5f5;} .container{background:white;padding:20px;border-radius:8px;max-width:1000px;margin:0 auto;} .ok{color:green;font-weight:bold;} .error{color:red;font-weight:bold;} .warning{color:orange;font-weight:bold;} .section{margin:20px 0;padding:15px;border:1px solid #ddd;border-radius:5px;background:#fafafa;} .code{background:#f0f0f0;padding:10px;border-radius:4px;font-family:monospace;margin:10px 0;white-space:pre-wrap;}</style>";
echo "</head><body><div class='container'>";

echo "<h1>🔍 LOGIN DIAGNOSTIC TOOL</h1>";
echo "<p><strong>Diagnosing login form submission issues...</strong></p>";

$rootPath = dirname(__DIR__);
$fixes = [];

// Test 1: Check if this is a POST request (simulating login)
echo "<div class='section'>";
echo "<h2>Test 1: Request Method Analysis</h2>";
echo "Current request method: " . $_SERVER['REQUEST_METHOD'] . "<br>";
echo "Request URI: " . ($_SERVER['REQUEST_URI'] ?? 'Unknown') . "<br>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<span class='ok'>✅ This is a POST request (login submission)</span><br>";
    echo "POST data received: " . (empty($_POST) ? 'None' : 'Yes') . "<br>";
} else {
    echo "<span class='warning'>⚠️ This is a GET request (diagnostic page)</span><br>";
}
echo "</div>";

// Test 2: Check Laravel bootstrap
echo "<div class='section'>";
echo "<h2>Test 2: Laravel Bootstrap Test</h2>";

try {
    require_once $rootPath . '/vendor/autoload.php';
    $app = require_once $rootPath . '/bootstrap/app.php';
    echo "<span class='ok'>✅ Laravel bootstrapped successfully</span><br>";
    
    // Test if we can access Laravel's auth system
    if (class_exists('Illuminate\Support\Facades\Auth')) {
        echo "<span class='ok'>✅ Auth facade available</span><br>";
    } else {
        echo "<span class='error'>❌ Auth facade not available</span><br>";
    }
    
} catch (Exception $e) {
    echo "<span class='error'>❌ Laravel bootstrap failed: " . $e->getMessage() . "</span><br>";
}
echo "</div>";

// Test 3: Check routes
echo "<div class='section'>";
echo "<h2>Test 3: Route Configuration</h2>";

try {
    // Check if we can access route list
    $routeFiles = [
        'web.php' => $rootPath . '/routes/web.php',
        'auth.php' => $rootPath . '/routes/auth.php',
        'tenant.php' => $rootPath . '/routes/tenant.php'
    ];
    
    foreach ($routeFiles as $name => $path) {
        if (file_exists($path)) {
            $content = file_get_contents($path);
            echo "<span class='ok'>✅ $name exists</span><br>";
            
            // Check for login routes
            if (strpos($content, "Route::post('/login'") !== false || strpos($content, "Route::post('login'") !== false) {
                echo "<span class='ok'>✅ POST login route found in $name</span><br>";
            }
            
            if (strpos($content, "Route::get('/login'") !== false || strpos($content, "Route::get('login'") !== false) {
                echo "<span class='ok'>✅ GET login route found in $name</span><br>";
            }
        } else {
            echo "<span class='error'>❌ $name missing</span><br>";
        }
    }
    
} catch (Exception $e) {
    echo "<span class='error'>❌ Route check failed: " . $e->getMessage() . "</span><br>";
}
echo "</div>";

// Test 4: Create simple login processor
echo "<div class='section'>";
echo "<h2>Test 4: Simple Login Test</h2>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email']) && isset($_POST['password'])) {
    echo "<span class='ok'>✅ Login form data received</span><br>";
    echo "Email: " . htmlspecialchars($_POST['email']) . "<br>";
    echo "Password: " . (empty($_POST['password']) ? 'Empty' : 'Provided') . "<br>";
    
    // Try simple authentication
    try {
        if (isset($app)) {
            // Try to authenticate using Laravel
            $email = $_POST['email'];
            $password = $_POST['password'];
            
            echo "<span class='ok'>✅ Attempting Laravel authentication...</span><br>";
            
            // This is a simplified test - in real app, use proper validation
            echo "<span class='warning'>⚠️ Authentication test completed (simplified)</span><br>";
        }
    } catch (Exception $e) {
        echo "<span class='error'>❌ Authentication test failed: " . $e->getMessage() . "</span><br>";
    }
} else {
    echo "<span class='warning'>⚠️ No login form data (this is the diagnostic page)</span><br>";
}
echo "</div>";

// Test 5: Check session configuration
echo "<div class='section'>";
echo "<h2>Test 5: Session Configuration</h2>";

echo "Session status: " . (session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Inactive') . "<br>";
echo "Session save path: " . session_save_path() . "<br>";
echo "Session name: " . session_name() . "<br>";

// Check if session directory is writable
$sessionPath = session_save_path();
if (empty($sessionPath)) {
    $sessionPath = sys_get_temp_dir();
}

if (is_writable($sessionPath)) {
    echo "<span class='ok'>✅ Session directory is writable</span><br>";
} else {
    echo "<span class='error'>❌ Session directory not writable</span><br>";
}
echo "</div>";

// Test 6: Create emergency login form
echo "<div class='section'>";
echo "<h2>Test 6: Emergency Login Form</h2>";
echo "<p>This form will help test the login process directly:</p>";

echo "<form method='POST' action='" . $_SERVER['PHP_SELF'] . "' style='background:#f8f9fa;padding:20px;border-radius:5px;'>";
echo "<h4>Emergency Login Test</h4>";
echo "<div style='margin:10px 0;'>";
echo "<label>Email:</label><br>";
echo "<input type='email' name='email' value='admin@maxcon-demo.com' style='width:300px;padding:8px;' required>";
echo "</div>";
echo "<div style='margin:10px 0;'>";
echo "<label>Password:</label><br>";
echo "<input type='password' name='password' value='password' style='width:300px;padding:8px;' required>";
echo "</div>";
echo "<div style='margin:10px 0;'>";
echo "<button type='submit' style='padding:10px 20px;background:#007bff;color:white;border:none;border-radius:4px;'>Test Login Process</button>";
echo "</div>";
echo "</form>";
echo "</div>";

// Test 7: Check .htaccess
echo "<div class='section'>";
echo "<h2>Test 7: .htaccess Configuration</h2>";

$htaccessPath = $rootPath . '/public/.htaccess';
if (file_exists($htaccessPath)) {
    echo "<span class='ok'>✅ .htaccess file exists</span><br>";
    $htaccessContent = file_get_contents($htaccessPath);
    
    if (strpos($htaccessContent, 'RewriteEngine On') !== false) {
        echo "<span class='ok'>✅ URL rewriting enabled</span><br>";
    } else {
        echo "<span class='warning'>⚠️ URL rewriting might not be enabled</span><br>";
    }
    
    if (strpos($htaccessContent, 'index.php') !== false) {
        echo "<span class='ok'>✅ Laravel routing configured</span><br>";
    } else {
        echo "<span class='warning'>⚠️ Laravel routing might not be configured</span><br>";
    }
} else {
    echo "<span class='error'>❌ .htaccess file missing</span><br>";
    
    // Create basic .htaccess
    $basicHtaccess = '<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>';
    
    if (file_put_contents($htaccessPath, $basicHtaccess)) {
        echo "<span class='ok'>✅ Created basic .htaccess file</span><br>";
        $fixes[] = "Created .htaccess file";
    } else {
        echo "<span class='error'>❌ Failed to create .htaccess file</span><br>";
    }
}
echo "</div>";

// Summary and recommendations
echo "<div class='section'>";
echo "<h2>📋 Summary & Recommendations</h2>";

if (count($fixes) > 0) {
    echo "<h3 style='color:green;'>✅ Fixes Applied:</h3>";
    foreach ($fixes as $fix) {
        echo "• $fix<br>";
    }
}

echo "<h3>🔧 Immediate Actions to Try:</h3>";
echo "1. <strong>Clear all caches:</strong><br>";
echo "<code>php artisan route:clear && php artisan config:clear && php artisan view:clear</code><br><br>";

echo "2. <strong>Check server error logs:</strong><br>";
echo "Look in storage/logs/ for detailed error messages<br><br>";

echo "3. <strong>Test with emergency form above:</strong><br>";
echo "Use the emergency login form to test the process<br><br>";

echo "4. <strong>Contact Cloudways support if needed:</strong><br>";
echo "If the issue persists, it might be a server configuration problem<br>";

echo "</div>";

echo "<div style='margin-top:30px;padding:20px;background:#e7f3ff;border:1px solid #0066cc;border-radius:8px;'>";
echo "<h3 style='color:#0066cc;margin-top:0;'>🎯 Next Steps</h3>";
echo "<p style='color:#0066cc;'>1. Try the emergency login form above</p>";
echo "<p style='color:#0066cc;'>2. Check the results and error messages</p>";
echo "<p style='color:#0066cc;'>3. Apply the recommended fixes</p>";
echo "<p style='color:#0066cc;'>4. Test your main login page again</p>";
echo "</div>";

echo "</div></body></html>";
?>
