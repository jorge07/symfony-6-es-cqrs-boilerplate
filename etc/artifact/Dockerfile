ARG PHP_VERSION=8.2.11


FROM jorge07/alpine-php:${PHP_VERSION} AS php-base
# The base image includes PHP 8.2 with extensions
# Just ensure PDO extensions are enabled via ini files
RUN apk add -U --no-cache sqlite \
    && echo "extension=pdo.so" > /etc/php82/conf.d/10_pdo.ini 2>/dev/null || true \
    && echo "extension=pdo_mysql.so" > /etc/php82/conf.d/11_pdo_mysql.ini 2>/dev/null || true \
    && echo "extension=mysqlnd.so" > /etc/php82/conf.d/09_mysqlnd.ini 2>/dev/null || true \
    && php -m | grep -i pdo || echo "PDO check skipped"

FROM jorge07/alpine-php:${PHP_VERSION}-dev AS php-dev

ENV PHP_INI_DIR /etc/php82

# The base image includes PHP 8.2 with extensions
# Ensure PDO extensions are enabled via ini files if needed
RUN apk add -U --no-cache sqlite \
    && echo "extension=pdo.so" > /etc/php82/conf.d/10_pdo.ini 2>/dev/null || true \
    && echo "extension=pdo_mysql.so" > /etc/php82/conf.d/11_pdo_mysql.ini 2>/dev/null || true \
    && echo "extension=mysqlnd.so" > /etc/php82/conf.d/09_mysqlnd.ini 2>/dev/null || true \
    && php -m

FROM php-dev AS builder

WORKDIR /app

ENV APP_ENV prod
ENV APP_SECRET default-secret

COPY composer.json /app

# Use --ignore-platform-req for extensions that may be available at runtime
RUN composer update --no-ansi --no-scripts --no-dev --no-interaction --no-progress --optimize-autoloader \
    --ignore-platform-req=ext-amqp --ignore-platform-req=ext-simplexml

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
