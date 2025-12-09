#!/bin/bash

# Laravel Warehouse Inventory Docker Setup Script

set -e  # Exit on error

echo "🚀 Starting Laravel Product Warehouse Inventory Setup..."

# Check if .env exists
if [ ! -f .env ]; then
    echo "📝 Creating .env file from .env.example..."
    cp .env.example .env

    # Update database configuration for Docker
    sed -i.bak 's/DB_HOST=127.0.0.1/DB_HOST=db/' .env
    sed -i.bak 's/DB_DATABASE=laravel/DB_DATABASE=lv_warehouse_inventory_ms/' .env
    sed -i.bak 's/DB_USERNAME=root/DB_USERNAME=warehouse_user/' .env
    sed -i.bak 's/DB_PASSWORD=/DB_PASSWORD=secret/' .env

    # Update cache and session drivers
    sed -i.bak 's/CACHE_STORE=database/CACHE_STORE=redis/' .env
    sed -i.bak 's/CACHE_DRIVER=file/CACHE_DRIVER=redis/' .env
    sed -i.bak 's/SESSION_DRIVER=database/SESSION_DRIVER=redis/' .env
    sed -i.bak 's/SESSION_DRIVER=file/SESSION_DRIVER=redis/' .env
    sed -i.bak 's/QUEUE_CONNECTION=database/QUEUE_CONNECTION=redis/' .env
    sed -i.bak 's/QUEUE_CONNECTION=sync/QUEUE_CONNECTION=redis/' .env

    # Add Redis configuration if not present
    if ! grep -q "REDIS_HOST" .env; then
        echo "" >> .env
        echo "REDIS_HOST=redis" >> .env
        echo "REDIS_PASSWORD=null" >> .env
        echo "REDIS_PORT=6379" >> .env
    else
        sed -i.bak 's/REDIS_HOST=127.0.0.1/REDIS_HOST=redis/' .env
    fi

    # Remove backup files
    rm -f .env.bak

    echo "✅ .env file created and configured for Docker"
else
    echo "ℹ️  .env file already exists"
fi

# Stop any running containers
echo "🛑 Stopping any existing containers..."
docker-compose down -v || true

# Build and start containers
echo "🐳 Building Docker containers..."
docker-compose build --no-cache

echo "🚀 Starting Docker containers..."
docker-compose up -d

echo "⏳ Waiting for database to be ready..."
sleep 15

# Wait for database to be healthy
echo "🔍 Checking database health..."
max_attempts=30
attempt=0
until docker-compose exec -T db mysqladmin ping -h localhost -uroot -proot &> /dev/null || [ $attempt -eq $max_attempts ]; do
    attempt=$((attempt+1))
    echo "Waiting for database... attempt $attempt/$max_attempts"
    sleep 2
done

if [ $attempt -eq $max_attempts ]; then
    echo "❌ Database failed to become healthy"
    docker-compose logs db
    exit 1
fi

echo "✅ Database is ready"

# Install composer dependencies
echo "📦 Installing Composer dependencies..."
docker-compose exec -T app composer install --no-interaction --prefer-dist --optimize-autoloader || {
    echo "❌ Composer install failed"
    docker-compose logs app
    exit 1
}

# Generate application key
echo "🔑 Generating application key..."
docker-compose exec -T app php artisan key:generate --force

# Run migrations
echo "🗄️ Running database migrations..."
docker-compose exec -T app php artisan migrate --force || {
    echo "⚠️  Migrations failed, but continuing..."
}

# Run seeders
echo "🌱 Running database seeders..."
docker-compose exec -T app php artisan db:seed --force || {
    echo "⚠️  Seeders failed, but continuing..."
}

# Clear and cache config
echo "🔧 Optimizing Laravel..."
docker-compose exec -T app php artisan config:clear
docker-compose exec -T app php artisan cache:clear
docker-compose exec -T app php artisan route:clear
docker-compose exec -T app php artisan view:clear

# Set permissions
echo "🔒 Setting permissions..."
docker-compose exec -T app chown -R www-data:www-data /var/www/html/storage || true
docker-compose exec -T app chown -R www-data:www-data /var/www/html/bootstrap/cache || true
docker-compose exec -T app chmod -R 775 /var/www/html/storage || true
docker-compose exec -T app chmod -R 775 /var/www/html/bootstrap/cache || true

echo ""
echo "✅ Setup complete!"
echo ""
echo "📱 Application URL: http://localhost:8080"
echo "🗄️ phpMyAdmin URL: http://localhost:8081"
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
