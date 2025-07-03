#!/bin/bash

echo "🚀 Optimizing Maxcon ERP for Production..."

# Clear all caches
echo "📝 Clearing caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize for production
echo "⚡ Optimizing for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Install/update dependencies
echo "📦 Installing production dependencies..."
composer install --optimize-autoloader --no-dev

# Generate optimized autoloader
echo "🔧 Generating optimized autoloader..."
composer dump-autoload --optimize

# Set proper permissions
echo "🔒 Setting proper permissions..."
chmod -R 755 storage
chmod -R 755 bootstrap/cache
chmod 644 .env

# Create necessary directories
echo "📁 Creating necessary directories..."
mkdir -p storage/logs
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p bootstrap/cache

# Set storage permissions
echo "📂 Setting storage permissions..."
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Generate application key if not exists
if grep -q "APP_KEY=base64:" .env; then
    echo "🔑 Application key already exists"
else
    echo "🔑 Generating application key..."
    php artisan key:generate
fi

# Run migrations
echo "🗄️ Running database migrations..."
php artisan migrate --force

# Seed essential data
echo "🌱 Seeding essential data..."
php artisan db:seed --class=SuperAdminSeeder --force

# Clear and warm up caches
echo "🔥 Warming up caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create symbolic link for storage
echo "🔗 Creating storage link..."
php artisan storage:link

echo "✅ Production optimization complete!"
echo ""
echo "📋 Next steps:"
echo "1. Update your .env file with production settings"
echo "2. Set APP_DEBUG=false in .env"
echo "3. Configure your web server to point to the public directory"
echo "4. Set up SSL certificate"
echo "5. Configure backup and monitoring"
echo ""
echo "🌐 Your application should now be ready for production!"
