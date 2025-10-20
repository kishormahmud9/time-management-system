# Stage 1: Build dependencies
FROM composer:2 AS build
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader
COPY . .

# Stage 2: Run app
FROM php:8.2-fpm
WORKDIR /var/www/html
COPY --from=build /app .
RUN docker-php-ext-install pdo pdo_mysql
CMD php artisan serve --host=0.0.0.0 --port=8000
EXPOSE 8001
