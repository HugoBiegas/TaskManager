# Dockerfile de Production pour Render.com
# Multi-stage build optimisé pour Symfony 6.4 LTS

# ============================================
# Stage 1: Composer dependencies
# ============================================
FROM composer:2 AS composer

WORKDIR /app

# Copier les fichiers composer (composer.lock peut ne pas exister)
COPY composer.json ./
COPY composer.lock* ./

# Installer les dépendances (sans dev)
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --prefer-dist \
    --ignore-platform-reqs

# ============================================
# Stage 2: Build final
# ============================================
FROM php:8.3-fpm-alpine AS production

# Arguments de build
ARG APP_ENV=prod

# Installation des dépendances système
RUN apk add --no-cache \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    icu-dev \
    libpq-dev \
    nginx \
    supervisor

# Installation des extensions PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_pgsql \
        pgsql \
        zip \
        gd \
        intl \
        opcache \
        bcmath

# Installation de APCu
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && apk del .build-deps

# Configuration PHP pour la production
COPY docker/php/php-prod.ini /usr/local/etc/php/conf.d/custom.ini

# Configuration Nginx
COPY docker/nginx/nginx-render.conf /etc/nginx/http.d/default.conf

# Configuration Supervisor
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Répertoire de travail
WORKDIR /var/www/html

# Copier les fichiers de l'application
COPY --from=composer /app/vendor ./vendor
COPY . .

# Copier composer depuis le stage composer pour finaliser l'autoload
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Finaliser l'installation Composer (autoload optimisé)
RUN composer dump-autoload --optimize --classmap-authoritative --no-dev

# Créer les répertoires nécessaires et définir les permissions
RUN mkdir -p var/cache var/log public/build \
    && chown -R www-data:www-data var public

# Warmup du cache Symfony
RUN php bin/console cache:warmup --env=prod

# Port exposé (Render utilise la variable PORT)
ENV PORT=10000
EXPOSE 10000

# Script d'entrée pour les migrations automatiques
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
