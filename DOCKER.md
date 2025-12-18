# Docker Setup for Laravel Warehouse Inventory Management System

This project is fully configured to run with Docker, providing an isolated and consistent development environment.

## 🚀 Quick Start

### Prerequisites

- Docker Engine 20.10+
- Docker Compose 2.0+

### Setup Instructions

1. **Clone the repository:**

    ```bash
    git clone https://github.com/fooysaal/lv-product-inventory-ms.git
    cd lv-product-inventory-ms
    ```

2. **Create the environment file:**

    ```bash
    cp .env.example .env
    ```

    Or create `.env` manually with the following content:

    ```env
    APP_NAME=Laravel
    APP_ENV=local
    APP_KEY=
    APP_DEBUG=true
    APP_URL=http://localhost:8080

    DB_CONNECTION=mysql
    DB_HOST=db
    DB_PORT=3306
    DB_DATABASE=lv_warehouse_inventory_ms
    DB_USERNAME=warehouse_user
    DB_PASSWORD=secret

    CACHE_STORE=database
    SESSION_DRIVER=database
    QUEUE_CONNECTION=database

    REDIS_HOST=redis
    REDIS_PORT=6379
    ```

3. **Build and start Docker containers:**

    ```bash
    # Start all services (database will take ~30-60 seconds to initialize on first run)
    docker compose up -d
    ```

    Wait for the database to become healthy. You can check with:

    ```bash
    docker ps
    ```

    Look for `(healthy)` status on `lv-product-inventory-db`.

4. **Fix permissions and initialize Laravel:**

    ```bash
    # Fix storage and cache permissions
    docker compose exec app chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
    docker compose exec app chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

    # Fix .env permissions (needed for key:generate)
    docker compose exec app chown www-data:www-data /var/www/html/.env

    # Generate application key
    docker compose exec app php artisan key:generate

    # Run database migrations
    docker compose exec app php artisan migrate --force

    # Seed the database
    docker compose exec app php artisan db:seed --force
    ```

5. **Access the application:**
    - **Application**: http://localhost:8080
    - **phpMyAdmin**: http://localhost:8081

## 📦 Docker Services

The application runs with the following services:

| Service    | Container Name                     | Port | Description                      |
| ---------- | ---------------------------------- | ---- | -------------------------------- |
| app        | lv-product-inventory-app           | 8080 | Laravel app (PHP 8.4 + Nginx)    |
| db         | lv-product-inventory-db            | 3306 | MySQL 8.0 database               |
| redis      | lv-product-inventory-redis         | 6379 | Redis for caching                |
| phpmyadmin | lv-product-inventory-ms-phpmyadmin | 8081 | Database management interface    |

## 🗄️ Database Credentials

**Application Database:**

- Host: `db` (from within containers) or `localhost` (from host)
- Database: `lv_warehouse_inventory_ms`
- Username: `warehouse_user`
- Password: `secret`

**Root Access (phpMyAdmin):**

- Username: `root`
- Password: `root`

## 🛠️ Useful Commands

### Container Management

```bash
# Start all containers
docker compose up -d

# Stop all containers
docker compose down

# Stop and remove volumes (fresh start)
docker compose down -v

# Restart containers
docker compose restart

# View logs
docker compose logs -f

# View specific service logs
docker compose logs -f app
```

### Application Commands

```bash
# Access container shell
docker compose exec app sh

# Run artisan commands
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed
docker compose exec app php artisan cache:clear
docker compose exec app php artisan config:clear

# Run tests
docker compose exec app php artisan test
```

### Database Commands

```bash
# Access MySQL CLI
docker compose exec db mysql -u root -proot

# Backup database
docker compose exec db mysqldump -u root -proot lv_warehouse_inventory_ms > backup.sql

# Restore database
docker compose exec -T db mysql -u root -proot lv_warehouse_inventory_ms < backup.sql
```

## 🔧 Configuration

### Environment Variables

The `.env` file is bind-mounted into the container. Key settings for Docker:

- `DB_HOST=db` — Points to the MySQL container
- `REDIS_HOST=redis` — Points to the Redis container

### Custom Configuration

You can customize the setup by modifying:

- `docker-compose.yml` — Service configurations
- `Dockerfile` — Application container setup
- `docker/nginx/nginx.conf` — Nginx web server settings
- `docker/supervisor/supervisord.conf` — Process management

## 📁 Project Structure

```
├── docker/
│   ├── nginx/
│   │   └── nginx.conf          # Nginx configuration
│   └── supervisor/
│       └── supervisord.conf    # Process supervisor config
├── .dockerignore               # Files to exclude from build
├── Dockerfile                  # Application container definition
├── docker-compose.yml          # Multi-container orchestration
└── DOCKER.md                   # This file
```

## 🐛 Troubleshooting

### Database Container Unhealthy on First Run

MySQL takes time to initialize on first run. Wait 30-60 seconds and try again:

```bash
# Check container health
docker ps

# If db shows "unhealthy", check logs
docker logs lv-product-inventory-db
```

### Permission Denied Errors

If you see permission errors for storage or .env:

```bash
docker compose exec app chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
docker compose exec app chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
docker compose exec app chown www-data:www-data /var/www/html/.env
```

### Port Already in Use

If ports 8080 or 8081 are already in use, modify the port mappings in `docker-compose.yml`:

```yaml
ports:
    - "9080:80" # Change 8080 to 9080
```

### Clear All Data and Start Fresh

```bash
docker compose down -v
docker compose up -d
# Then run the initialization commands again (step 4)
```

### .env File Not Found in Container

The `.env` file is mounted from host. Ensure it exists:

```bash
ls -la .env
```

If missing, create it from `.env.example` or manually.

## 🔄 Updating the Application

```bash
# Pull latest changes
git pull origin main

# Rebuild containers (if Dockerfile changed)
docker compose build --no-cache app

# Restart services
docker compose up -d

# Run migrations
docker compose exec app php artisan migrate --force
```

## 🛡️ Production Considerations

For production deployment:

1. Update `.env` with production settings
2. Set `APP_ENV=production` and `APP_DEBUG=false`
3. Use strong database credentials
4. Configure SSL/TLS certificates
5. Set up proper volume backups
6. Consider using Docker Swarm or Kubernetes for orchestration
7. Implement proper logging and monitoring

## 📝 Notes

- The application runs with **PHP 8.4 FPM** and Nginx
- Supervisor manages PHP-FPM and Nginx processes
- The `.env` file is bind-mounted from host for easy configuration
- Storage and cache directories require `www-data` ownership

## 🤝 Support

For issues or questions:

1. Check the logs: `docker compose logs -f`
2. Verify all services are running: `docker ps`
3. Review the configuration files in the `docker/` directory
