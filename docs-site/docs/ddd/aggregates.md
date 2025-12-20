---
title: Aggregates and Aggregate Root
slug: /ddd/aggregates
---

# Aggregates and Aggregate Root

## What is an Aggregate?

An **Aggregate** is a cluster of associated objects that we treat as a unit for data changes. Each aggregate has a boundary and a root. The boundary defines what is inside the aggregate. The root is a single, specific entity contained in the aggregate.

## Aggregate Root

The **Aggregate Root** is the only member of the aggregate that outside objects are allowed to hold references to. This ensures that all changes to the aggregate go through the root, maintaining consistency and enforcing business rules.

## Implementation in This Codebase

### User Aggregate

The `User` class is our main example of an Aggregate Root:

```php
// src/App/User/Domain/User.php
class User extends EventSourcedAggregateRoot
{
    private UuidInterface $uuid;                    // Identity
    private Email $email;                          // Value Object
    private HashedPassword $hashedPassword;        // Value Object
    private ?DateTime $createdAt = null;          // Value Object
    private ?DateTime $updatedAt = null;          // Value Object

    // Factory method - only way to create a User
    public static function create(
        UuidInterface $uuid,
        Credentials $credentials,
        UniqueEmailSpecificationInterface $uniqueEmailSpecification
    ): self {
        $uniqueEmailSpecification->isUnique($credentials->email);
        
        $user = new self();
        $user->apply(new UserWasCreated($uuid, $credentials, DateTime::now()));
        
        return $user;
    }

    // Business operations that maintain aggregate consistency
    public function changeEmail(
        Email $email,
        UniqueEmailSpecificationInterface $uniqueEmailSpecification
    ): void {
        $uniqueEmailSpecification->isUnique($email);
        $this->apply(new UserEmailChanged($this->uuid, $email, DateTime::now()));
    }

    public function signIn(string $plainPassword): void
    {
        if (!$this->hashedPassword->match($plainPassword)) {
            throw new InvalidCredentialsException('Invalid credentials entered.');
        }

        $this->apply(new UserSignedIn($this->uuid, $this->email));
    }
}
```

### Aggregate Characteristics

#### 1. **Consistency Boundary**

All business rules are enforced within the aggregate:

```php
public function changeEmail(Email $email, UniqueEmailSpecificationInterface $spec): void
{
    // Business rule: email must be unique
    $spec->isUnique($email);
    
    // State change with domain event
    $this->apply(new UserEmailChanged($this->uuid, $email, DateTime::now()));
}
```

#### 2. **Transactional Boundary**

Each aggregate is saved as a single transaction:

```php
// In the application layer
public function handle(ChangeEmailCommand $command): void
{
    $user = $this->userRepository->get($command->uuid);
    $user->changeEmail($command->email, $this->uniqueEmailSpecification);
    
    // This saves the entire aggregate atomically
    $this->userRepository->store($user);
}
```

#### 3. **Event Source**

The aggregate root publishes domain events:

```php
protected function apply($event): void
{
    // Record the event for later publishing
    $this->recordThat($event);
    
    // Apply the event to update aggregate state
    $this->applyEvent($event);
}
```

## Aggregate Design Rules

### 1. **Reference by ID Only**

Aggregates should reference other aggregates only by their ID:

```php
class Order extends EventSourcedAggregateRoot
{
    private UuidInterface $customerId;  // Reference by ID, not object
    
    // Not this:
    // private Customer $customer;  // ❌ Direct reference
}
```

### 2. **Enforce Invariants**

Business rules must always be satisfied within the aggregate:

```php
public function changeEmail(Email $email, UniqueEmailSpecificationInterface $spec): void
{
    // Invariant: email must be unique
    $spec->isUnique($email);
    
    // Invariant: email must be valid (enforced by Email value object)
    $this->apply(new UserEmailChanged($this->uuid, $email, DateTime::now()));
}
```

### 3. **Small and Focused**

Keep aggregates small and focused on a single responsibility:

```php
// Good: User aggregate focused on user identity
class User extends EventSourcedAggregateRoot
{
    // User-specific properties and behavior
}

// Avoid: Large aggregate with multiple responsibilities
class UserOrderCustomer extends EventSourcedAggregateRoot
{
    // ❌ Too many responsibilities
}
```

## Event Sourcing and Aggregates

This boilerplate uses Event Sourcing, where aggregates are rebuilt from events:

### Event Application

```php
// Events are applied to rebuild aggregate state
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
```

### Event Store Integration

