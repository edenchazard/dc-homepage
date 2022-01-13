FROM php:8.0.0-fpm-alpine
WORKDIR .
COPY ./src/composer.json composer.json
RUN apk update; \
    apk upgrade;

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install
EXPOSE 9000