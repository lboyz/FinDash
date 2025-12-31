# =========================
# 1) Frontend build stage
# =========================
FROM node:20-alpine AS frontend-build
WORKDIR /app

# Install php-cli + composer requirements (alpine)
RUN apk add --no-cache \
  php82 php82-phar php82-mbstring php82-ctype php82-openssl php82-json php82-tokenizer php82-xml php82-xmlwriter php82-dom php82-simplexml php82-session \
  curl git unzip

# (opsional) bikin alias php -> php82 kalau perlu
RUN ln -sf /usr/bin/php82 /usr/bin/php

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Cache dependencies: copy composer files dulu
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress

# Cache node deps: copy package files dulu
COPY package*.json ./
RUN npm ci

# Copy source setelah deps (biar cache maksimal)
COPY . .

# Build assets (ini sekarang aman karena vendor/autoload.php sudah ada)
RUN npm run build


# =========================
# 2) PHP runtime stage
# =========================
FROM php:8.2-apache
WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libpq-dev \
 && docker-php-ext-install pdo pdo_mysql pdo_pgsql zip \
 && a2enmod rewrite \
 && rm -rf /var/lib/apt/lists/*

# Arahkan Apache ke public
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

# Copy aplikasi
COPY . .

# Copy vendor hasil composer dari build stage (biar tidak perlu composer install di runtime)
COPY --from=frontend-build /app/vendor /var/www/html/vendor

# Copy hasil build vite
COPY --from=frontend-build /app/public/build /var/www/html/public/build

# Permissions Laravel
RUN chown -R www-data:www-data storage bootstrap/cache

# Start script
COPY start.sh /start.sh
RUN chmod +x /start.sh

CMD ["/start.sh"]
