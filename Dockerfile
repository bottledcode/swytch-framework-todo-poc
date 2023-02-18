FROM dunglas/frankenphp AS server

RUN install-php-extensions @composer

COPY composer.json composer.lock ./

RUN composer install -o --no-dev -n

COPY src ./src
