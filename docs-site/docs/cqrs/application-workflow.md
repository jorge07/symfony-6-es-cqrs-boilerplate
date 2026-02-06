---
title: Application Workflow
slug: /cqrs/application-workflow
---

# Application Workflow

## Complete Request Flow

This document explains how a typical request flows through the application architecture, from the user interface to the domain and back.

## Request Flow Overview

```
1. User Interface (UI)
2. Command/Query Creation
3. Bus Routing
4. Handler Execution
5. Domain Logic
6. Event Publishing
7. Projection Updates
8. Response
```

## Example: User Sign Up Flow

Let's trace a complete user sign-up request through the system:

### 1. HTTP Request Arrives

```php
// POST /api/users
{
    "email": "john@example.com",
    "password": "securePassword123"
}
```

### 2. REST Controller Handles Request

```php
// src/UI/Http/Rest/Controller/User/SignUpController.php
class SignUpController
{
    public function __invoke(Request $request): JsonResponse
    {
        // Extract data from HTTP request
        $requestData = $request->toArray();
        
        // Create command with domain objects
        $command = new SignUpCommand(
            uuid: Uuid::uuid4(),
            credentials: Credentials::fromArray($requestData)
        );

        // Send command to application layer
        $this->commandBus->handle($command);

        // Return HTTP response
        return new JsonResponse(null, Response::HTTP_CREATED);
    }
}
```

### 3. Command Bus Routes to Handler

```php
// Symfony Messenger routes SignUpCommand to SignUpHandler
// Based on configuration in config/services.yaml
```

### 4. Application Layer Processes Command

```php
// src/App/User/Application/Command/SignUp/SignUpHandler.php
class SignUpHandler implements CommandHandlerInterface
{
    public function __invoke(SignUpCommand $command): void
    {
        // Use domain service to enforce business rules
        $this->uniqueEmailSpecification->isUnique($command->credentials->email);

        // Create domain aggregate
        $user = User::create(
            $command->uuid,
            $command->credentials,
            $this->uniqueEmailSpecification
        );

        // Persist through repository
        $this->userRepository->store($user);
    }
}
```

### 5. Domain Layer Executes Business Logic

```php
// src/App/User/Domain/User.php
class User extends EventSourcedAggregateRoot
{
    public static function create(
        UuidInterface $uuid,
        Credentials $credentials,
        UniqueEmailSpecificationInterface $uniqueEmailSpecification
    ): self {
        // Enforce business rule: email must be unique
        $uniqueEmailSpecification->isUnique($credentials->email);

        $user = new self();
        
        // Apply domain event
        $user->apply(new UserWasCreated($uuid, $credentials, DateTime::now()));

        return $user;
    }

    protected function applyUserWasCreated(UserWasCreated $event): void
    {
        // Update aggregate state
        $this->uuid = $event->uuid;
        $this->email = $event->credentials->email;
        $this->hashedPassword = $event->credentials->password;
        $this->createdAt = $event->occurredOn;
    }
}
```

### 6. Infrastructure Layer Persists Events

```php
// src/App/User/Infrastructure/Repository/UserStore.php
class UserStore implements UserRepositoryInterface
{
    public function store(User $user): void
    {
        // Broadway event sourcing repository
        // 1. Gets uncommitted events from aggregate
        // 2. Saves events to event store
        // 3. Publishes events to event bus
        $this->repository->save($user);
    }
}
```

### 7. Events Are Published Asynchronously

```php
// src/App/Infrastructure/Shared/Event/Publisher/AsyncEventPublisher.php
class AsyncEventPublisher implements EventListener
{
    public function handle($event): void
    {
        // Collect events during request
        $this->collectedEvents[] = $event;
    }

    public function publishCollectedEvents(): void
    {
        // Publish to RabbitMQ after response is sent
        foreach ($this->collectedEvents as $event) {
            $this->eventPublisher->publish($event);
        }
    }
}
```

### 8. Projections Are Updated

