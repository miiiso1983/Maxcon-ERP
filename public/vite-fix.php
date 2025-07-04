<?php
/**
 * Vite Manifest Fix Tool
 * Run this script to resolve Vite manifest not found errors
 */

echo "<!DOCTYPE html><html><head><title>Vite Manifest Fix</title>";
echo "<style>body{font-family:Arial;margin:20px;} .ok{color:green;} .error{color:red;} .warning{color:orange;} .step{margin:15px 0;padding:10px;border:1px solid #ddd;}</style>";
echo "</head><body>";

echo "<h1>🔧 Vite Manifest Fix Tool</h1>";
echo "<p>Fixing Vite manifest not found errors...</p>";

$fixes = [];

// Step 1: Check if we're in the right location
echo "<div class='step'>";
echo "<h3>Step 1: Checking Environment</h3>";

$publicPath = __DIR__;
$rootPath = dirname($publicPath);
$buildPath = $publicPath . '/build';
$manifestPath = $buildPath . '/manifest.json';

echo "Public path: $publicPath<br>";
echo "Root path: $rootPath<br>";
echo "Build path: $buildPath<br>";

if (file_exists($rootPath . '/artisan')) {
    echo "<span class='ok'>✅ Laravel application detected</span><br>";
} else {
    echo "<span class='error'>❌ Laravel application not found</span><br>";
}
echo "</div>";

// Step 2: Check Vite manifest
echo "<div class='step'>";
echo "<h3>Step 2: Checking Vite Manifest</h3>";

if (file_exists($manifestPath)) {
    echo "<span class='ok'>✅ Vite manifest found at: $manifestPath</span><br>";
    $manifest = json_decode(file_get_contents($manifestPath), true);
    if ($manifest) {
        echo "<span class='ok'>✅ Manifest is valid JSON</span><br>";
        echo "Manifest entries: " . count($manifest) . "<br>";
    } else {
        echo "<span class='error'>❌ Manifest is corrupted</span><br>";
    }
} else {
    echo "<span class='warning'>⚠️ Vite manifest not found</span><br>";
    echo "Expected location: $manifestPath<br>";
}
echo "</div>";

// Step 3: Check build directory
echo "<div class='step'>";
echo "<h3>Step 3: Checking Build Directory</h3>";

if (is_dir($buildPath)) {
    echo "<span class='ok'>✅ Build directory exists</span><br>";
    $files = scandir($buildPath);
    $assetFiles = array_filter($files, function($file) {
        return !in_array($file, ['.', '..']) && !is_dir($file);
    });
    echo "Files in build directory: " . count($assetFiles) . "<br>";
    foreach ($assetFiles as $file) {
        echo "- $file<br>";
    }
} else {
    echo "<span class='warning'>⚠️ Build directory not found</span><br>";
    echo "Creating build directory...<br>";
    if (mkdir($buildPath, 0755, true)) {
        echo "<span class='ok'>✅ Build directory created</span><br>";
        $fixes[] = "Created build directory";
    } else {
        echo "<span class='error'>❌ Failed to create build directory</span><br>";
    }
}
echo "</div>";

// Step 4: Create minimal manifest if missing
echo "<div class='step'>";
echo "<h3>Step 4: Creating Minimal Manifest</h3>";

if (!file_exists($manifestPath)) {
    $minimalManifest = [
        "resources/css/app.css" => [
            "file" => "assets/app.css",
            "isEntry" => true,
            "src" => "resources/css/app.css"
        ],
        "resources/js/app.js" => [
            "file" => "assets/app.js",
            "isEntry" => true,
            "src" => "resources/js/app.js"
        ]
    ];
    
    if (file_put_contents($manifestPath, json_encode($minimalManifest, JSON_PRETTY_PRINT))) {
        echo "<span class='ok'>✅ Created minimal manifest.json</span><br>";
        $fixes[] = "Created minimal Vite manifest";
    } else {
        echo "<span class='error'>❌ Failed to create manifest.json</span><br>";
    }
} else {
    echo "<span class='ok'>✅ Manifest already exists</span><br>";
}
echo "</div>";

// Step 5: Create minimal CSS file
echo "<div class='step'>";
echo "<h3>Step 5: Creating Minimal Assets</h3>";

$assetsPath = $buildPath . '/assets';
if (!is_dir($assetsPath)) {
    mkdir($assetsPath, 0755, true);
}

$cssPath = $assetsPath . '/app.css';
if (!file_exists($cssPath)) {
    $minimalCSS = "/* Minimal CSS for Maxcon ERP */\nbody { font-family: Arial, sans-serif; margin: 0; padding: 20px; }\n.container { max-width: 1200px; margin: 0 auto; }";
    if (file_put_contents($cssPath, $minimalCSS)) {
        echo "<span class='ok'>✅ Created minimal app.css</span><br>";
        $fixes[] = "Created minimal CSS file";
    }
}

$jsPath = $assetsPath . '/app.js';
if (!file_exists($jsPath)) {
    $minimalJS = "/* Minimal JS for Maxcon ERP */\nconsole.log('Maxcon ERP loaded');";
    if (file_put_contents($jsPath, $minimalJS)) {
        echo "<span class='ok'>✅ Created minimal app.js</span><br>";
        $fixes[] = "Created minimal JS file";
    }
}
echo "</div>";

// Step 6: Check Laravel cache
echo "<div class='step'>";
echo "<h3>Step 6: Checking Laravel Cache</h3>";

$viewCachePath = $rootPath . '/storage/framework/views';
if (is_dir($viewCachePath)) {
    $cacheFiles = glob($viewCachePath . '/*.php');
    if (count($cacheFiles) > 0) {
        echo "<span class='warning'>⚠️ Found " . count($cacheFiles) . " cached view files</span><br>";
        echo "Consider clearing view cache: <code>php artisan view:clear</code><br>";
    } else {
        echo "<span class='ok'>✅ No cached view files found</span><br>";
    }
}
echo "</div>";

// Step 7: Provide instructions
echo "<div class='step'>";
echo "<h3>Step 7: Next Steps</h3>";

if (count($fixes) > 0) {
    echo "<h4>Applied Fixes:</h4>";
    foreach ($fixes as $fix) {
        echo "<span class='ok'>✅ $fix</span><br>";
    }
    echo "<br>";
}

echo "<h4>Recommended Actions:</h4>";
echo "1. <strong>Build production assets:</strong><br>";
echo "   <code>npm install && npm run build</code><br><br>";

echo "2. <strong>Clear Laravel caches:</strong><br>";
echo "   <code>php artisan view:clear</code><br>";
echo "   <code>php artisan config:clear</code><br><br>";

echo "3. <strong>If you don't have Node.js/NPM:</strong><br>";
echo "   The application will use CDN fallbacks automatically<br><br>";

echo "4. <strong>For permanent solution:</strong><br>";
echo "   Set up a proper build process in your deployment pipeline<br>";

echo "</div>";

echo "<div style='margin-top:30px;padding:15px;background:#f0f8ff;border:1px solid #0066cc;'>";
echo "<h3>🎯 Quick Fix Applied!</h3>";
echo "<p>The application should now work without Vite manifest errors.</p>";
echo "<p>If you still see errors, the layout files have been updated with CDN fallbacks.</p>";
echo "</div>";

echo "</body></html>";
?>
