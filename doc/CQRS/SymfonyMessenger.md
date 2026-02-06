# Symfony Messenger Integration

## Overview

This boilerplate uses **Symfony Messenger** as the implementation for Command Bus, Query Bus, and Event Bus. Messenger provides a powerful message handling system with support for routing, middleware, and async processing.

## Bus Configuration

### Three Separate Buses

The application uses three distinct message buses:

```yaml
# config/packages/messenger.yaml
framework:
    messenger:
        default_bus: command.bus
        buses:
            # Command Bus - for write operations
            command.bus:
                middleware:
                    - validation
                    - doctrine_transaction

            # Query Bus - for read operations  
            query.bus:
                middleware:
                    - validation

            # Event Bus - for domain events
            event.bus:
                default_middleware: allow_no_handlers
                middleware:
                    - validation
```

### Bus Implementations

```php
// src/App/Shared/Infrastructure/Bus/Command/MessengerCommandBus.php
class MessengerCommandBus implements CommandBusInterface
{
    public function __construct(
        private readonly MessageBusInterface $messageBus
    ) {}

    public function handle(CommandInterface $command): void
    {
        try {
            $this->messageBus->dispatch($command);
        } catch (HandlerFailedException $e) {
            while ($e instanceof HandlerFailedException) {
                $e = $e->getPrevious();
            }
            throw $e;
        }
    }
}

// src/App/Shared/Infrastructure/Bus/Query/MessengerQueryBus.php
class MessengerQueryBus implements QueryBusInterface
{
    public function __construct(
        private readonly MessageBusInterface $messageBus
    ) {}

    public function ask(QueryInterface $query): mixed
    {
        $envelope = $this->messageBus->dispatch($query);
        $handledStamp = $envelope->last(HandledStamp::class);

        return $handledStamp->getResult();
    }
}
```

## Handler Registration

### Automatic Handler Discovery

Handlers are automatically registered using Symfony's autoconfigure feature:

```yaml
# config/services.yaml
services:
    # Command Handlers
    App\User\Application\Command\:
        resource: '../src/App/User/Application/Command/*Handler.php'
        tags:
            - { name: messenger.message_handler, bus: command.bus }

    # Query Handlers
    App\User\Application\Query\:
        resource: '../src/App/User/Application/Query/*Handler.php'
        tags:
            - { name: messenger.message_handler, bus: query.bus }

    # Event Handlers (Projectors)
    App\User\Infrastructure\ReadModel\Projection\:
        resource: '../src/App/User/Infrastructure/ReadModel/Projection/'
        tags:
            - { name: messenger.message_handler, bus: event.bus }
```

### Manual Handler Registration

For specific configuration needs:

```yaml
services:
    App\User\Application\Command\SignUp\SignUpHandler:
        tags:
            - name: messenger.message_handler
              bus: command.bus
              method: __invoke
              priority: 100
```

## Message Routing

### Simple Routing (Default)

By default, messages are routed to handlers based on type:

```php
// SignUpCommand automatically routes to SignUpHandler
class SignUpCommand implements CommandInterface {}
class SignUpHandler implements CommandHandlerInterface 
{
    public function __invoke(SignUpCommand $command): void {}
}
```

### Custom Routing

For complex routing scenarios:

```yaml
# config/packages/messenger.yaml
framework:
    messenger:
        routing:
            # Route specific commands to different transports
            'App\User\Application\Command\SendEmailCommand': email_transport
            'App\User\Application\Command\GenerateReportCommand': reports_transport
            
            # Route all events to async processing
            'App\*\Domain\Event\*': async_events
```

## Middleware Configuration

### Built-in Middleware

```yaml
framework:
    messenger:
        buses:
            command.bus:
                middleware:
                    # Validation middleware
                    - validation
                    
                    # Doctrine transaction wrapper
                    - doctrine_transaction
                    
                    # Failure handling
                    - failed_message_processing_middleware
                    
                    # Retry failed messages
                    - retry_strategy_middleware
```

### Custom Middleware

```php
// src/App/Shared/Infrastructure/Bus/Middleware/LoggingMiddleware.php
class LoggingMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly LoggerInterface $logger
    ) {}

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $message = $envelope->getMessage();
        
        $this->logger->info('Processing message', [
            'message_type' => get_class($message),
            'message_id' => $envelope->last(UniqueIdStamp::class)?->getUniqueId()
        ]);

        try {
            $envelope = $stack->next()->handle($envelope, $stack);
            
            $this->logger->info('Message processed successfully');
            
            return $envelope;
        } catch (\Throwable $throwable) {
            $this->logger->error('Message processing failed', [
                'exception' => $throwable->getMessage(),
                'trace' => $throwable->getTraceAsString()
            ]);
            
            throw $throwable;
        }
    }
}
```

### Registering Custom Middleware

