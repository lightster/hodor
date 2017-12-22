FROM php:5.6-cli-alpine
MAINTAINER Matt Light <matt.light@lightdatasys.com>

# bash is for the docker entrypoint
# git is for composer
# bcmath is for phpunit
# sockets is for phpamqplib
# zip is for composer
# pdo_pgsql is for postgres

RUN apk add --no-cache --virtual '.lightster-phpize-deps' \
        $PHPIZE_DEPS \
    && apk add --no-cache \
        bash \
        git \
        zlib-dev \
        postgresql-dev \
        postgresql-client \
    && docker-php-ext-install \
        bcmath \
        sockets \
        zip \
        pdo_pgsql \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && apk del --no-cache .lightster-phpize-deps

ADD https://getcomposer.org/installer /usr/local/bin/composer-setup.php
RUN php /usr/local/bin/composer-setup.php \
    --quiet \
    --install-dir=/usr/local/bin \
    --filename=composer

ADD https://raw.githubusercontent.com/vishnubob/wait-for-it/master/wait-for-it.sh \
    /usr/local/bin/wait-for-it.sh

RUN chmod 0755 /usr/local/bin/*.sh

VOLUME [ "/hodor" ]
WORKDIR /hodor

ENTRYPOINT ["bash", "/usr/local/bin/entrypoint.sh", "docker"]
