#!/bin/sh
composer update
composer install

export PHP_CLI_SERVER_WORKERS=8
php -S 0.0.0.0:8088 -c /usr/local/etc/php/php.development.ini -t ./src/ 