```yaml
# config/services.yaml
services:
    App\Shared\Infrastructure\Bus\Middleware\LoggingMiddleware:
        tags:
            - { name: messenger.middleware }

# config/packages/messenger.yaml
framework:
    messenger:
        buses:
            command.bus:
                middleware:
                    - App\Shared\Infrastructure\Bus\Middleware\LoggingMiddleware
                    - doctrine_transaction
```

## Async Processing with RabbitMQ

### Transport Configuration

```yaml
# config/packages/messenger.yaml
framework:
    messenger:
        transports:
            # Async events transport
            async_events:
                dsn: '%env(RABBITMQ_URL)%'
                options:
                    exchange:
                        name: 'events'
                        type: topic
                    queues:
                        events: ~
                retry_strategy:
                    max_retries: 3
                    delay: 1000
                    multiplier: 2

        routing:
            # Route all domain events to async processing
            'App\*\Domain\Event\*': async_events
```

### Event Publishing

Events are automatically published when aggregates are saved:

```php
// Domain event is applied
$this->apply(new UserWasCreated($uuid, $credentials, DateTime::now()));

// Repository saves aggregate
$this->userRepository->store($user);

// Broadway publishes events to Symfony Messenger
// Messenger routes events to RabbitMQ based on routing configuration
```

### Consuming Messages

Start workers to process async messages:

```bash
# Process all transports
./bin/console messenger:consume

# Process specific transport
./bin/console messenger:consume async_events

# Run with options
./bin/console messenger:consume async_events --limit=100 --time-limit=3600
```

### Docker Configuration

```yaml
# docker-compose.yml
services:
  worker:
    image: jorge07/alpine-php:8.1-dev-sf
    volumes:
      - .:/app
    command: ['./bin/console', 'messenger:consume', 'async_events']
    depends_on:
      - rabbitmq
      - database
```

## Event Routing Patterns

### Routing Key Generation

Events are automatically routed based on their class name:

```php
// App\User\Domain\Event\UserWasCreated
// becomes routing key: App.User.Domain.Event.UserWasCreated

class AsyncEventPublisher implements EventListener
{
    private function createRoutingKey($event): string
    {
        return str_replace('\\', '.', get_class($event));
    }
}
```

### Consumer Routing Patterns

Different consumers can subscribe to different event patterns:

```yaml
# config/packages/old_sound_rabbit_mq.yaml
old_sound_rabbit_mq:
    connections:
        default:
            host: '%env(RABBITMQ_HOST)%'
            port: '%env(RABBITMQ_PORT)%'
            user: '%env(RABBITMQ_USER)%'
            password: '%env(RABBITMQ_PASSWORD)%'
            vhost: '/'

    multiple_consumers:
        events:
            connection: default
            exchange_options:
                name: 'events'
                type: topic
            queues:
                # All events
                all_events:
                    name: all_events
                    routing_keys: ['#']
                    callback: App\Demo\Infrastructure\Event\Consumer\AllEventsConsumer

                # Only user events  
                user_events:
                    name: user_events
                    routing_keys: ['App.User.Domain.Event.#']
                    callback: App\Demo\Infrastructure\Event\Consumer\UserEventsConsumer

                # Specific event
                user_created:
                    name: user_created
                    routing_keys: ['App.User.Domain.Event.UserWasCreated']
                    callback: App\Demo\Infrastructure\Event\Consumer\UserCreatedConsumer
```

### Consumer Implementation

```php
// src/App/Demo/Infrastructure/Event/Consumer/UserEventsConsumer.php
class UserEventsConsumer implements ConsumerInterface
{
    public function __construct(
        private readonly EventSerializerInterface $eventSerializer,
        private readonly MessageBusInterface $eventBus
    ) {}

    public function execute(AMQPMessage $msg): void
    {
        try {
            // Deserialize event
            $event = $this->eventSerializer->deserialize($msg->body);
            
            // Dispatch to appropriate handlers
            $this->eventBus->dispatch($event);
            
            // Acknowledge message
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        } catch (\Exception $e) {
            // Log error and reject message
            error_log('Failed to process event: ' . $e->getMessage());
            $msg->delivery_info['channel']->basic_nack(
                $msg->delivery_info['delivery_tag'],
                false,
                false // Don't requeue
            );
        }
    }
}
```

## Testing Messenger Integration

### Testing Bus Implementations

```php
class MessengerCommandBusTest extends TestCase
{
    private MessengerCommandBus $commandBus;
    private MessageBusInterface $messageBus;

    protected function setUp(): void
    {
        $this->messageBus = $this->createMock(MessageBusInterface::class);
        $this->commandBus = new MessengerCommandBus($this->messageBus);
    }

    public function testHandlesCommand(): void
    {
        $command = new SignUpCommand($uuid, $credentials);

        $this->messageBus
            ->expects($this->once())
            ->method('dispatch')
            ->with($command);

        $this->commandBus->handle($command);
    }
}
```

