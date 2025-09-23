# Event Sourcing Patterns and Best Practices

## Advanced Event Sourcing Patterns

### Event Versioning Strategies

As your domain evolves, event schemas need to change. Here are strategies to handle this evolution.

#### Versioned Events

```php
// Version 1 - Initial implementation
class UserWasCreatedV1 implements EventInterface
{
    public function __construct(
        public readonly UuidInterface $uuid,
        public readonly string $email,
        public readonly string $password
    ) {}
}

// Version 2 - Added timestamp and structured credentials
class UserWasCreatedV2 implements EventInterface
{
    public function __construct(
        public readonly UuidInterface $uuid,
        public readonly Credentials $credentials,
        public readonly DateTime $occurredOn
    ) {}
}

// Version 3 - Added user metadata
class UserWasCreatedV3 implements EventInterface
{
    public function __construct(
        public readonly UuidInterface $uuid,
        public readonly Credentials $credentials,
        public readonly UserMetadata $metadata,
        public readonly DateTime $occurredOn
    ) {}
}
```

#### Event Upcasting

Transform old events to new format when loading:

```php
class UserEventUpcaster implements EventUpcasterInterface
{
    public function upcast(EventInterface $event): EventInterface
    {
        if ($event instanceof UserWasCreatedV1) {
            return new UserWasCreatedV2(
                $event->uuid,
                new Credentials(
                    Email::fromString($event->email),
                    HashedPassword::fromHash($event->password)
                ),
                DateTime::now() // Default timestamp for old events
            );
        }

        if ($event instanceof UserWasCreatedV2) {
            return new UserWasCreatedV3(
                $event->uuid,
                $event->credentials,
                UserMetadata::default(), // Default metadata
                $event->occurredOn
            );
        }

        return $event;
    }
}
```

#### Event Schema Registry

Maintain a registry of event schemas:

```php
class EventSchemaRegistry
{
    private array $schemas = [];

    public function registerSchema(string $eventType, int $version, array $schema): void
    {
        $this->schemas[$eventType][$version] = $schema;
    }

    public function validateEvent(EventInterface $event): void
    {
        $eventType = get_class($event);
        $version = $event->getVersion();
        
        $schema = $this->schemas[$eventType][$version] ?? null;
        
        if (!$schema) {
            throw new UnknownEventSchemaException($eventType, $version);
        }

        // Validate event data against schema
        $this->validateAgainstSchema($event->serialize(), $schema);
    }
}
```

### Snapshot Patterns

For aggregates with many events, snapshots improve performance.

#### Basic Snapshots

```php
class UserSnapshot
{
    public function __construct(
        public readonly UuidInterface $uuid,
        public readonly Email $email,
        public readonly HashedPassword $hashedPassword,
        public readonly DateTime $createdAt,
        public readonly ?DateTime $updatedAt,
        public readonly int $version
    ) {}

    public static function fromAggregate(User $user): self
    {
        return new self(
            $user->getUuid(),
            $user->getEmail(),
            $user->getHashedPassword(),
            $user->getCreatedAt(),
            $user->getUpdatedAt(),
            $user->getVersion()
        );
    }
}

class UserStore implements UserRepositoryInterface
{
    public function get(UuidInterface $uuid): User
    {
        // Try to load from snapshot first
        $snapshot = $this->snapshotStore->get($uuid);
        
        if ($snapshot) {
            // Load only events after snapshot
            $events = $this->eventStore->loadFromPlayhead(
                $uuid->toString(),
                $snapshot->version + 1
            );
            
            return User::fromSnapshot($snapshot, $events);
        }

        // Fallback to loading all events
        return $this->repository->load($uuid->toString());
    }

    public function store(User $user): void
    {
        $this->repository->save($user);

        // Create snapshot every 50 events
        if ($user->getVersion() % 50 === 0) {
            $snapshot = UserSnapshot::fromAggregate($user);
            $this->snapshotStore->save($snapshot);
        }
    }
}
```

#### Automated Snapshot Strategy

```php
class SnapshotStrategy
{
    public function shouldCreateSnapshot(AggregateRoot $aggregate): bool
    {
        $eventCount = $aggregate->getVersion();
        
        // Create snapshot every 100 events
        if ($eventCount % 100 === 0) {
            return true;
        }

        // Or based on time since last snapshot
        $lastSnapshot = $this->snapshotStore->getLastSnapshot($aggregate->getAggregateRootId());
        if ($lastSnapshot && $this->daysSince($lastSnapshot->getCreatedAt()) > 7) {
            return true;
        }

        return false;
    }

    private function daysSince(DateTime $date): int
    {
        return DateTime::now()->diff($date)->days;
    }
}
```

### Process Managers (Sagas)

