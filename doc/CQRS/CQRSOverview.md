# CQRS Overview

## What is CQRS?

**Command Query Responsibility Segregation (CQRS)** is a pattern that separates read and write operations for a data store. It suggests using different models to update information than the model you use to read information.

## Why Use CQRS?

### 1. **Optimized for Different Needs**

Read and write operations have different requirements:

- **Writes**: Focus on business rules, consistency, and validation
- **Reads**: Focus on performance, denormalization, and user experience

### 2. **Scalability**

Scale read and write sides independently:

```yaml
# Different scaling strategies
write_side:
  replicas: 2
  resources: "high CPU for business logic"
  
read_side:
  replicas: 10
  resources: "high memory for caching"
```

### 3. **Performance**

Optimize each side for its specific use case:

- **Write side**: Complex business logic, event sourcing
- **Read side**: Denormalized views, fast queries

### 4. **Team Independence**

Different teams can work on different aspects:

- **Domain team**: Focus on write model and business logic
- **UI team**: Focus on read models and user experience

## Implementation in This Boilerplate

### Command Side (Write Model)

Handles business operations and state changes:

```php
// Command represents intention to change state
class SignUpCommand implements CommandInterface
{
    public function __construct(
        public readonly UuidInterface $uuid,
        public readonly Credentials $credentials
    ) {}
}

// Command Handler contains business logic
class SignUpHandler implements CommandHandlerInterface
{
    public function __invoke(SignUpCommand $command): void
    {
        // Business logic and validation
        $user = User::create(
            $command->uuid,
            $command->credentials,
            $this->uniqueEmailSpecification
        );

        // Persist through repository (event sourcing)
        $this->userRepository->store($user);
    }
}
```

### Query Side (Read Model)

Handles data retrieval and presentation:

```php
// Query represents request for information
class FindByEmailQuery implements QueryInterface
{
    public function __construct(
        public readonly Email $email
    ) {}
}

// Query Handler retrieves data from optimized read model
class FindByEmailHandler implements QueryHandlerInterface
{
    public function __invoke(FindByEmailQuery $query): ?UserView
    {
        // Simple data retrieval from read model
        return $this->userReadModelRepository->oneByEmailOrNull($query->email);
    }
}
```

## Command vs Query Characteristics

### Commands

**Purpose**: Change system state

**Characteristics**:
- Represent business intentions
- Contain data needed for operation
- Should be validated
- May fail due to business rules
- Return void (no data)

```php
// Examples of commands
class SignUpCommand implements CommandInterface
{
    // Data needed to create user
}

class ChangeEmailCommand implements CommandInterface
{
    // Data needed to change email
}

class SignInCommand implements CommandInterface
{
    // Data needed to authenticate
}
```

**Naming Convention**: Use imperative verbs
- `SignUpCommand`
- `ChangeEmailCommand`
- `DeleteUserCommand`

### Queries

**Purpose**: Retrieve data

**Characteristics**:
- Represent information requests
- Should not change system state
- Always return data
- Should be side-effect free
- Can be cached

```php
// Examples of queries
class FindByEmailQuery implements QueryInterface
{
    // Criteria for finding user
}

class GetUserQuery implements QueryInterface
{
    // ID of user to retrieve
}

class GetUsersQuery implements QueryInterface
{
    // Pagination and filtering criteria
}
```

**Naming Convention**: Use descriptive nouns
- `FindByEmailQuery`
- `GetUserQuery`
- `ListUsersQuery`

## Benefits Demonstrated

### 1. **Clear Separation of Concerns**

```php
// Write model focuses on business logic
class User extends EventSourcedAggregateRoot
{
    public function changeEmail(Email $email, UniqueEmailSpecificationInterface $spec): void
    {
        $spec->isUnique($email);
        $this->apply(new UserEmailChanged($this->uuid, $email, DateTime::now()));
    }
}

// Read model focuses on data presentation
class UserView
{
    public function __construct(
        public readonly string $uuid,
        public readonly string $email,
        public readonly string $createdAt,
        public readonly ?string $updatedAt
    ) {}
}
```

### 2. **Independent Evolution**

Write and read models can evolve independently:

```php
// Write model can add complex business rules
class User extends EventSourcedAggregateRoot
{
    public function suspendForSecurityReasons(SecurityPolicy $policy): void
    {
        $policy->validateSuspension($this);
        $this->apply(new UserSuspended($this->uuid, DateTime::now()));
    }
}

// Read model can add UI-specific optimizations
class UserView
{
    public function __construct(
        public readonly string $uuid,
        public readonly string $email,
        public readonly string $displayName,      // UI optimization
        public readonly string $avatarUrl,        // UI optimization
        public readonly bool $isOnline           // UI optimization
    ) {}
}
```

### 3. **Performance Optimization**

Different storage strategies for different needs:

```yaml
# Write side: Event store for business logic
user_write_model:
  storage: event_store
  consistency: strong
  features: [business_rules, audit_trail, temporal_queries]

# Read side: Optimized for queries
user_read_model:
  storage: elasticsearch
  consistency: eventual
  features: [fast_search, aggregations, caching]
```

## CQRS Patterns in This Codebase

### 1. **Simple CQRS**

Basic separation without event sourcing:

```php
// Command updates database directly
class UpdateUserHandler
{
    public function handle(UpdateUserCommand $command): void
    {
        $user = $this->userRepository->find($command->id);
        $user->update($command->data);
        $this->userRepository->save($user);
    }
}

// Query reads from same database
class GetUserHandler
{
    public function handle(GetUserQuery $query): UserView
    {
        return $this->userReadRepository->find($query->id);
    }
}
```

### 2. **CQRS with Event Sourcing** (Current Implementation)

Commands create events, queries read from projections:

```php
// Command creates events
class SignUpHandler
{
    public function handle(SignUpCommand $command): void
    {
        $user = User::create($command->uuid, $command->credentials, $this->spec);
        $this->userRepository->store($user); // Stores events
    }
}

// Events are projected to read models
class UserProjector extends Projector
{
    protected function applyUserWasCreated(UserWasCreated $event): void
    {
        $readModel = new UserView(
            $event->uuid->toString(),
            $event->credentials->email->toString(),
            $event->occurredOn->toString()
        );
        
        $this->repository->save($readModel);
    }
}

// Query reads from projection
class FindByEmailHandler
{
    public function handle(FindByEmailQuery $query): ?UserView
    {
        return $this->readRepository->findByEmail($query->email);
    }
}
```

## Common CQRS Misconceptions

### ❌ **"CQRS Requires Event Sourcing"**

CQRS can be implemented without event sourcing:

```php
// Simple CQRS with traditional persistence
class CreateUserHandler
{
    public function handle(CreateUserCommand $command): void
    {
        $user = new User($command->email, $command->password);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        
        // Still CQRS - separate write operation
    }
}
```

### ❌ **"CQRS Requires Separate Databases"**

Can use same database with different models:

```sql
-- Write model table
CREATE TABLE users (
    id UUID PRIMARY KEY,
    email VARCHAR(255),
    password_hash VARCHAR(255),
    created_at TIMESTAMP
);

-- Read model view
CREATE VIEW user_summary AS
SELECT 
    id,
    email,
    created_at,
    'active' as status
FROM users
WHERE deleted_at IS NULL;
```

### ❌ **"CQRS is Always Better"**

CQRS adds complexity - use when benefits justify the cost:

**Use CQRS when**:
- Complex business logic
- Different scalability needs for reads/writes
- Performance requirements differ significantly
- Multiple read representations needed

**Don't use CQRS when**:
- Simple CRUD applications
- Small applications
- Tight coupling between reads and writes is acceptable

## Testing CQRS

### Command Testing

```php
class SignUpHandlerTest extends TestCase
{
    public function testCanSignUpUser(): void
    {
        $command = new SignUpCommand($uuid, $credentials);
        
        $this->handler->handle($command);
        
        // Verify business logic was executed
        $user = $this->userRepository->get($uuid);
        $this->assertEquals($credentials->email, $user->email());
    }
}
```

### Query Testing

```php
class FindByEmailHandlerTest extends TestCase
{
    public function testCanFindUserByEmail(): void
    {
        // Arrange: Set up read model
        $userView = new UserView($uuid, $email, $createdAt);
        $this->readRepository->save($userView);
        
        $query = new FindByEmailQuery($email);
        
        // Act
        $result = $this->handler->handle($query);
        
        // Assert
        $this->assertEquals($email, $result->email);
    }
}
```

CQRS provides a powerful foundation for building scalable applications with clear separation between business logic and data access patterns.