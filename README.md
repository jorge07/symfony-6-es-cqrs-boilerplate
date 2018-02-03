# Symfony 4 ES CQRS Boilerplate

- [x] Command Bus, Query Bus, Event Bus & Async Event Bus
- [x] Event Store
- [x] Read Model
- [x] Async Command->Query subsystems communication
- [x] Rest API
- [ ] Event Store Rest API 

### Use Cases

- [x] Register
- [x] Change Email
- [ ] Login
- [ ] Logout

### Architecture

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

### PHPStorm integration

Use ssh remote connection.

HOST: 
- docker4mac: `localhost`
- docker machine OR dinghy: `192.168.99.100`

PORT: `2323`

Filesystem mapping:

`{PROJECT_PATH}` -> `/app`

