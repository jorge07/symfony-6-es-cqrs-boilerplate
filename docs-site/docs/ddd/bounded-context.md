---
title: Bounded Context
slug: /ddd/bounded-context
---

# Bounded Context

## What is a Bounded Context?

A Bounded Context is a central pattern in Domain-Driven Design that defines the boundaries within which a particular domain model is defined and applicable. It's a conceptual boundary around a system where the ubiquitous language and domain model are consistent.

## Bounded Contexts in This Boilerplate

### Current Implementation

This boilerplate currently demonstrates one main bounded context:

#### **User Context** (`src/App/User/`)

**Responsibility**: User identity management and authentication

**Domain Concepts**:
- `User` - The main aggregate representing a user in the system
- `Email` - Value object for email addresses
- `Credentials` - Value object for user authentication data
- `HashedPassword` - Value object for secure password storage

**Directory Structure**:
```
src/App/User/
├── Application/          # Use cases and application services
│   ├── Command/         # Command handlers (write operations)
│   └── Query/           # Query handlers (read operations)
├── Domain/              # Business logic and rules
│   ├── Event/          # Domain events
│   ├── Exception/      # Domain-specific exceptions
│   ├── Repository/     # Repository interfaces
│   ├── Specification/  # Business rules
│   ├── ValueObject/    # Value objects
│   └── User.php        # Main aggregate root
└── Infrastructure/      # Technical implementation
    ├── Auth/           # Authentication mechanisms
    ├── ReadModel/      # Read model projections
    ├── Repository/     # Repository implementations
    └── Specification/  # Specification implementations
```

#### **Shared Context** (`src/App/Shared/`)

**Responsibility**: Common infrastructure and shared concepts across bounded contexts

**Contains**:
- Common value objects (DateTime)
- Base interfaces and abstractions
- Shared domain events infrastructure
- Cross-cutting concerns

## Benefits of Bounded Context

### 1. **Clear Ownership**
Each bounded context has clear ownership and responsibility:

```php
// User context owns user-related concepts
namespace App\User\Domain;

class User extends EventSourcedAggregateRoot
{
    // User-specific business logic
}
```

### 2. **Autonomous Evolution**
Contexts can evolve independently without affecting others:

- User context can change authentication mechanisms
- New contexts can be added without modifying existing ones

### 3. **Team Boundaries**
Different teams can work on different contexts:

- User Management Team → User Context
- Payment Team → Payment Context (future)
- Catalog Team → Product Context (future)

### 4. **Technology Diversity**
Different contexts can use different technologies:

```yaml
# Different contexts could use different storage mechanisms
user_context:
    storage: event_store  # Event sourcing for user data
    
catalog_context:
    storage: relational   # Traditional database for catalog
```

## Context Mapping

### Integration Patterns

When multiple bounded contexts exist, they need to integrate. Common patterns include:

#### **Shared Kernel**
Common concepts shared between contexts:

```php
// Shared across all contexts
namespace App\Shared\Domain\ValueObject;

final class DateTime extends DateTimeImmutable
{
    // Shared time representation
}
```

#### **Published Language**
Events published by one context and consumed by others:

```php
// User context publishes events
class UserWasCreated implements EventInterface
{
    // Other contexts can subscribe to this event
}
```

#### **Anti-Corruption Layer**
Protecting domain model from external systems (covered in detail in [Anti-Corruption Layer](./anti-corruption-layer))

## Identifying Bounded Contexts

### Signs You Need a New Bounded Context

1. **Different Ubiquitous Language**: Teams use different terms for the same concept
2. **Different Business Rules**: Same data has different validation or behavior
3. **Different Stakeholders**: Different business units own different parts
4. **Different Change Rates**: Some parts change frequently, others are stable

### Example: Future E-commerce Contexts

This boilerplate could be extended with additional contexts:

```
src/App/
├── User/              # Identity and access management
├── Catalog/           # Product information management
├── Order/             # Order processing and fulfillment
├── Payment/           # Payment processing
├── Inventory/         # Stock management
└── Shipping/          # Logistics and delivery
```

Each would have its own:
- Domain model
- Database/Storage
- Business rules
- Team ownership

## Context Integration Example

```php
// User context publishes events
class UserWasCreated implements EventInterface
{
    public function __construct(
        public readonly UuidInterface $uuid,
        public readonly Email $email,
        public readonly DateTime $occurredOn
    ) {}
}

// Order context subscribes to user events
class UserEventSubscriber
{
    public function handleUserWasCreated(UserWasCreated $event): void
    {
        // Create customer record in order context
        $customer = Customer::fromUserData(
            $event->uuid,
            $event->email
        );
        
        $this->customerRepository->store($customer);
    }
}
```

## Best Practices

### 1. **Start Simple**
Begin with fewer, larger contexts and split as complexity grows

### 2. **Follow Conway's Law**
Align bounded contexts with organizational structure

### 3. **Minimize Coupling**
Contexts should interact through well-defined interfaces

### 4. **Consistent Within Context**
Maintain consistency within each context boundary

### 5. **Document Context Boundaries**
Clearly document what belongs in each context and why

## Testing Bounded Contexts

Each context should be testable in isolation:

```php
// User context tests don't depend on other contexts
class UserTest extends TestCase
{
    public function testUserCanChangeEmail(): void
    {
        $user = User::create($uuid, $credentials, $uniqueEmailSpec);
        $user->changeEmail($newEmail, $uniqueEmailSpec);
        
        // Test user domain logic in isolation
        $this->assertEquals($newEmail, $user->email());
    }
}
```

This approach ensures that bounded contexts remain truly autonomous and can evolve independently.