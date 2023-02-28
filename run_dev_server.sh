#!/bin/sh
composer update
composer install
php -S 0.0.0.0:8088 -c /usr/local/etc/php/php.development.ini -t ./src/ 