Handle long-running business processes that span multiple aggregates.

#### Order Processing Saga

```php
class OrderProcessingSaga
{
    private string $orderId;
    private string $customerId;
    private OrderStatus $status;
    private array $completedSteps = [];

    public function handle(OrderWasPlaced $event): void
    {
        $this->orderId = $event->orderId->toString();
        $this->customerId = $event->customerId->toString();
        $this->status = OrderStatus::PLACED;

        // Start the process
        $this->scheduleCommand(new ReserveInventoryCommand($event->orderId, $event->items));
    }

    public function handle(InventoryWasReserved $event): void
    {
        $this->completedSteps[] = 'inventory_reserved';
        
        // Next step
        $this->scheduleCommand(new ProcessPaymentCommand($event->orderId, $event->amount));
    }

    public function handle(PaymentWasProcessed $event): void
    {
        $this->completedSteps[] = 'payment_processed';
        
        // Final step
        $this->scheduleCommand(new ShipOrderCommand($event->orderId));
    }

    public function handle(OrderWasShipped $event): void
    {
        $this->completedSteps[] = 'order_shipped';
        $this->status = OrderStatus::COMPLETED;
        
        // Process completed
        $this->scheduleEvent(new OrderProcessingCompleted($event->orderId));
    }

    // Handle failures
    public function handle(PaymentFailed $event): void
    {
        // Compensate previous actions
        $this->scheduleCommand(new ReleaseInventoryCommand($event->orderId));
        $this->scheduleCommand(new CancelOrderCommand($event->orderId));
    }
}
```

### Event Store Patterns

#### Partitioned Event Store

```php
class PartitionedEventStore implements EventStoreInterface
{
    public function __construct(
        private readonly array $partitions
    ) {}

    public function append(string $aggregateId, DomainEventStream $eventStream): void
    {
        $partition = $this->getPartitionForAggregate($aggregateId);
        $partition->append($aggregateId, $eventStream);
    }

    public function load(string $aggregateId): DomainEventStream
    {
        $partition = $this->getPartitionForAggregate($aggregateId);
        return $partition->load($aggregateId);
    }

    private function getPartitionForAggregate(string $aggregateId): EventStoreInterface
    {
        $hash = crc32($aggregateId);
        $partitionIndex = $hash % count($this->partitions);
        
        return $this->partitions[$partitionIndex];
    }
}
```

#### Event Store with Encryption

```php
class EncryptedEventStore implements EventStoreInterface
{
    public function __construct(
        private readonly EventStoreInterface $eventStore,
        private readonly EncryptionService $encryption
    ) {}

    public function append(string $aggregateId, DomainEventStream $eventStream): void
    {
        $encryptedStream = $this->encryptEventStream($eventStream);
        $this->eventStore->append($aggregateId, $encryptedStream);
    }

    public function load(string $aggregateId): DomainEventStream
    {
        $encryptedStream = $this->eventStore->load($aggregateId);
        return $this->decryptEventStream($encryptedStream);
    }

    private function encryptEventStream(DomainEventStream $stream): DomainEventStream
    {
        $encryptedEvents = [];
        
        foreach ($stream as $event) {
            $serialized = $event->getPayload();
            $encrypted = $this->encryption->encrypt(json_encode($serialized));
            
            $encryptedEvents[] = new DomainMessage(
                $event->getId(),
                $event->getPlayhead(),
                $event->getMetadata(),
                $encrypted,
                $event->getRecordedOn()
            );
        }
        
        return new DomainEventStream($encryptedEvents);
    }
}
```

### Event Replay Patterns

#### Selective Event Replay

```php
class EventReplayService
{
    public function replayEventsForProjection(
        string $projectionName,
        ?DateTime $fromDate = null
    ): void {
        $events = $this->eventStore->loadEventsSince($fromDate);
        $projector = $this->projectorRegistry->get($projectionName);
        
        // Reset projection
        $projector->reset();
        
        foreach ($events as $event) {
            if ($projector->handles($event)) {
                $projector->handle($event);
            }
        }
    }

    public function replayEventsForAggregate(UuidInterface $aggregateId): void
    {
        $events = $this->eventStore->load($aggregateId->toString());
        
        // Rebuild aggregate from events
        $aggregate = $this->aggregateFactory->createFromHistory($events);
        
        // Save current state
        $this->repository->store($aggregate);
    }
}
```

#### Parallel Event Processing

```php
class ParallelEventProcessor
{
    public function processEventsInParallel(array $events): void
    {
        $chunks = array_chunk($events, 100);
        
        $processes = [];
        foreach ($chunks as $chunk) {
            $process = new Process([
                'php', 'bin/console', 'app:process-events',
                '--events=' . base64_encode(serialize($chunk))
            ]);
            
            $process->start();
            $processes[] = $process;
        }
        
        // Wait for all processes to complete
        foreach ($processes as $process) {
            $process->wait();
        }
    }
}
```

