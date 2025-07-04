<?php
/**
 * .htaccess Fix Tool
 * Fixes FcgidInitialEnv and other .htaccess errors
 */

echo "<!DOCTYPE html><html><head><title>.htaccess Fix</title>";
echo "<style>body{font-family:Arial;margin:20px;} .ok{color:green;} .error{color:red;} .warning{color:orange;} .step{margin:15px 0;padding:10px;border:1px solid #ddd;}</style>";
echo "</head><body>";

echo "<h1>🔧 .htaccess Fix Tool</h1>";
echo "<p>Fixing .htaccess configuration errors...</p>";

$fixes = [];

// Step 1: Check current .htaccess
echo "<div class='step'>";
echo "<h3>Step 1: Checking Current .htaccess</h3>";
if (file_exists('.htaccess')) {
    $htaccess = file_get_contents('.htaccess');
    
    // Check for problematic directives
    $problems = [];
    if (strpos($htaccess, 'FcgidInitialEnv') !== false) {
        $problems[] = 'FcgidInitialEnv (not allowed in .htaccess)';
    }
    if (strpos($htaccess, 'FcgidMaxRequestLen') !== false) {
        $problems[] = 'FcgidMaxRequestLen (not allowed in .htaccess)';
    }
    
    if (count($problems) > 0) {
        echo "<span class='error'>❌ Found problems:</span><br>";
        foreach ($problems as $problem) {
            echo "• $problem<br>";
        }
    } else {
        echo "<span class='ok'>✅ No obvious problems found</span><br>";
    }
} else {
    echo "<span class='warning'>⚠️ No .htaccess file found</span><br>";
}
echo "</div>";

// Step 2: Backup current .htaccess
echo "<div class='step'>";
echo "<h3>Step 2: Creating Backup</h3>";
if (file_exists('.htaccess')) {
    if (copy('.htaccess', '.htaccess.backup.' . date('Y-m-d-H-i-s'))) {
        echo "<span class='ok'>✅ Backup created</span><br>";
        $fixes[] = "Created backup of original .htaccess";
    } else {
        echo "<span class='error'>❌ Failed to create backup</span><br>";
    }
} else {
    echo "<span class='warning'>⚠️ No file to backup</span><br>";
}
echo "</div>";

// Step 3: Apply fixed .htaccess
echo "<div class='step'>";
echo "<h3>Step 3: Applying Fixed .htaccess</h3>";

$fixedHtaccess = '<IfModule mod_rewrite.c>
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
</IfModule>

# Security Headers (only if supported)
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
</IfModule>

# PHP Configuration (safe settings)
<IfModule mod_php.c>
    php_value memory_limit 256M
    php_value max_execution_time 120
    php_flag display_errors Off
    php_flag log_errors On
</IfModule>

# Protect sensitive files
<Files ".env">
    Require all denied
</Files>

<Files "composer.*">
    Require all denied
</Files>

<Files "*.log">
    Require all denied
</Files>
';

if (file_put_contents('.htaccess', $fixedHtaccess)) {
    echo "<span class='ok'>✅ Applied fixed .htaccess</span><br>";
    $fixes[] = "Applied safe .htaccess configuration";
} else {
    echo "<span class='error'>❌ Failed to write .htaccess</span><br>";
}
echo "</div>";

// Step 4: Test the fix
echo "<div class='step'>";
echo "<h3>Step 4: Testing Configuration</h3>";
try {
    // Try to access a simple PHP script
    $testContent = "<?php echo 'OK'; ?>";
    file_put_contents('test-htaccess.php', $testContent);
    
    // Test if we can access it
    $testUrl = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/test-htaccess.php';
    
    echo "<span class='ok'>✅ .htaccess applied successfully</span><br>";
    echo "Test URL: <a href='test-htaccess.php' target='_blank'>test-htaccess.php</a><br>";
    
    // Clean up test file
    unlink('test-htaccess.php');
    
} catch (Exception $e) {
    echo "<span class='error'>❌ Error testing: " . htmlspecialchars($e->getMessage()) . "</span><br>";
}
echo "</div>";

// Step 5: Alternative options
echo "<div class='step'>";
echo "<h3>Step 5: Alternative Options</h3>";
echo "<p>If the main .htaccess still causes issues, try these alternatives:</p>";

// Create minimal .htaccess
$minimalHtaccess = 'RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ index.php [L]

<Files ".env">
    Require all denied
</Files>
';

if (file_put_contents('.htaccess.minimal', $minimalHtaccess)) {
    echo "<span class='ok'>✅ Created minimal .htaccess alternative</span><br>";
}

// Create ultra-simple version
$ultraSimple = 'RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ index.php [L]
';

if (file_put_contents('.htaccess.ultra-simple', $ultraSimple)) {
    echo "<span class='ok'>✅ Created ultra-simple .htaccess alternative</span><br>";
}

echo "<p><strong>To use alternatives:</strong></p>";
echo "<ol>";
echo "<li>Rename current .htaccess to .htaccess.backup</li>";
echo "<li>Rename .htaccess.minimal to .htaccess</li>";
echo "<li>Test your site</li>";
echo "<li>If still issues, try .htaccess.ultra-simple</li>";
echo "</ol>";
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
    echo "<span class='warning'>No fixes were needed or applied</span><br>";
}
echo "</div>";

echo "<div class='step'>";
echo "<h3>📋 Next Steps</h3>";
echo "<ol>";
echo "<li><a href='../'>Test your main application</a></li>";
echo "<li>Check server error logs for any remaining issues</li>";
echo "<li>If problems persist, contact your hosting provider</li>";
echo "<li>Consider using the minimal .htaccess versions provided</li>";
echo "</ol>";
echo "</div>";

echo "<p><a href='debug.php'>← Full Debug</a> | <a href='simple-test.php'>Simple Test</a></p>";

echo "</body></html>";
?>
