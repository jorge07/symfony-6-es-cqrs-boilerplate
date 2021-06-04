ARG PHP_VERSION=8.0.1

FROM jorge07/alpine-php:${PHP_VERSION} as php-base
RUN  echo "http://dl-4.alpinelinux.org/alpine/edge/testing" >> /etc/apk/repositories
# List your dependencies here
ARG PHP_MODULES="php-pdo@cast php-pdo_mysql@cast php8-pecl-amqp@cast"
RUN apk add -U --no-cache ${PHP_MODULES}

FROM jorge07/alpine-php:${PHP_VERSION}-dev as php-dev
RUN  echo "http://dl-4.alpinelinux.org/alpine/edge/testing" >> /etc/apk/repositories
# List your dependencies here
ARG PHP_MODULES="php-pdo@cast php-pdo_mysql@cast php8-pecl-amqp@cast"
RUN apk add -U --no-cache ${PHP_MODULES}

ENV PHP_INI_DIR /etc/php8

# Install your project DEVELOPMENT dependencies here
RUN apk add -U sqlite php8-posix@cast \
	&& version=$(php -r "echo PHP_MAJOR_VERSION.PHP_MINOR_VERSION;") \
    && curl -A "Docker" -o /tmp/blackfire-probe.tar.gz -D - -L -s https://blackfire.io/api/v1/releases/probe/php/alpine/amd64/$version \
    && mkdir -p /tmp/blackfire \
    && tar zxpf /tmp/blackfire-probe.tar.gz -C /tmp/blackfire \
    && mv /tmp/blackfire/blackfire-*.so $(php -r "echo ini_get ('extension_dir');")/blackfire.so \
    && printf "extension=blackfire.so\nblackfire.agent_socket=tcp://blackfire:8707\n" > $PHP_INI_DIR/conf.d/blackfire.ini \
    && rm -rf /tmp/blackfire /tmp/blackfire-probe.tar.gz \
    && mkdir -p /tmp/blackfire \
    && curl -A "Docker" -L https://blackfire.io/api/v1/releases/client/linux_static/amd64 | tar zxp -C /tmp/blackfire \
    && mv /tmp/blackfire/blackfire /usr/bin/blackfire \
    && rm -Rf /tmp/blackfire

FROM php-dev as builder

WORKDIR /app

ENV APP_ENV prod
ENV APP_SECRET default-secret

COPY composer.json /app
COPY composer.lock /app
COPY symfony.lock /app

RUN composer install --no-ansi --no-scripts --no-dev --no-interaction --no-progress --optimize-autoloader

COPY bin /app/bin
COPY config /app/config
COPY src /app/src
COPY public /app/public

RUN composer run-script post-install-cmd

FROM php-base as php

ENV APP_ENV prod

WORKDIR /app

COPY --from=builder /app /app

FROM nginx:1.17-alpine as nginx

ENV APP_ENV prod

WORKDIR /app

COPY etc/artifact/nginx/nginx.conf /etc/nginx/conf.d/default.conf

COPY --from=builder /app/public /app/public
