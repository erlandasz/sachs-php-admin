# Stage 1: Build frontend assets
FROM node:20-alpine AS node-builder
WORKDIR /app
COPY package*.json yarn.lock ./
RUN yarn install --frozen-lockfile
COPY . .
RUN yarn build

# Stage 2: Build PHP application with dependencies and assets
FROM php:8.2-fpm-alpine AS php-base

# Install required Alpine packages including nginx, supervisor, PHP extensions dependencies
RUN apk add --no-cache \
    git curl libpng-dev libxml2-dev zip unzip oniguruma-dev libzip-dev freetype-dev libjpeg-turbo-dev libwebp-dev icu-dev \
    postgresql-dev sqlite-dev autoconf build-base \
    nginx supervisor cronie

# Install PHP extensions including gd with proper configuration and redis via pecl
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp && \
    docker-php-ext-install pdo pdo_mysql pdo_pgsql pdo_sqlite mbstring exif pcntl bcmath gd zip intl opcache && \
    pecl install redis && docker-php-ext-enable redis

# Install Composer from official composer image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy composer manifests and install PHP dependencies (optimized, no dev)
COPY composer*.json ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Copy application source code
COPY . .

# Run Composer post-autoload dump scripts
RUN composer run-script post-autoload-dump

# Copy built frontend assets from node-builder stage (assumes Vite or similar build system)
COPY --from=node-builder /app/public/build ./public/build

# Set permissions for Laravel folders
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 storage bootstrap/cache

# Prepare environment file, generate APP key, cache config/routes/views
RUN cp .env.example .env || echo "APP_NAME=Laravel" > .env
RUN php artisan key:generate --no-interaction || true
RUN php artisan config:cache && php artisan route:cache && php artisan view:cache

# Copy service configuration files
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini

# Copy cron job and set permissions, register crontab for www-data user
COPY docker/schedule-cron /etc/cron.d/schedule-cron
RUN chmod 0644 /etc/cron.d/schedule-cron && crontab -u www-data /etc/cron.d/schedule-cron

# Create necessary runtime directories and set ownership
RUN mkdir -p /var/log/nginx /var/log/supervisor /run/nginx && \
    chown -R www-data:www-data /var/www/html

# Expose HTTP port
EXPOSE 80

# Start supervisord to manage nginx, php-fpm, and cron
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
