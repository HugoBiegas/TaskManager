# Dockerfile de Production pour Render.com
# Build optimisé pour Symfony 6.4 LTS

FROM php:8.3-fpm-alpine

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
    supervisor \
    netcat-openbsd

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

# Installation de Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Configuration PHP pour le BUILD (sans preload pour éviter les erreurs)
COPY docker/php/php-build.ini /usr/local/etc/php/conf.d/custom.ini

# Configuration Nginx
COPY docker/nginx/nginx-render.conf /etc/nginx/http.d/default.conf

# Configuration Supervisor
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Répertoire de travail
WORKDIR /var/www/html

# Copier d'abord les fichiers composer pour profiter du cache Docker
COPY composer.json ./
COPY composer.lock* ./

# Installer les dépendances (sans scripts pour éviter les erreurs)
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install \
    --no-dev \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader

# Copier le reste de l'application
COPY . .

# Exécuter les scripts post-install et préparer l'application
RUN composer run-script post-install-cmd --no-dev || true \
    && mkdir -p var/cache var/log public/build \
    && chown -R www-data:www-data var public

# Remplacer par la configuration PHP de production (avec preload)
COPY docker/php/php-prod.ini /usr/local/etc/php/conf.d/custom.ini

# Warmup du cache Symfony (avec gestion d'erreur si pas de BDD)
RUN APP_ENV=prod APP_DEBUG=0 php bin/console cache:warmup --no-optional-warmers || true

# Port exposé (Render utilise la variable PORT)
ENV PORT=10000
EXPOSE 10000

# Script d'entrée pour les migrations automatiques
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
