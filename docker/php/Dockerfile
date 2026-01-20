# Dockerfile PHP pour Symfony 6.4 LTS
FROM php:8.3-fpm-alpine

# Arguments pour build
ARG APP_ENV=dev

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
    linux-headers \
    $PHPIZE_DEPS

# Installation des extensions PHP nécessaires pour Symfony
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

# Installation de APCu pour le cache
RUN pecl install apcu && docker-php-ext-enable apcu

# Installation de Xdebug pour le développement
RUN if [ "$APP_ENV" = "dev" ]; then \
        pecl install xdebug && docker-php-ext-enable xdebug; \
    fi

# Installation de Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Installation de Symfony CLI
RUN curl -sS https://get.symfony.com/cli/installer | bash \
    && mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

# Configuration du répertoire de travail
WORKDIR /var/www/html

# Création d'un utilisateur non-root pour les fichiers
RUN addgroup -g 1000 symfony && adduser -u 1000 -G symfony -s /bin/sh -D symfony

# Permissions
RUN chown -R symfony:symfony /var/www/html

# Utiliser l'utilisateur symfony pour composer (éviter les warnings)
USER symfony

# Exposer le port FPM
EXPOSE 9000

CMD ["php-fpm"]
