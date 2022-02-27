FROM composer:latest AS composer

FROM php:8.0-fpm-alpine3.14
COPY --from=composer /usr/bin/composer /usr/bin/composer
COPY ./docker/php.ini /usr/local/etc/php/php.ini

WORKDIR /var/www/html
COPY ./src/composer.json composer.json
RUN composer install --prefer-source --no-interaction --no-dev
COPY ./src .
#RUN php ./scripts/sitemap.php