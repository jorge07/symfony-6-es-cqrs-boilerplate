# Symfony 5 ES CQRS Boilerplate

A boilerplate for DDD, CQRS, Event Sourcing applications using Symfony as framework and running with php7

![pr](https://github.com/jorge07/symfony-5-es-cqrs-boilerplate/workflows/pr/badge.svg)
[![Coverage Status](https://coveralls.io/repos/github/jorge07/symfony-5-es-cqrs-boilerplate/badge.svg?branch=master)](https://coveralls.io/github/jorge07/symfony-5-es-cqrs-boilerplate?branch=coverage)

Symfony 4 still available in [symfony-4 branch](https://github.com/jorge07/symfony-5-es-cqrs-boilerplate/tree/symfony-4)

## Documentation

[Buses](https://github.com/jorge07/symfony-5-es-cqrs-boilerplate/tree/master/doc/GetStarted/Buses.md)

[Creating an Application Use Case](https://github.com/jorge07/symfony-5-es-cqrs-boilerplate/tree/master/doc/GetStarted/UseCases.md)

[Adding Projections](https://github.com/jorge07/symfony-5-es-cqrs-boilerplate/tree/master/doc/GetStarted/Projections.md)

[Async executions](https://github.com/jorge07/symfony-5-es-cqrs-boilerplate/tree/master/doc/GetStarted/Async.md)

[UI workflow](https://github.com/jorge07/symfony-5-es-cqrs-boilerplate/blob/master/doc/Workflow.md)

[Xdebug configuration](https://github.com/jorge07/symfony-5-es-cqrs-boilerplate/blob/master/doc/GetStarted/Xdebug.md)

[Kubernetes Deployment](https://github.com/jorge07/symfony-5-es-cqrs-boilerplate/blob/master/doc/Deployment.md)

## Architecture

![Architecture](https://i.imgur.com/SzHgMft.png)

## Implementations

- [x] Environment in Docker
- [x] Symfony Messenger
- [x] Event Store
- [x] Read Model
- [x] Async Event subscribers
- [x] Rest API
- [x] Web UI (A Terrible UX/UI)
- [x] Event Store Rest API 
- [x] Swagger API Doc

## Use Cases

#### User
- [x] Sign up
- [x] Change Email
- [x] Sign in
- [x] Logout

![API Doc](https://i.imgur.com/DBZsPlE.png)

## Stack

- PHP 7.4
- Mysql 8.0
- Elastic & Kibana 6.6
- RabbitMQ 3

## Project Setup

Up environment:

`make start`

Execute tests:

`make phpunit`

Static code analysis:

`make style`

Code style fixer:

`make cs`

Code style checker:

`make cs-check`

Enter in php container:

`make s=php sh`

Disable\Enable Xdebug:

`make xoff`

`make xon`

Build image to deploy

`make artifact`

## PHPStorm integration

PHPSTORM has native integration with Docker compose. That's nice but will stop your php container after run the test scenario. That's not nice when using fpm. A solution could be use another container just for that purpose. But I don't want. For that reason I use ssh connection.

IMPORTANT

> ssh in the container it's ONLY for that reason, if you've ssh installed in your production container, you're doing it wrong... 

Use ssh remote connection.
---

Host: 
- Docker 4 Mac: `localhost`
- docker machine OR dinghy: `192.168.99.100`

Port: 
 - `2323`

Filesystem mapping:
 - `{PROJECT_PATH}` -> `/app`
