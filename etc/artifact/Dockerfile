ARG PHP_VERSION=8.2.11


FROM jorge07/alpine-php:${PHP_VERSION} AS php-base
# PHP modules are already included in the base image
# Only add additional packages if needed
RUN apk add -U --no-cache sqlite

FROM jorge07/alpine-php:${PHP_VERSION}-dev AS php-dev

ENV PHP_INI_DIR /etc/php82

# PHP modules are already included in the base image
RUN apk add -U --no-cache sqlite

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
