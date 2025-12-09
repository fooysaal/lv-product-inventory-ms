############################
# 1. Base PHP build stage
############################
FROM php:8.2-fpm AS base

WORKDIR /var/www/html

# Install system dependencies (minimal, optimized)
RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libicu-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
       pdo_mysql \
       mbstring \
       bcmath \
       exif \
       intl \
       zip \
       gd \
       opcache

# Set PHP memory limit
RUN echo "memory_limit=512M" > /usr/local/etc/php/conf.d/memory-limit.ini

# Copy composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer


############################
# 2. Composer build stage
############################
FROM base AS vendor

COPY composer.json composer.lock ./

# Set Composer environment variables
ENV COMPOSER_MEMORY_LIMIT=-1
ENV COMPOSER_ALLOW_SUPERUSER=1

# Install dependencies with error handling
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --prefer-dist \
    --no-interaction \
    --no-scripts \
    --verbose


############################
# 3. Application stage
############################
FROM base AS app

# Copy application source
COPY . .

# Copy vendor from previous stage (much faster)
COPY --from=vendor /var/www/html/vendor ./vendor

# Laravel permissions
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Laravel optimize (with error handling)
RUN php artisan config:cache || true && \
    php artisan route:cache || true && \
    php artisan view:cache || true


############################
# 4. Final Nginx + PHP-FPM stage
############################
FROM nginx:stable AS final

# Install PHP-FPM runtime dependencies
RUN apt-get update && apt-get install -y \
    libpng16-16 \
    libonig5 \
    libxml2 \
    libzip4 \
    libicu72 \
    libfreetype6 \
    libjpeg62-turbo \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Copy PHP runtime from app stage
COPY --from=app /usr/local/etc/php/ /usr/local/etc/php/
COPY --from=app /usr/local/lib/php/ /usr/local/lib/php/
COPY --from=app /usr/local/bin/php* /usr/local/bin/
COPY --from=app /usr/local/sbin/php-fpm /usr/local/sbin/php-fpm

# Copy application files
COPY --from=app /var/www/html /var/www/html

# Copy nginx config
COPY docker/nginx/nginx.conf /etc/nginx/conf.d/default.conf

# Create startup script
COPY <<'EOF' /start.sh
#!/bin/sh
php-fpm -D
nginx -g 'daemon off;'
EOF

RUN chmod +x /start.sh

WORKDIR /var/www/html

EXPOSE 80
CMD ["/start.sh"]
