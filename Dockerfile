FROM php:8.2-apache

# System deps
RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libpq-dev \
 && docker-php-ext-install pdo pdo_mysql zip \
 && a2enmod rewrite

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy code
COPY . .

# Apache config for Laravel public/
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

# Install PHP deps
RUN composer install --no-dev --optimize-autoloader

# Cache configs (optional; aman kalau env sudah ada saat runtime)
RUN php artisan config:clear && php artisan route:clear && php artisan view:clear || true

# Permissions
RUN chown -R www-data:www-data storage bootstrap/cache