### Event Sourcing Anti-Patterns

#### ❌ Mutable Events

```php
// Bad - events should be immutable
class UserWasCreated
{
    public string $email;
    
    public function setEmail(string $email): void // ❌
    {
        $this->email = $email;
    }
}

// Good - immutable events
class UserWasCreated
{
    public function __construct(
        public readonly string $email // ✅
    ) {}
}
```

#### ❌ Logic in Events

```php
// Bad - business logic in events
class UserWasCreated
{
    public function calculateUserLevel(): string // ❌
    {
        // Business logic doesn't belong in events
        return $this->loginCount > 100 ? 'premium' : 'basic';
    }
}

// Good - events are data containers
class UserWasCreated
{
    public function __construct(
        public readonly UuidInterface $uuid,
        public readonly Credentials $credentials,
        public readonly DateTime $occurredOn
    ) {} // ✅
}
```

#### ❌ Large Events

```php
// Bad - events with too much data
class UserWasCreated
{
    public function __construct(
        public readonly array $completeUserProfile // ❌ Too much data
    ) {}
}

// Good - events with essential data only
class UserWasCreated
{
    public function __construct(
        public readonly UuidInterface $uuid,
        public readonly Credentials $credentials,
        public readonly DateTime $occurredOn
    ) {} // ✅
}
```

### Monitoring and Observability

#### Event Store Health Checks

```php
class EventStoreHealthCheck
{
    public function check(): HealthStatus
    {
        try {
            // Check if event store is accessible
            $this->eventStore->loadMetadata();
            
            // Check event store size
            $totalEvents = $this->eventStore->countEvents();
            if ($totalEvents > 10_000_000) {
                return HealthStatus::warning('Event store is very large: ' . $totalEvents);
            }
            
            // Check for recent events
            $recentEvents = $this->eventStore->loadEventsSince(DateTime::now()->sub(new DateInterval('PT1H')));
            if (empty($recentEvents)) {
                return HealthStatus::warning('No events in the last hour');
            }
            
            return HealthStatus::healthy();
        } catch (\Exception $e) {
            return HealthStatus::unhealthy('Event store error: ' . $e->getMessage());
        }
    }
}
```

#### Event Processing Metrics

```php
class EventMetrics
{
    public function recordEventProcessed(EventInterface $event, float $duration): void
    {
        $this->metrics->increment('events.processed', [
            'event_type' => get_class($event)
        ]);
        
        $this->metrics->histogram('events.processing_time', $duration, [
            'event_type' => get_class($event)
        ]);
    }

    public function recordEventFailed(EventInterface $event, \Exception $exception): void
    {
        $this->metrics->increment('events.failed', [
            'event_type' => get_class($event),
            'error_type' => get_class($exception)
        ]);
    }

    public function recordSnapshotCreated(string $aggregateType): void
    {
        $this->metrics->increment('snapshots.created', [
            'aggregate_type' => $aggregateType
        ]);
    }
}
```

### Performance Optimization

#### Event Batching

```php
class BatchEventProcessor
{
    private array $eventBatch = [];
    private int $batchSize;

    public function __construct(int $batchSize = 100)
    {
        $this->batchSize = $batchSize;
    }

    public function addEvent(EventInterface $event): void
    {
        $this->eventBatch[] = $event;
        
        if (count($this->eventBatch) >= $this->batchSize) {
            $this->processBatch();
        }
    }

    public function processBatch(): void
    {
        if (empty($this->eventBatch)) {
            return;
        }

        // Process events in batch for better performance
        $this->eventProcessor->processBatch($this->eventBatch);
        
        $this->eventBatch = [];
    }

    public function __destruct()
    {
        $this->processBatch(); // Process remaining events
    }
}
```

#### Async Event Processing

```php
class AsyncEventProcessor
{
    public function processEventAsync(EventInterface $event): void
    {
        // Queue event for background processing
        $this->messageQueue->publish(new ProcessEventMessage($event));
    }
}

class ProcessEventMessage
{
    public function __construct(
        public readonly EventInterface $event
    ) {}
}

class ProcessEventHandler
{
    public function handle(ProcessEventMessage $message): void
    {
        try {
            $this->eventProcessor->process($message->event);
        } catch (\Exception $e) {
            // Handle failures - maybe retry or dead letter queue
            $this->handleFailure($message->event, $e);
        }
    }
}
```

These patterns and practices help build robust, scalable event-sourced systems that can evolve over time while maintaining data integrity and performance.