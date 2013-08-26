#! /bin/sh

PHP53=`php -r "echo (int)(PHP_VERSION_ID >= 50300);"`
if [ "1" = ${PHP53} ]; then
	composer require --dev "satooshi/php-coveralls dev-master"
	php vendor/bin/coveralls -v
fi
