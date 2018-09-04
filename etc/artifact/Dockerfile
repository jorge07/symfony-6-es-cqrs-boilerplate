FROM jorge07/cqrs:builder as builder

WORKDIR /app

ENV APP_ENV prod
ENV APP_SECRET default-secret
ENV RABBITMQ_URL amqp://guest:guest@rmq:5672

COPY composer.json /app
COPY composer.lock /app
COPY symfony.lock /app

RUN composer install --no-ansi --no-scripts --no-dev --no-interaction --no-progress --optimize-autoloader

COPY bin /app/bin
COPY config /app/config
COPY src /app/src
COPY public /app/public

RUN composer run-script post-install-cmd && php /app/bin/console c:w

FROM jorge07/alpine-php:7.2 as php

ENV APP_ENV prod

WORKDIR /app

COPY --from=builder /app /app

FROM nginx:1.15-alpine as nginx

ENV APP_ENV prod

WORKDIR /app

COPY etc/artifact/nginx/nginx.conf /etc/nginx/conf.d/default.conf

COPY --from=builder /app/public /app/public
