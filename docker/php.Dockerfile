FROM composer:latest AS composer
COPY --from=composer /usr/bin/composer /usr/bin/composer

FROM php:8.0.0-fpm-alpine
WORKDIR /var/www/html
COPY ./src/composer.json composer.json
RUN composer install --prefer-source --no-interaction --no-autoloader