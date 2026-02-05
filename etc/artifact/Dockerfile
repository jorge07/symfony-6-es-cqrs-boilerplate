ARG PHP_VERSION=8.2


FROM php:${PHP_VERSION}-cli-alpine AS php-base
# Install required PHP extensions
RUN apk add -U --no-cache \
        sqlite \
        rabbitmq-c-dev \
        libzip-dev \
        icu-dev \
        libxml2-dev \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        intl \
        zip \
        simplexml \
        opcache \
    && pecl install amqp \
    && docker-php-ext-enable amqp

FROM php:${PHP_VERSION}-cli-alpine AS php-dev

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install required PHP extensions and dev tools
RUN apk add -U --no-cache \
        sqlite \
        git \
        bash \
        rabbitmq-c-dev \
        libzip-dev \
        icu-dev \
        libxml2-dev \
        linux-headers \
        $PHPIZE_DEPS \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        intl \
        zip \
        simplexml \
        opcache \
        pcntl \
        posix \
    && pecl install amqp xdebug \
    && docker-php-ext-enable amqp xdebug \
    && php -m

FROM php-dev AS builder

WORKDIR /app

ENV APP_ENV prod
ENV APP_SECRET default-secret

COPY composer.json /app

RUN composer update --no-ansi --no-scripts --no-dev --no-interaction --no-progress --optimize-autoloader

COPY bin /app/bin
COPY config /app/config
COPY src /app/src
COPY public /app/public

RUN composer run-script post-install-cmd

FROM php-base AS php

ENV APP_ENV prod

WORKDIR /app

COPY --from=builder /app /app

FROM nginx:1.17-alpine AS nginx

ENV APP_ENV prod

WORKDIR /app

COPY etc/artifact/nginx/nginx.conf /etc/nginx/conf.d/default.conf

COPY --from=builder /app/public /app/public
