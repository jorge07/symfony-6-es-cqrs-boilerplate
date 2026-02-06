---
title: Event Sourcing
slug: /cqrs/event-sourcing
---

# Event Sourcing

## What is Event Sourcing?

**Event Sourcing** is a pattern where we store all changes to application state as a sequence of events. Instead of storing just the current state, we store the events that led to that state. The current state is derived by replaying all events from the beginning.

## Traditional vs Event Sourcing

### Traditional Approach

```sql
-- Store current state
CREATE TABLE users (
    id UUID PRIMARY KEY,
    email VARCHAR(255),
    password_hash VARCHAR(255),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- When email changes, we update the record
UPDATE users SET email = 'new@example.com', updated_at = NOW() WHERE id = '123';
-- Previous email is lost forever
```

### Event Sourcing Approach

```sql
-- Store events that caused state changes
CREATE TABLE events (
    id UUID PRIMARY KEY,
    aggregate_id UUID,
    event_type VARCHAR(255),
    event_data JSON,
    occurred_on TIMESTAMP,
    version INTEGER
);

-- Events are never updated, only appended
INSERT INTO events (aggregate_id, event_type, event_data, occurred_on, version) VALUES
('123', 'UserWasCreated', '{"email": "test@example.com", "password": "hash"}', NOW(), 1),
('123', 'UserEmailChanged', '{"email": "new@example.com"}', NOW(), 2);

-- Current state is derived by replaying events
```

## Implementation in This Boilerplate

### Event Store

This boilerplate uses Broadway Event Store:

```php
// Events are stored automatically when aggregates are saved
$user = User::create($uuid, $credentials, $uniqueEmailSpec);
$user->changeEmail($newEmail, $uniqueEmailSpec);

// This saves all uncommitted events to the event store
$this->userRepository->store($user);
```

### Domain Events

Events represent what happened in the domain:

```php
// src/App/User/Domain/Event/UserWasCreated.php
class UserWasCreated implements EventInterface
{
    public function __construct(
        public readonly UuidInterface $uuid,
        public readonly Credentials $credentials,
        public readonly DateTime $occurredOn
    ) {}
}

// src/App/User/Domain/Event/UserEmailChanged.php
class UserEmailChanged implements EventInterface
{
    public function __construct(
        public readonly UuidInterface $uuid,
        public readonly Email $email,
        public readonly DateTime $occurredOn
    ) {}
}

// src/App/User/Domain/Event/UserSignedIn.php
class UserSignedIn implements EventInterface
{
    public function __construct(
        public readonly UuidInterface $uuid,
        public readonly Email $email
    ) {}
}
```

### Aggregate as Event Source

The User aggregate extends `EventSourcedAggregateRoot`:

```php
// src/App/User/Domain/User.php
class User extends EventSourcedAggregateRoot
{
    public static function create(
        UuidInterface $uuid,
        Credentials $credentials,
        UniqueEmailSpecificationInterface $uniqueEmailSpecification
    ): self {
        $uniqueEmailSpecification->isUnique($credentials->email);

        $user = new self();
        // Apply event - this records it for persistence
        $user->apply(new UserWasCreated($uuid, $credentials, DateTime::now()));

        return $user;
    }

    public function changeEmail(
        Email $email,
        UniqueEmailSpecificationInterface $uniqueEmailSpecification
    ): void {
        $uniqueEmailSpecification->isUnique($email);
        // Apply event - this records it for persistence
        $this->apply(new UserEmailChanged($this->uuid, $email, DateTime::now()));
    }

    // Event application methods rebuild state from events
    protected function applyUserWasCreated(UserWasCreated $event): void
    {
        $this->uuid = $event->uuid;
        $this->email = $event->credentials->email;
        $this->hashedPassword = $event->credentials->password;
        $this->createdAt = $event->occurredOn;
    }

    protected function applyUserEmailChanged(UserEmailChanged $event): void
    {
        $this->email = $event->email;
        $this->updatedAt = $event->occurredOn;
    }
}
```

### Repository Implementation

The repository loads aggregates from events:

```php
// src/App/User/Infrastructure/Repository/UserStore.php
class UserStore implements UserRepositoryInterface
{
    public function get(UuidInterface $uuid): User
    {
        try {
            // Broadway loads events and rebuilds aggregate
            return $this->repository->load($uuid->toString());
        } catch (AggregateNotFoundException $e) {
            throw UserNotFoundException::withId($uuid);
        }
    }

    public function store(User $user): void
    {
        // Broadway saves uncommitted events to event store
        $this->repository->save($user);
    }
}
```

