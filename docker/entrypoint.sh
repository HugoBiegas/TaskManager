#!/bin/sh
set -e

echo "🚀 Starting TaskManager..."

# Attendre que la base de données soit disponible
if [ -n "$DATABASE_URL" ]; then
    echo "⏳ Waiting for database..."

    # Extraire l'hôte et le port de DATABASE_URL
    DB_HOST=$(echo $DATABASE_URL | sed -E 's|.*@([^:]+):([0-9]+)/.*|\1|')
    DB_PORT=$(echo $DATABASE_URL | sed -E 's|.*@([^:]+):([0-9]+)/.*|\2|')

    # Attendre que PostgreSQL soit prêt (max 30 secondes)
    RETRIES=30
    until nc -z $DB_HOST $DB_PORT 2>/dev/null || [ $RETRIES -eq 0 ]; do
        echo "⏳ Waiting for PostgreSQL at $DB_HOST:$DB_PORT... ($RETRIES retries left)"
        RETRIES=$((RETRIES-1))
        sleep 1
    done

    if [ $RETRIES -eq 0 ]; then
        echo "⚠️  Could not connect to database, continuing anyway..."
    else
        echo "✅ Database is ready!"
    fi
fi

# Exécuter les migrations Doctrine
echo "📦 Running database migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration || true

# Vider et réchauffer le cache
echo "🔥 Warming up cache..."
php bin/console cache:clear --env=prod --no-warmup
php bin/console cache:warmup --env=prod

# Créer les répertoires nécessaires
mkdir -p var/cache var/log
chown -R www-data:www-data var

echo "✅ TaskManager is ready!"

# Exécuter la commande passée en argument
exec "$@"
