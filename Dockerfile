# ---------- Frontend Build ----------
FROM node:20-alpine AS frontend

WORKDIR /app

COPY package*.json ./
RUN npm install

COPY . .
RUN npm run build


# ---------- Backend Build ----------
FROM composer:2 AS backend

WORKDIR /app
COPY . .
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --no-scripts

# ---------- Runtime ----------
FROM php:8.4-fpm-alpine

# System dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    bash \
    curl \
    libzip-dev \
    zip \
    unzip \
    oniguruma-dev

# PHP extensions
RUN docker-php-ext-install pdo_mysql zip mbstring

WORKDIR /var/www/html

# Copy backend
COPY --from=backend /app /var/www/html

# Copy frontend build
COPY --from=frontend /app/public/build /var/www/html/public/build

# Nginx config
COPY docker/nginx/nginx.conf /etc/nginx/http.d/nginx.conf

# Supervisor
COPY docker/supervisor/supervisord.conf /etc/supervisord.conf

# Permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
