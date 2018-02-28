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

`make start`

Execute tests:

`make tests`

Static code analysis:

`make style`

Enter in php container:

`make s=php sh`

Disable\Enable Xdebug:

`make xoff`

`make xon`

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

