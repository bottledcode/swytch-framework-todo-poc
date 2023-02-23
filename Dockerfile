FROM dunglas/frankenphp:latest AS build

RUN install-php-extensions @composer dom intl mbstring sodium zip uuid

COPY composer.json composer.lock ./

RUN composer install --no-dev -o

COPY public public
COPY src src

FROM dunglas/frankenphp:latest AS dev

RUN install-php-extensions xdebug @composer dom intl mbstring sodium zip uuid && \
    echo "xdebug.mode = debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.log = /tmp/xdebug.log" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

FROM build AS prod

RUN install-php-extensions opcache
