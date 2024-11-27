ARG PHP_VERSION=latest
ARG CODE_COVERAGE=false
FROM php:${PHP_VERSION}-cli-alpine
ARG CODE_COVERAGE

WORKDIR /workdir

# install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_HTACCESS_PROTECT=0
ENV COMPOSER_CACHE_DIR=/.composer

# install PHP extension pcov
RUN if [[ "${CODE_COVERAGE}" == "true" ]] ; \
    then apk add --no-cache --virtual .build-deps $PHPIZE_DEPS linux-headers \
      && pecl install xdebug \
      && docker-php-ext-enable xdebug \
      && apk del --no-cache .build-deps ; \
    fi

