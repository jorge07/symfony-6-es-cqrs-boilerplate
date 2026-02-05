ARG PHP_VERSION=8.2.11


FROM jorge07/alpine-php:${PHP_VERSION} AS php-base
# The base image includes PHP 8.2 with most extensions compiled-in
# Add simplexml and xmlwriter for Doctrine and PHPUnit
RUN apk add -U --no-cache sqlite php82-simplexml php82-xmlwriter

FROM jorge07/alpine-php:${PHP_VERSION}-dev AS php-dev

ENV PHP_INI_DIR /etc/php82

# The base image includes PHP 8.2 with most extensions compiled-in
# Add simplexml and xmlwriter for Doctrine and PHPUnit
RUN apk add -U --no-cache sqlite php82-simplexml php82-xmlwriter && php -m

FROM php-dev AS builder

WORKDIR /app

ENV APP_ENV prod
ENV APP_SECRET default-secret

COPY composer.json /app

# Ignore platform requirements - extensions are available at runtime
RUN composer update --no-ansi --no-scripts --no-dev --no-interaction --no-progress --optimize-autoloader \
    --ignore-platform-reqs

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
