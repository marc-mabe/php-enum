ARG PHP_VERSION=latest
FROM php:${PHP_VERSION}-cli-alpine

WORKDIR /workdir

# install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_HTACCESS_PROTECT=0
ENV COMPOSER_CACHE_DIR=/.composer
