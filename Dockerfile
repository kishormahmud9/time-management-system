# Dockerfile (Simple, works on Render)
# Stage 1: Build PHP dependencies with composer image
FROM composer:2 AS build
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist
COPY . .

# Stage 2: Runtime image
FROM php:8.2-cli
WORKDIR /var/www/html

# System deps & PHP extensions commonly needed for Laravel
RUN apt-get update && apt-get install -y \
    git unzip libzip-dev zip libpng-dev libonig-dev libxml2-dev \
    default-mysql-client \
  && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath zip gd

# Copy built app
COPY --from=build /app /var/www/html

# Ensure permissions for storage and cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache || true \
    && chmod -R 755 /var/www/html/storage /var/www/html/bootstrap/cache

# Expose "default" port (not strictly required, but useful)
EXPOSE 8000

# Use PORT env set by Render or default to 8000
ENV PORT=8000

# Start using artisan serve on the provided PORT
CMD php artisan serve --host=0.0.0.0 --port=${PORT}
















# # Stage 1: Build dependencies
# FROM composer:2 AS build
# WORKDIR /app
# COPY composer.json composer.lock ./
# RUN composer install --no-dev --optimize-autoloader
# COPY . .

# # Stage 2: Run app
# FROM php:8.2-fpm
# WORKDIR /var/www/html
# COPY --from=build /app .
# RUN docker-php-ext-install pdo pdo_mysql
# CMD php artisan serve --host=0.0.0.0 --port=8000
# EXPOSE 8001
