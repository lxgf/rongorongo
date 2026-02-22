#!/bin/sh
set -e

# Wait for PostgreSQL to be ready
echo "Waiting for database..."
until php -r "new PDO('pgsql:host=${DB_HOST:-db};port=${DB_PORT:-5432};dbname=${DB_DATABASE:-rongorongo}', '${DB_USERNAME:-rongorongo}', '${DB_PASSWORD:-secret}');" 2>/dev/null; do
    sleep 1
done
echo "Database is ready."

# Run migrations
php artisan migrate --force --no-interaction

# Publish Filament assets
php artisan filament:assets

# Optimize for production (skip in dev via APP_ENV check)
if [ "${APP_ENV}" = "production" ]; then
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

exec "$@"
