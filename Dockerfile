FROM dunglas/frankenphp AS server

RUN install-php-extensions @composer intl xdebug

COPY composer.json composer.lock ./

RUN COMPOSER_ALLOW_SUPERUSER=1 composer install -o --no-dev -n && \
    echo xdebug.client_host=172.22.192.1 >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

COPY src public ./

ENV SERVER_PORT=443 SERVER_NAME=:80
