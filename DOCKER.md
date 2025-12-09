# Docker Setup for Laravel Warehouse Inventory Management System

This project is fully configured to run with Docker, providing an isolated and consistent development environment.

## 🚀 Quick Start

### Prerequisites

-   Docker Engine 20.10+
-   Docker Compose 2.0+

### Setup Instructions

1. **Make the setup script executable:**

    ```bash
    chmod +x docker-setup.sh
    ```

2. **Run the setup script:**

    ```bash
    ./docker-setup.sh
    ```

    This script will:

    - Create and configure `.env` file
    - Build Docker containers
    - Install dependencies
    - Run migrations and seeders
    - Set proper permissions

3. **Access the application:**
    - Application: http://localhost:8080
    - phpMyAdmin: http://localhost:8081

## 📦 Docker Services

The application runs with the following services:

| Service    | Container Name          | Port | Description                                             |
| ---------- | ----------------------- | ---- | ------------------------------------------------------- |
| app        | lv-warehouse-app        | 8080 | Laravel application with PHP 8.2, Nginx, and Supervisor |
| db         | lv-warehouse-db         | 3307 | MySQL 8.0 database                                      |
| redis      | lv-warehouse-redis      | 6380 | Redis for caching and sessions                          |
| phpmyadmin | lv-warehouse-phpmyadmin | 8081 | Database management interface                           |

## 🗄️ Database Credentials

**Application Database:**

-   Host: `db` (from within containers) or `localhost:3307` (from host)
-   Database: `lv_warehouse_inventory_ms`
-   Username: `warehouse_user`
-   Password: `secret`

**Root Access:**

-   Username: `root`
-   Password: `root`

## 🛠️ Useful Commands

### Container Management

```bash
# Start all containers
docker-compose up -d

# Stop all containers
docker-compose down

# Restart containers
docker-compose restart

# View logs
docker-compose logs -f

# View specific service logs
docker-compose logs -f app
```

### Application Commands

```bash
# Access container shell
docker-compose exec app bash

# Run artisan commands
docker-compose exec app php artisan migrate
docker-compose exec app php artisan db:seed
docker-compose exec app php artisan cache:clear

# Install composer packages
docker-compose exec app composer install

# Run tests
docker-compose exec app php artisan test
```

### Database Commands

```bash
# Access MySQL CLI
docker-compose exec db mysql -u root -proot

# Backup database
docker-compose exec db mysqldump -u root -proot lv_warehouse_inventory_ms > backup.sql

# Restore database
docker-compose exec -T db mysql -u root -proot lv_warehouse_inventory_ms < backup.sql
```

## 🔧 Configuration

### Environment Variables

The `.env` file is automatically configured by the setup script with Docker-appropriate settings:

-   Database host points to `db` service
-   Redis host points to `redis` service
-   Cache and session drivers set to Redis

### Custom Configuration

You can customize the setup by modifying:

-   `docker-compose.yml` - Service configurations
-   `Dockerfile` - Application container setup
-   `docker/nginx/nginx.conf` - Nginx web server settings
-   `docker/php/local.ini` - PHP configuration
-   `docker/supervisor/supervisord.conf` - Process management

## 📁 Project Structure

```
├── docker/
│   ├── nginx/
│   │   └── nginx.conf          # Nginx configuration
│   ├── php/
│   │   └── local.ini           # PHP settings
│   └── supervisor/
│       └── supervisord.conf    # Process supervisor config
├── .dockerignore               # Files to exclude from build
├── Dockerfile                  # Application container definition
├── docker-compose.yml          # Multi-container orchestration
└── docker-setup.sh            # Automated setup script
```

## 🐛 Troubleshooting

### Port Already in Use

If ports 8080, 3307, 6380, or 8081 are already in use, modify the port mappings in `docker-compose.yml`:

```yaml
ports:
    - "9080:80" # Change 8080 to 9080
```

### Permission Issues

```bash
docker-compose exec app chown -R www-data:www-data /var/www/html/storage
docker-compose exec app chmod -R 775 /var/www/html/storage
```

### Database Connection Issues

Ensure the database service is fully started:

```bash
docker-compose logs db
```

### Clear All Data and Start Fresh

```bash
docker-compose down -v
./docker-setup.sh
```

## 🔄 Updating the Application

```bash
# Pull latest changes
git pull origin dev

# Rebuild containers
docker-compose build --no-cache

# Restart services
docker-compose up -d

# Run migrations
docker-compose exec app php artisan migrate --force
```

## 🛡️ Production Considerations

For production deployment:

1. Update `.env` with production settings
2. Set `APP_ENV=production` and `APP_DEBUG=false`
3. Use proper database credentials
4. Configure SSL/TLS certificates
5. Set up proper volume backups
6. Consider using Docker Swarm or Kubernetes for orchestration
7. Implement proper logging and monitoring

## 📝 Notes

-   The application runs with PHP 8.2 FPM and Nginx
-   Supervisor manages PHP-FPM, Nginx, and Laravel queue workers
-   Redis is used for caching, sessions, and queues
-   All Laravel optimization commands are run during build
-   Storage and cache directories have proper permissions set

## 🤝 Support

For issues or questions:

1. Check the logs: `docker-compose logs -f`
2. Verify all services are running: `docker-compose ps`
3. Review the configuration files in the `docker/` directory
