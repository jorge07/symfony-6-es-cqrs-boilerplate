# Symfony 7 ES CQRS Boilerplate
<!-- ALL-CONTRIBUTORS-BADGE:START - Do not remove or modify this section -->
[![All Contributors](https://img.shields.io/badge/all_contributors-5-orange.svg?style=flat-square)](#contributors-)
<!-- ALL-CONTRIBUTORS-BADGE:END -->

A boilerplate demonstrating **Domain-Driven Design (DDD)**, **Command Query Responsibility Segregation (CQRS)**, and **Event Sourcing** patterns using Symfony 7 and PHP 8.3.

[![push](https://github.com/jorge07/symfony-6-es-cqrs-boilerplate/actions/workflows/push.yml/badge.svg)](https://github.com/jorge07/symfony-6-es-cqrs-boilerplate/actions/workflows/push.yml)

> This is a long living repository that started at v4 and was upgraded to each major version since then. You can find older versions at the following branches:
>
> [symfony-6 branch](https://github.com/jorge07/symfony-6-es-cqrs-boilerplate/tree/symfony-6)
>
> [symfony-5 branch](https://github.com/jorge07/symfony-6-es-cqrs-boilerplate/tree/symfony-5)
>
> [symfony-4 branch](https://github.com/jorge07/symfony-6-es-cqrs-boilerplate/tree/symfony-4)

## Documentation

- [Command and Query Buses](doc/GetStarted/Buses.md)
- [Creating Use Cases](doc/GetStarted/UseCases.md)
- [Adding Projections](doc/GetStarted/Projections.md)
- [Async Processing](doc/GetStarted/Async.md)
- [UI Workflow](doc/Workflow.md)
- [Xdebug Configuration](doc/GetStarted/Xdebug.md)
- [Kubernetes Deployment](doc/Deployment.md)

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

- PHP 8.3
- Symfony 7
- MySQL 8.0
- Elasticsearch & Kibana 7.11.0
- RabbitMQ 3

## Project Setup


|    Action           |     Command      |
|---------------------|------------------|
|  Setup              | `make start`     |
|  Run Tests          | `make phpunit`   |
|  Static Analysis    | `make phpstan`   |
|  Code Style         | `make cs`        |
|  PHP Shell          | `make s=php sh`  |
|  Xdebug             | `make xoff/xon`  |
|  Build Artifacts    | `make artifact`  |

## PHPStorm integration

PHPSTORM has native integration with Docker compose. That's nice but will stop your php container after run the test scenario. That's not nice when using fpm. A solution could be use another container just for that purpose but is way slower and I don't want. For that reason I use ssh connection.

### IMPORTANT

> **ssh in the container it's ONLY for that reason and ONLY in the DEV TAG, if you've ssh installed in your production container, you're doing it wrong...***

[Click here for the detailed instructions about how to setup the PHP remote interpreter in PHPStorm.](https://github.com/jorge07/alpine-php/blob/master/doc/IDE.md)

If you're already familiar with it, here a quick configuration reference:

|    Host          	|    Direction  |
|------------------	|--------------	|
|  Docker 4 mac 	   | `localhost`   |
|  Dinghy       	   | `$ dinghy ip` |

**Port:** `2323`

**Filesystem mapping:** `{PROJECT_PATH}` -> `/app`

### Xdebug

To ease your development process, you can use Xdebug with PHPSTORM.

1. Add a Docker interpreter

   ![Docker PHP interpreter](doc/docker-php-interpreter.png)

2. Enable Xdebug listenning. Don't forget to also activate Xdebug helper from your browser.
   
   ![Xdebug activation](doc/xdebug-activation.png)
   
   Additionally, you can check `Break at first line in PHP scripts` to ensure your debug is working.

3. Make a request from you API at http://127.0.0.1/api/doc for example. You should see this popup:

   ![Xdebug mapping](doc/xdebug-mapping.png)
   
   Click on `Accept` and you should be ready to debug ! Start placing breakpoints on your code and enjoy debugging !

> Note for Windows users:
>
> You might need to update `docker-os=` to `docker-os=windows` in [Makefile](makefile)
> or specify its value on command line like `$ make start docker-os=windows`.

## Contributors ✨

Thanks goes to these wonderful people ([emoji key](https://allcontributors.org/docs/en/emoji-key)):

<!-- ALL-CONTRIBUTORS-LIST:START - Do not remove or modify this section -->
<!-- prettier-ignore-start -->
<!-- markdownlint-disable -->
<table>
  <tr>
    <td align="center"><a href="http://tacon.eu"><img src="https://avatars2.githubusercontent.com/u/2017676?v=4" width="100px;" alt=""/><br /><sub><b>Luis</b></sub></a><br /><a href="https://github.com/jorge07/symfony-6-es-cqrs-boilerplate/commits?author=Lutacon" title="Code">💻</a></td>
    <td align="center"><a href="https://github.com/cv65kr"><img src="https://avatars0.githubusercontent.com/u/9404962?v=4" width="100px;" alt=""/><br /><sub><b>Kajetan</b></sub></a><br /><a href="https://github.com/jorge07/symfony-6-es-cqrs-boilerplate/commits?author=cv65kr" title="Code">💻</a></td>
    <td align="center"><a href="https://coderslab.pl"><img src="https://avatars0.githubusercontent.com/u/2781079?v=4" width="100px;" alt=""/><br /><sub><b>Krzysztof Kowalski</b></sub></a><br /><a href="https://github.com/jorge07/symfony-6-es-cqrs-boilerplate/commits?author=kowalk" title="Code">💻</a></td>
    <td align="center"><a href="http://patryk.it"><img src="https://avatars3.githubusercontent.com/u/17459288?v=4" width="100px;" alt=""/><br /><sub><b>Patryk Woziński</b></sub></a><br /><a href="https://github.com/jorge07/symfony-6-es-cqrs-boilerplate/commits?author=patrykwozinski" title="Code">💻</a></td>
    <td align="center"><a href="https://github.com/jon-ht"><img src="https://avatars3.githubusercontent.com/u/17051512?v=4" width="100px;" alt=""/><br /><sub><b>jon-ht</b></sub></a><br /><a href="https://github.com/jorge07/symfony-6-es-cqrs-boilerplate/commits?author=jon-ht" title="Code">💻</a></td>
  </tr>
</table>

<!-- markdownlint-enable -->
<!-- prettier-ignore-end -->
<!-- ALL-CONTRIBUTORS-LIST:END -->

This project follows the [all-contributors](https://github.com/all-contributors/all-contributors) specification. Contributions of any kind welcome!
