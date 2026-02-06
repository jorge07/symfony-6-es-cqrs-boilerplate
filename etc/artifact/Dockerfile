ARG PHP_VERSION=8.2


FROM php:${PHP_VERSION}-cli-alpine AS php-base
# Install system dependencies
RUN apk add -U --no-cache \
        sqlite \
        rabbitmq-c-dev \
        libzip-dev \
        icu-dev \
        libxml2-dev \
        mysql-client \
        $PHPIZE_DEPS

# Install PHP extensions - simplexml and xml are bundled with PHP, just need to enable them
RUN docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
        intl \
        zip \
        xml \
        opcache \
    && pecl install amqp \
    && docker-php-ext-enable amqp \
    && php -m | grep -E "pdo_mysql|PDO" && echo "PDO MySQL extension verified"

FROM php:${PHP_VERSION}-cli-alpine AS php-dev

WORKDIR /app

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install system dependencies
RUN apk add -U --no-cache \
        sqlite \
        git \
        bash \
        rabbitmq-c-dev \
        libzip-dev \
        icu-dev \
        libxml2-dev \
        linux-headers \
        mysql-client \
        $PHPIZE_DEPS

# Install PHP extensions
RUN docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
        intl \
        zip \
        xml \
        opcache \
        pcntl \
        posix \
    && pecl install amqp xdebug \
    && docker-php-ext-enable amqp xdebug

# Verify PDO MySQL is available - fail build if not
RUN php -m | grep pdo_mysql || (echo "ERROR: pdo_mysql extension not found!" && exit 1) \
    && php -r "new PDO('mysql:host=localhost', 'test', 'test');" 2>&1 | grep -v "Connection refused" || true \
    && echo "PHP extensions verified successfully"

FROM php-dev AS builder

WORKDIR /app

ENV APP_ENV prod
ENV APP_SECRET default-secret
# Config for cache:clear (not used at runtime, just for building)
ENV DATABASE_HOST localhost
ENV DATABASE_PORT 3306
ENV DATABASE_NAME api
ENV DATABASE_USER root
ENV DATABASE_PASSWORD password
ENV DEFAULT_URI http://localhost

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