## Benefits of Event Sourcing

### 1. **Complete Audit Trail**

Every change is recorded with timestamp and context:

```php
// Can answer questions like:
// - What was the user's email on specific date?
// - How many times has this user changed their email?
// - When did this user last sign in?

$events = $this->eventStore->load($userId);
foreach ($events as $event) {
    echo sprintf(
        "%s: %s at %s\n",
        $event->getType(),
        json_encode($event->getPayload()),
        $event->getRecordedOn()->format('Y-m-d H:i:s')
    );
}
```

### 2. **Temporal Queries**

Reconstruct state at any point in time:

```php
class UserAtTimeQuery
{
    public function getUserAt(UuidInterface $userId, DateTime $atTime): User
    {
        $events = $this->eventStore->load($userId);
        
        // Filter events up to specific time
        $eventsUntil = array_filter($events, fn($event) => 
            $event->getRecordedOn() <= $atTime
        );
        
        // Rebuild aggregate from filtered events
        return User::fromHistory($eventsUntil);
    }
}
```

### 3. **Event Replay and Projections**

Build different read models from same events:

```php
// Email projection
class EmailProjector extends Projector
{
    protected function applyUserWasCreated(UserWasCreated $event): void
    {
        $this->emailRepository->add(new EmailView(
            $event->uuid,
            $event->credentials->email,
            $event->occurredOn
        ));
    }

    protected function applyUserEmailChanged(UserEmailChanged $event): void
    {
        $this->emailRepository->updateEmail(
            $event->uuid,
            $event->email,
            $event->occurredOn
        );
    }
}

// Statistics projection  
class UserStatsProjector extends Projector
{
    protected function applyUserWasCreated(UserWasCreated $event): void
    {
        $this->statsRepository->incrementUserCount();
        $this->statsRepository->recordRegistration($event->occurredOn);
    }

    protected function applyUserSignedIn(UserSignedIn $event): void
    {
        $this->statsRepository->recordSignIn($event->uuid);
    }
}
```

### 4. **Business Intelligence**

Rich data for analytics:

```php
// Analyze user behavior patterns
class UserBehaviorAnalyzer
{
    public function analyzeEmailChangePatterns(): array
    {
        $emailChangeEvents = $this->eventStore->findByType('UserEmailChanged');
        
        return [
            'frequency' => $this->calculateChangeFrequency($emailChangeEvents),
            'timing' => $this->analyzeChangeTiming($emailChangeEvents),
            'domains' => $this->analyzeDomainMigration($emailChangeEvents)
        ];
    }
}
```

## Event Store Structure

### Event Schema

```sql
CREATE TABLE events (
    id UUID PRIMARY KEY,
    uuid VARCHAR(36) NOT NULL,        -- Aggregate ID
    playhead INT NOT NULL,            -- Event sequence number
    metadata LONGTEXT NOT NULL,       -- Event metadata
    payload LONGTEXT NOT NULL,        -- Event data
    recorded_on VARCHAR(32) NOT NULL, -- Timestamp
    type VARCHAR(255) NOT NULL,       -- Event type

    UNIQUE KEY UNIQ_5387574A2B36786BAF50CAA (uuid, playhead)
);
```

### Event Structure

```json
{
  "id": "550e8400-e29b-41d4-a716-446655440000",
  "uuid": "123e4567-e89b-12d3-a456-426614174000",
  "playhead": 1,
  "type": "App.User.Domain.Event.UserWasCreated",
  "payload": {
    "uuid": "123e4567-e89b-12d3-a456-426614174000",
    "credentials": {
      "email": "test@example.com",
      "password": "$2y$10$..."
    },
    "occurredOn": "2023-01-01T00:00:00.000000+00:00"
  },
  "metadata": {
    "commandId": "550e8400-e29b-41d4-a716-446655440001",
    "userId": "admin",
    "timestamp": "2023-01-01T00:00:00.000000+00:00"
  },
  "recorded_on": "2023-01-01T00:00:00.000000+00:00"
}
```

## Event Versioning

As your domain evolves, event schemas may need to change:

### Versioned Events

