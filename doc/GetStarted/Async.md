# Async Jobs

All events are published in RabbitMQ through `App\Infrastructure\Shared\Event\Publisher\AsyncEventPublisher`. The reason of this is that others can consume this events in background.

#### How it works?

The `AsyncEventPublisher` implements 2 important interfaces.

- `Broadway\EventHandling\EventListener`
	- It binds this class to the **EventBus** and invoke method `handle` that collect the events in memory inside the class.
- `Symfony\Component\EventDispatcher\EventSubscriberInterface`
	- This binds the class to **{KernelEvents,ConsoleEvents}::TERMINATE** Symfony events and invoke method `publish`

By that way we're sending the messages to RabbitMQ after respond to the client so we don't lock the client for things not required to wait.

#### Consume this events

Create you own consumer:
```php
<?php

declare(strict_types=1);

namespace App\Demo\Infrastructure\Event\Consumer;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class DemoEventsConsumer implements ConsumerInterface
{
    public function execute(AMQPMessage $msg): void
    {
        var_dump(unserialize($msg->body));
    }
}
```

#### Configure you consumer:

```yaml
old_sound_rabbit_mq:
	...
    multiple_consumers:
        events:
            ....
            queues:
                 ....
+                var_dump_all_events:
+                    name: var_dump_all_events
+                    routing_keys:
+                        - 'App.Domain.#'
+                    callback: App\Demo\Infrastructure\Event\Consumer\DemoEventsConsumer
```

### Running the Consumer

By default all consumers are invoked with container:

`docker-compose.yml`
```yaml
  workers:
    image: jorge07/alpine-php:7.2-dev-sf
    volumes:
      - .:/app
    command: ['/app/bin/console', 'rabbitmq:multiple-consumer', 'events']
```

**To run just our new consumer:** Inside docker container:
`./bin/console rabbitmq:consumer var_dump_all_events`

Full doc with much better example here: https://github.com/php-amqplib/RabbitMqBundle

#### Routing keys

So simple, it replaces namespaces `\` for `.`, example:

`App\User\Domain\Event\UserWasCreated` -> `App.User.Domain.Event.UserWasCreated`

You can bind you consumer to:
 
 - All events: `#` 
 - All domain events: `#.Domain.#`
 - All domain context boundary events: `#.User.Domain.#`
 - A one particular event: `App.User.Domain.Event.UserWasCreated`
 - Combination of keys:
    - `App.User.Domain.#`
    - `App.Payments.Domain.#`
    - `App.Cart.Domain.Event.OrderWasCreated`
    - `App.Cart.Domain.Event.OrderWasCanceled`
 
 Much better explained in the official documentation: https://www.rabbitmq.com/tutorials/tutorial-five-python.html
