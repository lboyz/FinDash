#!/bin/sh
set -e

# Default PORT kalau tidak diset
PORT="${PORT:-8000}"

# Set ServerName untuk hilangkan warning (optional)
echo "ServerName localhost" > /etc/apache2/conf-available/servername.conf
a2enconf servername >/dev/null 2>&1 || true

# Ubah Apache listen port (ports.conf + vhost)
sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -i "s/:80/:${PORT}/g" /etc/apache2/sites-available/000-default.conf

# Pastikan permission laravel
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache || true

# Start apache
apache2-foreground

# Start migrate
php artisan migrate --force