FROM jorge07/alpine-php:7.2-dev-sf as builder

WORKDIR /build

ENV APP_ENV prod

COPY composer.json /build
COPY composer.lock /build
COPY symfony.lock /build

RUN composer install --no-ansi --no-scripts --no-dev --no-interaction --no-progress --optimize-autoloader

COPY bin /build/bin
COPY config /build/config
COPY src /build/src
COPY public /build/public

RUN composer run-script post-install-cmd

FROM jorge07/alpine-php:7.2

WORKDIR /app

ENV APP_ENV prod

COPY --from=builder /build /app

RUN php /app/bin/console c:w
