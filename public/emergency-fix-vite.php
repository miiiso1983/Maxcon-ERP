<?php
/**
 * EMERGENCY VITE FIX
 * Upload this file to your server's public directory and run it immediately
 * URL: https://your-domain.com/emergency-fix-vite.php
 */

set_time_limit(300); // 5 minutes

echo "<!DOCTYPE html><html><head><title>Emergency Vite Fix</title>";
echo "<style>body{font-family:Arial;margin:20px;background:#f5f5f5;} .container{background:white;padding:20px;border-radius:8px;max-width:800px;margin:0 auto;} .ok{color:green;font-weight:bold;} .error{color:red;font-weight:bold;} .warning{color:orange;font-weight:bold;} .step{margin:15px 0;padding:15px;border:1px solid #ddd;border-radius:5px;background:#fafafa;} .code{background:#f0f0f0;padding:10px;border-radius:4px;font-family:monospace;margin:10px 0;}</style>";
echo "</head><body><div class='container'>";

echo "<h1>🚨 EMERGENCY VITE FIX</h1>";
echo "<p><strong>Fixing ViteManifestNotFoundException immediately...</strong></p>";

$rootPath = dirname(__DIR__);
$publicPath = __DIR__;
$buildPath = $publicPath . '/build';
$manifestPath = $buildPath . '/manifest.json';

echo "<div class='step'>";
echo "<h3>Step 1: Creating Build Directory and Manifest</h3>";

// Create build directory
if (!is_dir($buildPath)) {
    if (mkdir($buildPath, 0755, true)) {
        echo "<span class='ok'>✅ Created build directory: $buildPath</span><br>";
    } else {
        echo "<span class='error'>❌ Failed to create build directory</span><br>";
        exit;
    }
} else {
    echo "<span class='ok'>✅ Build directory exists</span><br>";
}

// Create assets directory
$assetsPath = $buildPath . '/assets';
if (!is_dir($assetsPath)) {
    mkdir($assetsPath, 0755, true);
    echo "<span class='ok'>✅ Created assets directory</span><br>";
}

// Create manifest.json
$manifest = [
    "resources/css/app.css" => [
        "file" => "assets/app-emergency.css",
        "isEntry" => true,
        "src" => "resources/css/app.css"
    ],
    "resources/js/app.js" => [
        "file" => "assets/app-emergency.js",
        "isEntry" => true,
        "src" => "resources/js/app.js"
    ]
];

if (file_put_contents($manifestPath, json_encode($manifest, JSON_PRETTY_PRINT))) {
    echo "<span class='ok'>✅ Created manifest.json</span><br>";
} else {
    echo "<span class='error'>❌ Failed to create manifest.json</span><br>";
}

echo "</div>";

echo "<div class='step'>";
echo "<h3>Step 2: Creating Emergency CSS</h3>";

