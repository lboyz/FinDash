FROM node:20-alpine AS frontend-build

WORKDIR /app
COPY package*.json ./
RUN apk add --no-cache php
RUN npm ci
COPY . .
RUN npm run build

FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libpq-dev \
 && docker-php-ext-install pdo pdo_mysql pdo_pgsql zip \
 && a2enmod rewrite

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ... bagian atas tetap

WORKDIR /var/www/html
COPY . .
COPY --from=frontend-build /app/public/build /var/www/html/public/build

# Arahkan Apache ke folder public Laravel
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

RUN composer install --no-dev --optimize-autoloader || true
RUN chown -R www-data:www-data storage bootstrap/cache

# Tambah start script
COPY start.sh /start.sh
RUN chmod +x /start.sh

CMD ["/start.sh"]
