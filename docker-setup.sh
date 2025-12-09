#!/bin/bash

# Laravel Warehouse Inventory Docker Setup Script

echo "🚀 Starting Laravel Warehouse Inventory Setup..."

# Detect OS for sed compatibility
SED_INPLACE=(-i)
if [[ "$OSTYPE" == "darwin"* ]]; then
    SED_INPLACE=(-i '')
fi

# Check if .env exists
if [ ! -f .env ]; then
    echo "📝 Creating .env file from .env.example..."
    cp .env.example .env

    # Update database configuration for Docker
    sed "${SED_INPLACE[@]}" 's/DB_HOST=127.0.0.1/DB_HOST=db/' .env
    sed "${SED_INPLACE[@]}" 's/DB_DATABASE=lv_warehouse_inventory_ms/DB_DATABASE=lv_warehouse_inventory_ms/' .env
    sed "${SED_INPLACE[@]}" 's/DB_USERNAME=root/DB_USERNAME=warehouse_user/' .env
    sed "${SED_INPLACE[@]}" 's/DB_PASSWORD=/DB_PASSWORD=secret/' .env

    # Update cache and session drivers
    sed "${SED_INPLACE[@]}" 's/CACHE_STORE=database/CACHE_STORE=redis/' .env
    sed "${SED_INPLACE[@]}" 's/SESSION_DRIVER=database/SESSION_DRIVER=redis/' .env
    sed "${SED_INPLACE[@]}" 's/QUEUE_CONNECTION=database/QUEUE_CONNECTION=redis/' .env

    # Add Redis configuration if not present
    if ! grep -q "REDIS_HOST" .env; then
        echo "" >> .env
        echo "REDIS_HOST=redis" >> .env
        echo "REDIS_PASSWORD=null" >> .env
        echo "REDIS_PORT=6379" >> .env
    else
        sed "${SED_INPLACE[@]}" 's/REDIS_HOST=127.0.0.1/REDIS_HOST=redis/' .env
    fi

    echo "✅ .env file created and configured for Docker"
else
    echo "ℹ️  .env file already exists"
fi

# Build and start containers
echo "🐳 Building Docker containers..."
docker-compose build --no-cache

echo "🚀 Starting Docker containers..."
docker-compose up -d

echo "⏳ Waiting for database to be ready..."
sleep 10

# Install composer dependencies
echo "📦 Installing Composer dependencies..."
docker-compose exec app composer install

# Generate application key
echo "🔑 Generating application key..."
docker-compose exec app php artisan key:generate

# Run migrations
echo "📊 Running database migrations..."
docker-compose exec app php artisan migrate --force

# Run seeders
echo "🌱 Running database seeders..."
docker-compose exec app php artisan db:seed --force

# Clear and cache config
echo "🔧 Optimizing Laravel..."
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan view:clear

# Set permissions
echo "🔐 Setting permissions..."
docker-compose exec app chown -R www-data:www-data /var/www/html/storage
docker-compose exec app chown -R www-data:www-data /var/www/html/bootstrap/cache
docker-compose exec app chmod -R 775 /var/www/html/storage
docker-compose exec app chmod -R 775 /var/www/html/bootstrap/cache

echo ""
echo "✅ Setup complete!"
echo ""
echo "📍 Application URL: http://localhost:8080"
echo "📍 phpMyAdmin URL: http://localhost:8081"
echo ""
echo "Database Credentials:"
echo "  Host: localhost:3307"
echo "  Database: lv_warehouse_inventory_ms"
echo "  Username: warehouse_user"
echo "  Password: secret"
echo "  Root Password: root"
echo ""
echo "Useful commands:"
echo "  docker-compose up -d        # Start containers"
echo "  docker-compose down         # Stop containers"
echo "  docker-compose logs -f      # View logs"
echo "  docker-compose exec app bash # Access container shell"
echo ""