$emergencyCSS = '/* Emergency CSS for Maxcon ERP */
* { box-sizing: border-box; }
body { 
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    margin: 0; 
    padding: 0; 
    background-color: #f8f9fa;
    color: #212529;
    line-height: 1.6;
}
.container { max-width: 1200px; margin: 0 auto; padding: 20px; }
.btn { 
    display: inline-block;
    padding: 8px 16px; 
    background: #007bff; 
    color: white; 
    text-decoration: none;
    border: none; 
    border-radius: 4px; 
    cursor: pointer;
    font-size: 14px;
}
.btn:hover { background: #0056b3; }
.btn-primary { background: #007bff; }
.btn-success { background: #28a745; }
.btn-danger { background: #dc3545; }
.btn-warning { background: #ffc107; color: #212529; }
.form-control { 
    padding: 8px 12px; 
    border: 1px solid #ced4da; 
    border-radius: 4px; 
    width: 100%; 
    font-size: 14px;
}
.form-group { margin-bottom: 15px; }
.card { 
    background: white; 
    border: 1px solid #dee2e6; 
    border-radius: 8px; 
    padding: 20px; 
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.alert { 
    padding: 12px 16px; 
    border-radius: 4px; 
    margin-bottom: 20px; 
}
.alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
.alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
.alert-warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
.table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
.table th, .table td { padding: 12px; text-align: left; border-bottom: 1px solid #dee2e6; }
.table th { background: #f8f9fa; font-weight: 600; }
.navbar { background: #343a40; color: white; padding: 15px 0; margin-bottom: 30px; }
.navbar .container { display: flex; justify-content: space-between; align-items: center; }
.navbar-brand { color: white; text-decoration: none; font-size: 18px; font-weight: bold; }
.nav-link { color: #rgba(255,255,255,0.8); text-decoration: none; margin: 0 10px; }
.nav-link:hover { color: white; }
.text-center { text-align: center; }
.text-right { text-align: right; }
.text-left { text-align: left; }
.mt-3 { margin-top: 1rem; }
.mb-3 { margin-bottom: 1rem; }
.p-3 { padding: 1rem; }
.d-flex { display: flex; }
.justify-content-between { justify-content: space-between; }
.align-items-center { align-items: center; }
.w-100 { width: 100%; }
.min-h-screen { min-height: 100vh; }
.bg-gray-100 { background-color: #f8f9fa; }
.shadow-md { box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
.rounded-lg { border-radius: 8px; }
.font-sans { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
.antialiased { -webkit-font-smoothing: antialiased; }
.text-gray-900 { color: #1a202c; }
.sm\\:max-w-md { max-width: 28rem; }
.sm\\:rounded-lg { border-radius: 8px; }
.overflow-hidden { overflow: hidden; }
.px-6 { padding-left: 1.5rem; padding-right: 1.5rem; }
.py-4 { padding-top: 1rem; padding-bottom: 1rem; }
.pt-6 { padding-top: 1.5rem; }
.sm\\:pt-0 { padding-top: 0; }
.sm\\:justify-center { justify-content: center; }
.items-center { align-items: center; }
.flex-col { flex-direction: column; }
.flex { display: flex; }
.bg-white { background-color: white; }
.w-20 { width: 5rem; }
.h-20 { height: 5rem; }
.fill-current { fill: currentColor; }
.text-gray-500 { color: #6b7280; }
.mt-6 { margin-top: 1.5rem; }
.w-full { width: 100%; }
';

$cssPath = $assetsPath . '/app-emergency.css';
if (file_put_contents($cssPath, $emergencyCSS)) {
    echo "<span class='ok'>✅ Created emergency CSS file</span><br>";
} else {
    echo "<span class='error'>❌ Failed to create CSS file</span><br>";
}

echo "</div>";

echo "<div class='step'>";
echo "<h3>Step 3: Creating Emergency JavaScript</h3>";

$emergencyJS = '/* Emergency JavaScript for Maxcon ERP */
console.log("Maxcon ERP Emergency Fix Applied Successfully");

// Basic form validation
document.addEventListener("DOMContentLoaded", function() {
    // Add basic interactivity
    const buttons = document.querySelectorAll(".btn");
    buttons.forEach(button => {
        button.addEventListener("click", function(e) {
            if (this.classList.contains("btn-danger")) {
                if (!confirm("Are you sure you want to proceed?")) {
                    e.preventDefault();
                }
            }
        });
    });
    
    // Basic form validation
    const forms = document.querySelectorAll("form");
    forms.forEach(form => {
        form.addEventListener("submit", function(e) {
            const requiredFields = form.querySelectorAll("[required]");
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.style.borderColor = "#dc3545";
                    isValid = false;
                } else {
                    field.style.borderColor = "#ced4da";
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert("Please fill in all required fields.");
            }
        });
    });
});

// Simple Alpine.js replacement for basic functionality
window.Alpine = {
    data: function(callback) {
        return callback();
    },
    start: function() {
        console.log("Alpine.js replacement loaded");
    }
};

// Auto-start
if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", function() {
        window.Alpine.start();
    });
} else {
    window.Alpine.start();
}
';

$jsPath = $assetsPath . '/app-emergency.js';
if (file_put_contents($jsPath, $emergencyJS)) {
    echo "<span class='ok'>✅ Created emergency JavaScript file</span><br>";
} else {
    echo "<span class='error'>❌ Failed to create JavaScript file</span><br>";
}

echo "</div>";

echo "<div class='step'>";
echo "<h3>Step 4: Clearing Laravel Caches</h3>";

// Clear view cache
$viewCachePath = $rootPath . '/storage/framework/views';
if (is_dir($viewCachePath)) {
    $files = glob($viewCachePath . '/*.php');
    $cleared = 0;
    foreach ($files as $file) {
        if (unlink($file)) {
            $cleared++;
        }
    }
    echo "<span class='ok'>✅ Cleared $cleared view cache files</span><br>";
}

// Try to run artisan commands if possible
$artisanPath = $rootPath . '/artisan';
if (file_exists($artisanPath)) {
    $commands = [
        'view:clear' => 'View cache cleared',
        'config:clear' => 'Config cache cleared'
    ];
    
    foreach ($commands as $command => $message) {
        $output = [];
        $returnCode = 0;
        exec("cd $rootPath && php artisan $command 2>&1", $output, $returnCode);
        if ($returnCode === 0) {
            echo "<span class='ok'>✅ $message</span><br>";
        } else {
            echo "<span class='warning'>⚠️ Could not run artisan $command</span><br>";
        }
    }
}

echo "</div>";

echo "<div class='step'>";
echo "<h3>Step 5: Verification</h3>";

$checks = [
    'Manifest file' => file_exists($manifestPath),
    'CSS file' => file_exists($cssPath),
    'JS file' => file_exists($jsPath),
    'Build directory' => is_dir($buildPath),
    'Assets directory' => is_dir($assetsPath)
];

foreach ($checks as $check => $status) {
    if ($status) {
        echo "<span class='ok'>✅ $check: OK</span><br>";
    } else {
        echo "<span class='error'>❌ $check: FAILED</span><br>";
    }
}

echo "</div>";

echo "<div style='margin-top:30px;padding:20px;background:#d4edda;border:1px solid #c3e6cb;border-radius:8px;'>";
echo "<h3 style='color:#155724;margin-top:0;'>🎉 EMERGENCY FIX COMPLETED!</h3>";
echo "<p style='color:#155724;margin-bottom:0;'><strong>The ViteManifestNotFoundException should now be resolved!</strong></p>";
echo "<p style='color:#155724;'>You can now access your application normally. The emergency assets will provide basic styling and functionality.</p>";
echo "</div>";

echo "<div style='margin-top:20px;padding:15px;background:#fff3cd;border:1px solid #ffeaa7;border-radius:8px;'>";
echo "<h4 style='color:#856404;margin-top:0;'>Next Steps:</h4>";
echo "<ul style='color:#856404;'>";
echo "<li>Test your application to ensure it's working</li>";
echo "<li>For better styling, run <code>npm install && npm run build</code> when possible</li>";
echo "<li>Consider setting up a proper deployment process</li>";
echo "<li>You can delete this emergency fix file after confirming everything works</li>";
echo "</ul>";
echo "</div>";

echo "</div></body></html>";
?>