```php
// Version 1
class UserWasCreatedV1 implements EventInterface
{
    public function __construct(
        public readonly UuidInterface $uuid,
        public readonly string $email,
        public readonly string $password
    ) {}
}

// Version 2 - added timestamp
class UserWasCreatedV2 implements EventInterface
{
    public function __construct(
        public readonly UuidInterface $uuid,
        public readonly Credentials $credentials,
        public readonly DateTime $occurredOn
    ) {}
}
```

### Event Upcasting

Convert old events to new format when loading:

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

        return $event;
    }
}
```

## Snapshots

For aggregates with many events, use snapshots to improve performance:

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
}

class UserStore implements UserRepositoryInterface
{
    public function get(UuidInterface $uuid): User
    {
        // Try to load from snapshot first
        $snapshot = $this->snapshotStore->get($uuid);
        
        if ($snapshot) {
            // Load only events after snapshot
            $events = $this->eventStore->loadFromPlayhead($uuid, $snapshot->version);
            return User::fromSnapshot($snapshot, $events);
        }

        // Fallback to loading all events
        return $this->repository->load($uuid->toString());
    }
}
```

## Testing Event Sourced Aggregates

### Testing Event Application

```php
class UserTest extends TestCase
{
    public function testApplyUserWasCreatedEvent(): void
    {
        $event = new UserWasCreated($uuid, $credentials, $occurredOn);
        
        $user = new User();
        $user->apply($event);
        
        $this->assertEquals($uuid->toString(), $user->uuid());
        $this->assertEquals($credentials->email->toString(), $user->email());
    }
}
```

### Testing Business Logic with Events

```php
class UserTest extends TestCase
{
    public function testUserCanChangeEmail(): void
    {
        // Arrange
        $user = User::create($uuid, $credentials, $uniqueEmailSpec);
        $newEmail = Email::fromString('new@example.com');
        
        // Act
        $user->changeEmail($newEmail, $uniqueEmailSpec);
        
        // Assert
        $events = $user->getUncommittedEvents();
        $this->assertCount(2, $events); // UserWasCreated + UserEmailChanged
        
        $emailChangedEvent = $events[1];
        $this->assertInstanceOf(UserEmailChanged::class, $emailChangedEvent);
        $this->assertEquals($newEmail, $emailChangedEvent->email);
    }
}
```

### Testing Event Store Integration

```php
class UserStoreTest extends TestCase
{
    public function testCanStoreAndRetrieveUser(): void
    {
        // Create user
        $user = User::create($uuid, $credentials, $uniqueEmailSpec);
        
        // Store events
        $this->userStore->store($user);
        
        // Retrieve and verify
        $retrievedUser = $this->userStore->get($uuid);
        
        $this->assertEquals($user->uuid(), $retrievedUser->uuid());
        $this->assertEquals($user->email(), $retrievedUser->email());
    }
}
```

## Common Pitfalls

### 1. **Don't Change Events**

Events are immutable facts - never change them:

```php
// ❌ Never do this
class UserWasCreated
{
    public function setEmail(Email $email): void // ❌ Don't add setters
    {
        $this->email = $email;
    }
}

// ✅ Events are immutable
class UserWasCreated
{
    public function __construct(
        public readonly UuidInterface $uuid,      // readonly
        public readonly Credentials $credentials  // readonly
    ) {}
}
```

### 2. **Handle Large Event Streams**

Use snapshots for aggregates with many events:

```php
// ❌ Loading 10,000 events every time is slow
$user = $this->repository->load($uuid); // Loads all events

// ✅ Use snapshots for performance
$user = $this->repository->loadFromSnapshot($uuid); // Snapshot + recent events
```

### 3. **Design Events for Multiple Consumers**

Events should contain all information needed by consumers:

```php
// ❌ Missing context
class UserEmailChanged
{
    public function __construct(
        public readonly Email $email // Not enough info
    ) {}
}

// ✅ Rich events with context
class UserEmailChanged
{
    public function __construct(
        public readonly UuidInterface $uuid,     // Who
        public readonly Email $email,            // What
        public readonly Email $previousEmail,    // From what
        public readonly DateTime $occurredOn     // When
    ) {}
}
```

Event Sourcing provides powerful capabilities for audit, analytics, and temporal queries, but requires careful design and consideration of the additional complexity it introduces.