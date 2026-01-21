FROM php:8.4-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    libpq-dev \
    icu-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl

# Install PHP extensions
RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    intl \
    opcache \
    zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create necessary directories for supervisor and nginx
RUN mkdir -p /var/log/supervisor /var/log/nginx /run/nginx

# Set working directory
WORKDIR /var/www/html

# Copy composer files first for better caching
COPY composer.json composer.lock ./

# Install dependencies without dev and without scripts (no autoload yet)
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Copy the rest of the application
COPY . .

# Set APP_ENV to prod for cache:clear
ENV APP_ENV=prod

# Generate autoloader and clear cache
RUN composer dump-autoload --optimize --classmap-authoritative \
    && php bin/console cache:clear --env=prod --no-warmup || true \
    && php bin/console cache:warmup --env=prod || true

# Create necessary directories
RUN mkdir -p var/cache var/log public/uploads/avatars \
    && chown -R www-data:www-data var public/uploads

# Copy nginx configuration
COPY docker/nginx.conf /etc/nginx/http.d/default.conf

# Copy supervisor configuration
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy PHP production configuration
COPY docker/php.ini /usr/local/etc/php/conf.d/app.ini

# Expose port
EXPOSE 10000

# Start supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
