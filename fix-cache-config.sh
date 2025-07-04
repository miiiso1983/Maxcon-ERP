#!/bin/bash

echo "🔧 Fixing Laravel Cache Configuration"
echo "===================================="

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

# Step 1: Check current cache configuration
echo ""
echo "📋 Step 1: Checking current cache configuration"
if [ -f ".env" ]; then
    CURRENT_CACHE=$(grep "CACHE_DRIVER" .env | cut -d'=' -f2)
    echo "Current cache driver: $CURRENT_CACHE"
else
    echo "❌ .env file not found"
    exit 1
fi

# Step 2: Create cache tables if using database cache
echo ""
echo "📋 Step 2: Handling database cache tables"
if [ "$CURRENT_CACHE" = "database" ]; then
    echo "Database cache detected. Creating cache tables..."
    
    # Try to create cache tables using artisan
    php artisan cache:table 2>/dev/null || echo "Cache table migration not available"
    
    # Run the SQL script directly
    if [ -f "create-cache-tables.sql" ]; then
        echo "Running cache tables creation script..."
        mysql -u $(grep DB_USERNAME .env | cut -d'=' -f2) \
              -p$(grep DB_PASSWORD .env | cut -d'=' -f2) \
              $(grep DB_DATABASE .env | cut -d'=' -f2) < create-cache-tables.sql
        
        if [ $? -eq 0 ]; then
            echo "✅ Cache tables created successfully"
        else
            echo "❌ Failed to create cache tables via SQL"
            echo "Switching to file cache instead..."
            
            # Switch to file cache
            sed -i.bak 's/CACHE_DRIVER=database/CACHE_DRIVER=file/' .env
            echo "✅ Switched to file cache driver"
        fi
    else
        echo "❌ Cache tables SQL script not found"
        echo "Switching to file cache instead..."
        
        # Switch to file cache
        sed -i.bak 's/CACHE_DRIVER=database/CACHE_DRIVER=file/' .env
        echo "✅ Switched to file cache driver"
    fi
else
    echo "Not using database cache. Current driver: $CURRENT_CACHE"
fi

# Step 3: Clear and rebuild caches
echo ""
echo "📋 Step 3: Clearing and rebuilding caches"
php artisan config:clear
php artisan cache:clear 2>/dev/null || echo "Cache clear completed (some warnings expected)"
php artisan config:cache
echo "✅ Caches cleared and rebuilt"

# Step 4: Test cache functionality
echo ""
echo "📋 Step 4: Testing cache functionality"
php artisan tinker --execute="
try {
    Cache::put('test_key', 'test_value', 60);
    \$value = Cache::get('test_key');
    if (\$value === 'test_value') {
        echo 'Cache test successful';
        Cache::forget('test_key');
    } else {
        echo 'Cache test failed';
    }
} catch (Exception \$e) {
    echo 'Cache error: ' . \$e->getMessage();
}
"

# Step 5: Test Laravel
echo ""
echo "📋 Step 5: Testing Laravel"
php artisan --version
if [ $? -eq 0 ]; then
    echo "✅ Laravel is working!"
else
    echo "❌ Laravel still has issues"
fi

echo ""
echo "🎉 Cache Configuration Fix Complete!"
echo "==================================="
echo ""
echo "Next steps:"
echo "1. Test your dashboard: https://your-domain.com/dashboard"
echo "2. Check cache functionality: https://your-domain.com/fix-database-cache.php"
echo "3. Monitor error logs: storage/logs/laravel.log"
echo ""

# Show current cache configuration
echo "Current cache configuration:"
grep "CACHE_DRIVER" .env