```php
// Repository loads aggregate from events
public function get(UuidInterface $uuid): User
{
    $eventStream = $this->eventStore->load($uuid->toString());
    
    return User::fromHistory($eventStream);
}

// Repository saves new events
public function store(User $user): void
{
    $events = $user->getUncommittedEvents();
    
    $this->eventStore->append($user->getAggregateRootId(), $events);
}
```

## Aggregate Design Patterns

### 1. **Factory Methods**

Use static factory methods for aggregate creation:

```php
public static function create(
    UuidInterface $uuid,
    Credentials $credentials,
    UniqueEmailSpecificationInterface $uniqueEmailSpecification
): self {
    // Validate business rules
    $uniqueEmailSpecification->isUnique($credentials->email);
    
    // Create and initialize aggregate
    $user = new self();
    $user->apply(new UserWasCreated($uuid, $credentials, DateTime::now()));
    
    return $user;
}
```

### 2. **Domain Services for Complex Rules**

Use domain services for rules that span multiple aggregates:

```php
// Domain service interface
interface UniqueEmailSpecificationInterface
{
    public function isUnique(Email $email): void;
}

// Used in aggregate
public function changeEmail(Email $email, UniqueEmailSpecificationInterface $spec): void
{
    $spec->isUnique($email);  // Domain service validates across aggregates
    $this->apply(new UserEmailChanged($this->uuid, $email, DateTime::now()));
}
```

### 3. **Snapshot Pattern**

For aggregates with many events, use snapshots:

```php
// Not implemented in this boilerplate, but conceptually:
class User extends EventSourcedAggregateRoot
{
    public function createSnapshot(): UserSnapshot
    {
        return new UserSnapshot(
            $this->uuid,
            $this->email,
            $this->hashedPassword,
            $this->version
        );
    }
    
    public static function fromSnapshot(UserSnapshot $snapshot): self
    {
        // Rebuild from snapshot instead of all events
    }
}
```

## Testing Aggregates

### Unit Testing

Test aggregate behavior in isolation:

```php
class UserTest extends TestCase
{
    public function testUserCanChangeEmail(): void
    {
        // Arrange
        $user = $this->createUser();
        $newEmail = Email::fromString('new@example.com');
        $uniqueSpec = $this->createMock(UniqueEmailSpecificationInterface::class);
        $uniqueSpec->expects($this->once())->method('isUnique');

        // Act
        $user->changeEmail($newEmail, $uniqueSpec);

        // Assert
        $this->assertEquals('new@example.com', $user->email());
        
        // Check events were recorded
        $events = $user->getUncommittedEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(UserEmailChanged::class, $events[0]);
    }
}
```

### Integration Testing

Test aggregate with real infrastructure:

```php
class UserRepositoryTest extends TestCase
{
    public function testUserCanBeStoredAndRetrieved(): void
    {
        // Create user
        $user = User::create($uuid, $credentials, $uniqueSpec);
        
        // Store
        $this->userRepository->store($user);
        
        // Retrieve
        $retrievedUser = $this->userRepository->get($uuid);
        
        // Verify
        $this->assertEquals($user->uuid(), $retrievedUser->uuid());
        $this->assertEquals($user->email(), $retrievedUser->email());
    }
}
```

## Aggregate Guidelines

### Do's ✅

1. **Keep aggregates small** - Focus on single responsibility
2. **Reference by ID** - Don't hold direct references to other aggregates
3. **Enforce invariants** - Validate business rules within aggregate
4. **Use value objects** - Compose aggregates with value objects
5. **Publish events** - Notify other parts of the system about changes

### Don'ts ❌

1. **Don't modify multiple aggregates** in a single transaction
2. **Don't expose internal state** directly
3. **Don't create complex object graphs** within aggregates
4. **Don't bypass the aggregate root** for modifications
5. **Don't ignore business rules** for performance

## Advanced Patterns

### Saga Pattern (Process Manager)

For operations spanning multiple aggregates:

```php
// Future implementation for complex workflows
class UserRegistrationSaga
{
    public function handle(UserWasCreated $event): void
    {
        // Create user profile in another aggregate
        // Send welcome email
        // Initialize user preferences
    }
}
```

### Eventual Consistency

Accept that consistency between aggregates is eventual:

```php
// User aggregate publishes event
$this->apply(new UserEmailChanged($uuid, $email, DateTime::now()));

// Email service eventually processes the event
class EmailService
{
    public function handle(UserEmailChanged $event): void
    {
        // Update email in mailing system (eventually)
    }
}
```

This aggregate pattern ensures data consistency, encapsulates business logic, and provides clear boundaries for complex domain operations.