```php
// src/App/User/Infrastructure/ReadModel/Projection/UserProjector.php
class UserProjector extends Projector
{
    protected function applyUserWasCreated(UserWasCreated $event): void
    {
        // Create read model for queries
        $userView = new UserView(
            uuid: $event->uuid->toString(),
            email: $event->credentials->email->toString(),
            createdAt: $event->occurredOn->toString()
        );

        $this->repository->save($userView);
    }
}
```

### 9. Response Sent to Client

```http
HTTP/1.1 201 Created
Content-Type: application/json

null
```

## Query Flow Example: Get User by Email

### 1. HTTP Request

```http
GET /api/users/find-by-email/john@example.com
```

### 2. REST Controller

```php
// src/UI/Http/Rest/Controller/User/GetUserByEmailController.php
class GetUserByEmailController
{
    public function __invoke(string $email): JsonResponse
    {
        // Create query
        $query = new FindByEmailQuery(
            Email::fromString($email)
        );

        // Execute query
        $user = $this->queryBus->ask($query);

        if (null === $user) {
            throw new UserNotFoundException();
        }

        return new JsonResponse($user->jsonSerialize());
    }
}
```

### 3. Query Handler

```php
// src/App/User/Application/Query/FindByEmail/FindByEmailHandler.php
class FindByEmailHandler implements QueryHandlerInterface
{
    public function __invoke(FindByEmailQuery $query): ?UserView
    {
        // Direct read from read model
        return $this->userReadModelRepository->oneByEmailOrNull($query->email);
    }
}
```

### 4. Read Model Repository

```php
// src/App/User/Infrastructure/ReadModel/Repository/UserReadModelElasticRepository.php
class UserReadModelElasticRepository implements UserReadModelRepositoryInterface
{
    public function oneByEmailOrNull(Email $email): ?UserView
    {
        // Optimized query against read model
        $result = $this->client->search([
            'index' => 'users',
            'body' => [
                'query' => [
                    'term' => ['email' => $email->toString()]
                ]
            ]
        ]);

        if (empty($result['hits']['hits'])) {
            return null;
        }

        return UserView::fromArray($result['hits']['hits'][0]['_source']);
    }
}
```

### 5. Response

```http
HTTP/1.1 200 OK
Content-Type: application/json

{
    "uuid": "123e4567-e89b-12d3-a456-426614174000",
    "email": "john@example.com",
    "createdAt": "2023-01-01T00:00:00.000000+00:00"
}
```

## Error Handling Flow

### Domain Exception

```php
// Domain throws business rule violation
public function changeEmail(Email $email, UniqueEmailSpecificationInterface $spec): void
{
    if (!$spec->isUnique($email)) {
        throw new EmailAlreadyExistsException($email);
    }
    // ...
}
```

### Application Layer Catches and Transforms

```php
class ChangeEmailHandler
{
    public function __invoke(ChangeEmailCommand $command): void
    {
        try {
            $user = $this->userRepository->get($command->uuid);
            $user->changeEmail($command->email, $this->uniqueEmailSpecification);
            $this->userRepository->store($user);
        } catch (EmailAlreadyExistsException $e) {
            throw new CommandExecutionException(
                'Cannot change email: ' . $e->getMessage(),
                previous: $e
            );
        }
    }
}
```

### UI Layer Handles Application Exceptions

```php
class ChangeEmailController
{
    public function __invoke(Request $request, string $uuid): JsonResponse
    {
        try {
            $command = new ChangeEmailCommand(/* ... */);
            $this->commandBus->handle($command);
            
            return new JsonResponse(null, Response::HTTP_OK);
        } catch (CommandExecutionException $e) {
            return new JsonResponse(
                ['error' => $e->getMessage()],
                Response::HTTP_CONFLICT
            );
        }
    }
}
```

## Async Processing Flow

### Event Publishing

```php
// Events are collected during request processing
$this->apply(new UserWasCreated($uuid, $credentials, DateTime::now()));

// Published after response via Symfony kernel event
class AsyncEventPublisher implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::TERMINATE => 'publishCollectedEvents',
            ConsoleEvents::TERMINATE => 'publishCollectedEvents',
        ];
    }
}
```

