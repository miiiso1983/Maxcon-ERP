#!/bin/bash

# Production Deployment Script for Maxcon ERP
# This script prepares the application for production deployment

echo "🚀 Starting Maxcon ERP Production Deployment..."

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo "❌ Error: artisan file not found. Please run this script from the Laravel root directory."
    exit 1
fi

# Step 1: Install/Update Composer dependencies (production)
echo "📦 Installing Composer dependencies..."
composer install --optimize-autoloader --no-dev --no-interaction --prefer-dist

# Step 2: Install/Update NPM dependencies
echo "📦 Installing NPM dependencies..."
npm ci --production=false

# Step 3: Build production assets
echo "🔨 Building production assets..."
npm run build

# Step 4: Clear all caches
echo "🧹 Clearing application caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Step 5: Optimize for production
echo "⚡ Optimizing for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Step 6: Generate application key if not exists
if grep -q "APP_KEY=$" .env 2>/dev/null; then
    echo "🔑 Generating application key..."
    php artisan key:generate --force
fi

# Step 7: Run database migrations (with confirmation)
read -p "🗄️  Do you want to run database migrations? (y/N): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    php artisan migrate --force
fi

# Step 8: Create storage symlink
echo "🔗 Creating storage symlink..."
php artisan storage:link

# Step 9: Set proper permissions
echo "🔒 Setting proper permissions..."
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage/logs
chmod -R 775 storage/framework/sessions
chmod -R 775 storage/framework/views
chmod -R 775 storage/framework/cache

# Step 10: Verify critical files exist
echo "✅ Verifying deployment..."

# Check if Vite manifest exists
if [ -f "public/build/manifest.json" ]; then
    echo "✅ Vite manifest found"
else
    echo "⚠️  Warning: Vite manifest not found, but fallback CSS/JS will be used"
fi

# Check if .env exists
if [ -f ".env" ]; then
    echo "✅ Environment file found"
else
    echo "❌ Warning: .env file not found. Please create one from .env.example"
fi

# Check if storage is writable
if [ -w "storage/logs" ]; then
    echo "✅ Storage directory is writable"
else
    echo "❌ Warning: Storage directory is not writable"
fi

echo ""
echo "🎉 Production deployment completed!"
echo ""
echo "📋 Post-deployment checklist:"
echo "   1. Verify .env configuration"
echo "   2. Test database connection"
echo "   3. Check file permissions"
echo "   4. Test application functionality"
echo "   5. Monitor error logs"
echo ""
echo "📁 Important paths:"
echo "   - Application: $(pwd)"
echo "   - Storage logs: $(pwd)/storage/logs"
echo "   - Public assets: $(pwd)/public"
echo ""
echo "🔧 If you encounter Vite manifest errors:"
echo "   - Run: npm run build"
echo "   - Or the application will use CDN fallbacks automatically"
echo ""
