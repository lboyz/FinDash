# =========================
# 1) Build stage (PHP + Node)
# =========================
FROM php:8.2-cli AS build
WORKDIR /app

# System deps
RUN apt-get update && apt-get install -y \
    git unzip curl libzip-dev \
 && docker-php-ext-install zip \
 && rm -rf /var/lib/apt/lists/*

# Install Node.js 20
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
 && apt-get install -y nodejs

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install PHP deps
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress

# Install Node deps
COPY package*.json ./
RUN npm ci

# Copy source
COPY . .

# Build frontend (Wayfinder aman karena vendor sudah ada)
RUN npm run build


# =========================
# 2) Runtime stage
# =========================
FROM php:8.2-apache
WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libpq-dev \
 && docker-php-ext-install pdo pdo_mysql pdo_pgsql zip \
 && a2enmod rewrite \
 && rm -rf /var/lib/apt/lists/*

# Apache → public
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

# App files
COPY . .

# Vendor & build assets dari build stage
COPY --from=build /app/vendor /var/www/html/vendor
COPY --from=build /app/public/build /var/www/html/public/build

# Permissions
RUN chown -R www-data:www-data storage bootstrap/cache

# Start
COPY start.sh /start.sh
RUN chmod +x /start.sh
CMD ["/start.sh"]