### Message Queue Processing

```yaml
# RabbitMQ routing
user.events:
    routing_key: "App.User.Domain.Event.#"
    consumer: user_events_consumer
```

### Background Consumers

```php
// Async event consumer
class UserEventsConsumer implements ConsumerInterface
{
    public function execute(AMQPMessage $msg): void
    {
        $event = $this->eventSerializer->deserialize($msg->body);
        
        // Process event asynchronously
        $this->eventBus->handle($event);
    }
}
```

## Transaction Boundaries

### Write Operations (Commands)

```php
// Each command is wrapped in a transaction
class MessengerCommandBus
{
    public function handle(CommandInterface $command): void
    {
        $this->entityManager->beginTransaction();
        
        try {
            $this->messageBus->dispatch($command);
            $this->entityManager->commit();
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }
}
```

### Read Operations (Queries)

```php
// Queries don't need transactions
class MessengerQueryBus
{
    public function ask(QueryInterface $query): mixed
    {
        // No transaction needed for reads
        $envelope = $this->messageBus->dispatch($query);
        
        return $envelope->last(HandledStamp::class)->getResult();
    }
}
```

## Performance Considerations

### Command Performance

- **Optimize for consistency**: Commands prioritize business rules over speed
- **Keep transactions short**: Minimize time holding locks
- **Defer non-critical work**: Use events for async processing

```php
class SignUpHandler
{
    public function __invoke(SignUpCommand $command): void
    {
        // Critical: User creation (synchronous)
        $user = User::create($command->uuid, $command->credentials, $this->spec);
        $this->userRepository->store($user);
        
        // Non-critical: Welcome email (asynchronous via events)
        // UserWasCreated event will trigger email sending
    }
}
```

### Query Performance

- **Optimize for speed**: Queries prioritize performance over absolute consistency
- **Use read-optimized storage**: ElasticSearch, denormalized tables
- **Cache frequently accessed data**: Redis, in-memory caches

```php
class FindByEmailHandler
{
    public function __invoke(FindByEmailQuery $query): ?UserView
    {
        // Check cache first
        $cached = $this->cache->get("user:email:{$query->email}");
        if ($cached) {
            return UserView::fromArray($cached);
        }
        
        // Fallback to database
        $user = $this->repository->oneByEmailOrNull($query->email);
        
        if ($user) {
            $this->cache->set("user:email:{$query->email}", $user->toArray());
        }
        
        return $user;
    }
}
```

## Monitoring and Observability

### Request Tracing

```php
class LoggingMiddleware implements MiddlewareInterface
{
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $message = $envelope->getMessage();
        $traceId = Uuid::uuid4()->toString();
        
        $this->logger->info('Processing message', [
            'trace_id' => $traceId,
            'message_type' => get_class($message),
            'message_data' => $this->serializer->serialize($message)
        ]);
        
        try {
            $result = $stack->next()->handle($envelope, $stack);
            
            $this->logger->info('Message processed successfully', [
                'trace_id' => $traceId
            ]);
            
            return $result;
        } catch (\Exception $e) {
            $this->logger->error('Message processing failed', [
                'trace_id' => $traceId,
                'error' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}
```

### Metrics Collection

```php
class MetricsMiddleware implements MiddlewareInterface
{
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $message = $envelope->getMessage();
        $startTime = microtime(true);
        
        try {
            $result = $stack->next()->handle($envelope, $stack);
            
            $this->metrics->increment('messages.processed', [
                'type' => get_class($message),
                'status' => 'success'
            ]);
            
            return $result;
        } catch (\Exception $e) {
            $this->metrics->increment('messages.processed', [
                'type' => get_class($message),
                'status' => 'error'
            ]);
            
            throw $e;
        } finally {
            $duration = microtime(true) - $startTime;
            $this->metrics->timing('messages.duration', $duration, [
                'type' => get_class($message)
            ]);
        }
    }
}
```

This workflow demonstrates how the various architectural patterns work together to create a scalable, maintainable, and observable application that cleanly separates concerns while maintaining high performance and reliability.