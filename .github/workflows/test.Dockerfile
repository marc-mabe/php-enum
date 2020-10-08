ARG PHP_VERSION=latest
FROM php:${PHP_VERSION}-cli-alpine

WORKDIR /workdir

# install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_HTACCESS_PROTECT=0
ENV COMPOSER_CACHE_DIR=/.composer

# install PHP extension pcov
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && mkdir -p /usr/src/php/ext/pcov && curl -fsSL https://pecl.php.net/get/pcov | tar xvz -C /usr/src/php/ext/pcov --strip 1 \
    && docker-php-ext-install pcov \
    && docker-php-ext-enable pcov \
    && rm -Rf /usr/src/php/ext/pcov \
    && apk del --no-cache .build-deps
