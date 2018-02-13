# Symfony 4 ES CQRS Boilerplate

A boilerplate for DDD, CQRS, Event Sourcing applications using Symfony as framework and running with php7

## Implementations

- [x] Environment in Docker
- [x] Command Bus, Query Bus, Event Bus
- [x] Event Store
- [x] Read Model
- [x] Async Event subscribers
- [x] Rest API
- [x] Web UI (A Terrible UX/UI)
- [x] Event Store Rest API 

## Use Cases

[See UI workflow](https://github.com/jorge07/symfony-4-es-cqrs-boilerplate/blob/master/doc/Workflow.md)
#### User
- [x] Sign up
- [x] Change Email
- [x] Sign in
- [x] Logout

## Architecture

![Architecture](https://i.imgur.com/SzHgMft.png)

## Stack

- PHP7.1
- Mysql
- Elastic & Kibana 5.6
- RabbitMQ

## Project Setup

Up environment:

`docker-compose up -d`

Install deps:

`docker-compose exec php sh -lc 'composer install'`

Run database migrations:

`docker-compose exec php sh -lc 'dev d:m:m -n'`

Execute tests:

`docker-compose exec php sh -lc './bin/phpunit'`

Static code analysis:

`docker-compose exec php sh -lc './vendor/bin/phpstan analyse -l 5 -c phpstan.neon src tests'`

Enter in php container:

`docker-compose exec php sh -l`

Disable\Enable Xdebug:

`docker-compose exec php sh -lc 'xoff'`

`docker-compose exec php sh -lc 'xon'`

## PHPStorm integration

PHPSTORM has native integration with Docker compose. That's nice but will stop your php container after run the test scenario. That's not nice when using fpm. A solution could be use another container just for that purpose. But I don't want. For that reason I use ssh connection. Note that ssh in the cntainer it's ONLY for that reason, if you've ssh installed in your production container, you're doing it wrong... 

Use ssh remote connection.
---

HOST: 

- Docker 4 Mac: `localhost`
- docker machine OR dinghy: `192.168.99.100`

PORT: 

 - `2323`

Filesystem mapping:

 - `{PROJECT_PATH}` -> `/app`

