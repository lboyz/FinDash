#!/bin/sh
set -e

PORT="${PORT:-8000}"

sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -i "s/:80/:${PORT}/g" /etc/apache2/sites-available/000-default.conf

chown -R www-data:www-data storage bootstrap/cache || true
chmod -R 775 storage bootstrap/cache || true

php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

apache2-foreground
