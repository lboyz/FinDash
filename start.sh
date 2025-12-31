#!/bin/sh
set -e

PORT="${PORT:-8000}"

# Update Apache to listen on $PORT (Koyeb)
sed -i "s/^Listen 80$/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# Laravel permissions
chown -R www-data:www-data storage bootstrap/cache || true
chmod -R 775 storage bootstrap/cache || true

# Optional: create storage symlink
php artisan storage:link || true

# Cache (prefer in production; fall back safely)
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Optional migrations (set RUN_MIGRATIONS=true)
if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    php artisan migrate --force || true
fi

exec apache2-foreground
