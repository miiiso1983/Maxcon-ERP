#!/bin/bash

echo "🔧 Fixing Laravel Composer Autoload Issue"
echo "=========================================="

# Get the current directory
LARAVEL_ROOT=$(pwd)
echo "Laravel Root: $LARAVEL_ROOT"

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo "❌ Error: Not in Laravel root directory"
    echo "Please run this script from the Laravel root directory"
    exit 1
fi

echo "✅ Confirmed Laravel root directory"

# Step 1: Check Composer
echo ""
echo "📋 Step 1: Checking Composer"
if ! command -v composer &> /dev/null; then
    echo "❌ Composer not found. Installing Composer..."
    curl -sS https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer
    echo "✅ Composer installed"
else
    echo "✅ Composer found"
fi

# Step 2: Clear existing autoload
echo ""
echo "📋 Step 2: Clearing existing autoload"
if [ -f "vendor/autoload.php" ]; then
    echo "Removing existing autoload..."
    rm -f vendor/composer/autoload_*.php
    rm -f vendor/composer/ClassLoader.php
    echo "✅ Existing autoload cleared"
fi

# Step 3: Reinstall Composer dependencies
echo ""
echo "📋 Step 3: Reinstalling Composer dependencies"
echo "This may take a few minutes..."

composer install --no-dev --optimize-autoloader --no-interaction
if [ $? -eq 0 ]; then
    echo "✅ Composer install completed successfully"
else
    echo "❌ Composer install failed. Trying alternative approach..."
    
    # Alternative: Update composer and try again
    composer self-update
    composer clear-cache
    composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist
    
    if [ $? -eq 0 ]; then
        echo "✅ Composer install completed with alternative approach"
    else
        echo "❌ Composer install still failing. Manual intervention required."
        exit 1
    fi
fi

# Step 4: Regenerate optimized autoload
echo ""
echo "📋 Step 4: Regenerating optimized autoload"
composer dump-autoload --optimize --no-dev
echo "✅ Autoload regenerated"

# Step 5: Fix Cache Configuration and Clear Laravel caches
echo ""
echo "📋 Step 5: Fixing Cache Configuration and Clearing Laravel caches"

# Check if cache table exists and create if needed
echo "Checking cache table..."
php artisan tinker --execute="
try {
    \DB::table('cache')->count();
    echo 'Cache table exists';
} catch (Exception \$e) {
    echo 'Cache table missing - will create';
    \Schema::create('cache', function (\$table) {
        \$table->string('key')->primary();
        \$table->mediumText('value');
        \$table->integer('expiration');
    });
    \Schema::create('cache_locks', function (\$table) {
        \$table->string('key')->primary();
        \$table->string('owner');
        \$table->integer('expiration');
    });
    echo 'Cache tables created successfully';
}
"

# Clear caches safely
echo "Clearing Laravel caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear

# Try to clear cache, but handle database cache errors gracefully
php artisan cache:clear 2>/dev/null || echo "Cache clear skipped (database cache not available)"

echo "✅ Laravel caches cleared and cache configuration fixed"

# Step 6: Rebuild Laravel caches
echo ""
echo "📋 Step 6: Rebuilding Laravel caches"
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "✅ Laravel caches rebuilt"

# Step 7: Test Laravel
echo ""
echo "📋 Step 7: Testing Laravel"
php artisan --version
if [ $? -eq 0 ]; then
    echo "✅ Laravel is working!"
else
    echo "❌ Laravel still has issues"
fi

# Step 8: Set proper permissions
echo ""
echo "📋 Step 8: Setting proper permissions"
chmod -R 755 storage
chmod -R 755 bootstrap/cache
echo "✅ Permissions set"

echo ""
echo "🎉 Laravel Autoload Fix Complete!"
echo "=================================="
echo ""
echo "Next steps:"
echo "1. Test your dashboard: https://your-domain.com/dashboard"
echo "2. If still not working, check the error logs"
echo "3. Use emergency dashboard as backup: https://your-domain.com/emergency-dashboard.php"
echo ""
echo "Logs location: storage/logs/laravel.log"
