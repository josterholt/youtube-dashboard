FROM php:8.2-cli
# Configure PHP
RUN cp /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.development.ini
RUN pear config-set php_ini /usr/local/etc/php/php.development.ini

# Install dependencies
RUN apt update && apt install zip git -y
RUN git config --global core.autocrlf true

# Composer installation
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
&& php composer-setup.php \
&& php -r "unlink('composer-setup.php');" \
&& mv composer.phar /usr/local/bin/composer

# Install required PHP extensions
RUN pecl install redis xdebug && docker-php-ext-enable redis xdebug
COPY _docker/php/php.development.ini /usr/local/etc/php/php.development.ini

# grpc dependencies and installation
RUN apt install zlib1g-dev
#RUN pecl install grpc
ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN install-php-extensions grpc

# Create user web service will run as
RUN useradd -ms /bin/bash web-dev \
 && mkdir -p /var/www/html/vendor \
 && chown -R web-dev:web-dev /var/www/html

# RUNNING AS web-dev
USER web-dev
WORKDIR /var/www/html

# Configure Composer for user
RUN composer config --global use-parent-dir true \
&& export PATH=/var/www/html/vendor/bin:$PATH
COPY _docker/composer/config.json /home/web-dev/.composer/config.json
