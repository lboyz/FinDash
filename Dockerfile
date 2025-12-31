# ======================
# Frontend build (Vite)
# ======================
FROM node:20-alpine AS frontend
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY . .
RUN npm run build

# ======================
# Backend (Laravel + Apache)
# ======================
FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libpq-dev \
 && docker-php-ext-install pdo pdo_mysql pdo_pgsql zip \
 && a2enmod rewrite

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy Laravel source
COPY . .

# Copy Vite build result
COPY --from=frontend /app/public/build /var/www/html/public/build

# Apache → public folder
RUN sed -i 's!/var/www/html!/var/www/html/public!g' \
    /etc/apache2/sites-available/000-default.conf

# Install PHP deps
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# Permissions
RUN chown -R www-data:www-data storage bootstrap/cache

# Start script
COPY start.sh /start.sh
RUN sed -i 's/\r$//' /start.sh
RUN chmod +x /start.sh

CMD ["/start.sh"]
