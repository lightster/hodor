FROM php:5.6.24-cli
MAINTAINER Matt Light <matt.light@lightdatasys.com>

RUN apt-get update -qq \
    && apt install -yqq \
        libpq-dev \
        git \
        postgresql-client \
    && docker-php-ext-install -j$(nproc) \
        # for phpunit
        bcmath \
        # for postgres
        pdo_pgsql \
        # for phpamqplib
        sockets \
        # for composer
        zip \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug

ADD https://getcomposer.org/installer /usr/local/bin/composer-setup.php
RUN php /usr/local/bin/composer-setup.php \
    --quiet \
    --install-dir=/usr/local/bin \
    --filename=composer

COPY docker/php/fs /
COPY . /hodor

ADD https://raw.githubusercontent.com/vishnubob/wait-for-it/master/wait-for-it.sh \
    /usr/local/bin/wait-for-it.sh

RUN chmod 0755 /usr/local/bin/*.sh

VOLUME [ "/hodor" ]
WORKDIR /hodor

ENTRYPOINT ["bash", "/usr/local/bin/entrypoint.sh", "docker"]
