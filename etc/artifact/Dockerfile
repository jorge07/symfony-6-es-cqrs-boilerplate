ARG PHP_VERSION=8.2.11


FROM jorge07/alpine-php:${PHP_VERSION} AS php-base
# Enable Alpine testing repository and install PHP extensions
# PHP 8.2 packages are in the testing repository
ARG PHP_MODULES="php82-pdo php82-pdo_mysql php82-mysqlnd php82-pecl-amqp php82-tokenizer php82-posix php82-simplexml php82-xmlwriter"
RUN echo "https://dl-cdn.alpinelinux.org/alpine/edge/testing" >> /etc/apk/repositories \
    && apk add -U --no-cache ${PHP_MODULES} sqlite

FROM jorge07/alpine-php:${PHP_VERSION}-dev AS php-dev

ENV PHP_INI_DIR /etc/php82

# Enable Alpine testing repository and install PHP extensions
# PHP 8.2 packages are in the testing repository
ARG PHP_MODULES="php82-pdo php82-pdo_mysql php82-mysqlnd php82-pecl-amqp php82-tokenizer php82-posix php82-simplexml php82-xmlwriter"
RUN echo "https://dl-cdn.alpinelinux.org/alpine/edge/testing" >> /etc/apk/repositories \
    && apk add -U --no-cache ${PHP_MODULES} sqlite \
    && php -m

FROM php-dev AS builder

WORKDIR /app

ENV APP_ENV prod
ENV APP_SECRET default-secret

COPY composer.json /app

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
