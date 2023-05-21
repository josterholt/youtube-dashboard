FROM php:8.1-cli
RUN cp /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.development.ini
RUN pear config-set php_ini /usr/local/etc/php/php.development.ini

RUN apt update
RUN apt install zip -y
RUN apt install git -y # For VS Code remote codings
RUN git config --global core.autocrlf true

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
# RUN php -r "if (hash_file('sha384', 'composer-setup.php') === '756890a4488ce9024fc62c56153228907f1545c228516cbf63f885e036d37e9a59d27d63f46af1d4d07ee0f76181c7d3') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
RUN php composer-setup.php
RUN php -r "unlink('composer-setup.php');"
RUN mv composer.phar /usr/local/bin/composer
RUN composer config --global use-parent-dir true
RUN export PATH=/var/www/html/vendor/bin:$PATH
COPY _docker/composer/config.json /root/.composer/config.json

RUN pecl install redis
RUN pecl install xdebug
RUN docker-php-ext-enable redis

RUN apt install zlib1g-dev
RUN pecl install grpc