### Testing Handlers with Messenger

```php
class SignUpHandlerTest extends TestCase
{
    use ResetDatabase;

    public function testHandlerCanBeInvokedByMessenger(): void
    {
        // Create command
        $command = new SignUpCommand($uuid, $credentials);

        // Dispatch through real messenger
        $this->commandBus->handle($command);

        // Verify user was created
        $user = $this->userRepository->get($uuid);
        $this->assertEquals($credentials->email, $user->email());
    }
}
```

### Testing Async Processing

```php
class AsyncEventProcessingTest extends TestCase
{
    public function testEventsArePublishedAsynchronously(): void
    {
        // Create user (this will generate events)
        $command = new SignUpCommand($uuid, $credentials);
        $this->commandBus->handle($command);

        // Verify event was published to transport
        $transport = $this->getContainer()->get('messenger.transport.async_events');
        $envelopes = $transport->get();
        
        $this->assertCount(1, $envelopes);
        $this->assertInstanceOf(UserWasCreated::class, $envelopes[0]->getMessage());
    }

    public function testEventConsumer(): void
    {
        // Create and publish event
        $event = new UserWasCreated($uuid, $credentials, DateTime::now());
        $this->eventBus->dispatch($event);

        // Process async events
        $this->runCommand('messenger:consume async_events --limit=1');

        // Verify projection was updated
        $userView = $this->userReadRepository->find($uuid);
        $this->assertNotNull($userView);
        $this->assertEquals($credentials->email, $userView->email);
    }
}
```

## Debugging and Monitoring

### Debug Commands

```bash
# List all configured buses
./bin/console debug:messenger

# List handlers for a specific bus
./bin/console debug:messenger command.bus

# Show message routing
./bin/console messenger:debug

# Check failed messages
./bin/console messenger:failed:show

# Retry failed messages
./bin/console messenger:failed:retry
```

### Monitoring Message Processing

```php
class MessageStatsMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly MetricsCollectorInterface $metrics
    ) {}

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $message = $envelope->getMessage();
        $messageType = get_class($message);
        $startTime = microtime(true);

        try {
            $result = $stack->next()->handle($envelope, $stack);
            
            $this->metrics->increment('messenger.messages.processed', [
                'type' => $messageType,
                'status' => 'success'
            ]);

            return $result;
        } catch (\Throwable $throwable) {
            $this->metrics->increment('messenger.messages.processed', [
                'type' => $messageType,
                'status' => 'failed'
            ]);

            throw $throwable;
        } finally {
            $duration = microtime(true) - $startTime;
            $this->metrics->histogram('messenger.messages.duration', $duration, [
                'type' => $messageType
            ]);
        }
    }
}
```

### Health Checks

```php
class MessengerHealthCheck implements HealthCheckInterface
{
    public function check(): HealthStatus
    {
        try {
            // Check if transports are accessible
            $transport = $this->container->get('messenger.transport.async_events');
            $transport->get(); // Try to fetch messages
            
            return HealthStatus::healthy('Messenger transports are accessible');
        } catch (\Exception $e) {
            return HealthStatus::unhealthy('Messenger transport error: ' . $e->getMessage());
        }
    }
}
```

## Best Practices

### 1. **Use Appropriate Bus for Message Type**

```php
// Commands - change state
$this->commandBus->handle(new SignUpCommand($uuid, $credentials));

// Queries - retrieve data  
$user = $this->queryBus->ask(new FindByEmailQuery($email));

// Events - notify about changes
$this->eventBus->dispatch(new UserWasCreated($uuid, $credentials, DateTime::now()));
```

### 2. **Configure Retry Strategies**

```yaml
framework:
    messenger:
        transports:
            async_events:
                retry_strategy:
                    max_retries: 3
                    delay: 1000
                    multiplier: 2
                    max_delay: 10000
```

### 3. **Handle Failures Gracefully**

```php
class RobustEventConsumer implements ConsumerInterface
{
    public function execute(AMQPMessage $msg): void
    {
        try {
            $this->processEvent($msg);
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        } catch (TemporaryException $e) {
            // Requeue for retry
            $msg->delivery_info['channel']->basic_nack(
                $msg->delivery_info['delivery_tag'],
                false,
                true // Requeue
            );
        } catch (PermanentException $e) {
            // Don't requeue, log error
            $this->logger->error('Permanent error processing message', [
                'error' => $e->getMessage(),
                'message' => $msg->body
            ]);
            
            $msg->delivery_info['channel']->basic_nack(
                $msg->delivery_info['delivery_tag'],
                false,
                false // Don't requeue
            );
        }
    }
}
```

### 4. **Monitor Performance**

Set up monitoring for:
- Message processing rates
- Queue depths
- Processing latency
- Error rates
- Failed message counts

Symfony Messenger provides a robust foundation for implementing CQRS patterns with excellent support for async processing, retry mechanisms, and monitoring capabilities.