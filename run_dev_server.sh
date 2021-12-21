#!/bin/sh
composer update
composer install
php -S 0.0.0.0:8088 -t ./src/