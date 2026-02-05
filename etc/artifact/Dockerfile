ARG PHP_VERSION=8.3

FROM jorge07/alpine-php:${PHP_VERSION} AS php-base

# Upgrade PHP to latest patch and install project-specific extensions
RUN apk upgrade --no-cache \
    && apk add --no-cache \
        php83-pdo \
        php83-pdo_mysql \
        php83-simplexml \
        php83-xmlwriter \
        php83-posix \
        php83-fileinfo \
        php83-pecl-amqp

FROM jorge07/alpine-php:${PHP_VERSION}-dev AS php-dev

WORKDIR /app

# Upgrade PHP to latest patch and install project-specific extensions + dev tools
RUN apk upgrade --no-cache \
    && apk add --no-cache \
        php83-pdo \
        php83-pdo_mysql \
        php83-simplexml \
        php83-xmlwriter \
        php83-posix \
        php83-fileinfo \
        php83-pecl-amqp \
        mysql-client

FROM php-dev AS builder

WORKDIR /app

ENV APP_ENV=prod
ENV APP_SECRET=default-secret
# Required by Symfony cache:clear during build
ENV DATABASE_HOST=localhost
ENV DATABASE_PORT=3306
ENV DATABASE_NAME=api
ENV DATABASE_USER=root
ENV DATABASE_PASSWORD=password
ENV DEFAULT_URI=http://localhost

COPY composer.json composer.lock /app

RUN composer install --no-ansi --no-scripts --no-dev --no-interaction --no-progress --optimize-autoloader

COPY bin /app/bin
COPY config /app/config
COPY src /app/src
COPY public /app/public

RUN composer run-script post-install-cmd

FROM php-base AS php

ENV APP_ENV=prod

WORKDIR /app

COPY --from=builder /app /app

FROM nginx:1.27-alpine AS nginx

ENV APP_ENV=prod

WORKDIR /app

COPY etc/artifact/nginx/nginx.conf /etc/nginx/conf.d/default.conf

COPY --from=builder /app/public /app/public
