---
title: Layered Architecture
slug: /ddd/layered-architecture
---

# Layered Architecture

## Overview

This boilerplate implements a **Clean Architecture** (also known as Hexagonal Architecture or Ports and Adapters) that organizes code into distinct layers with clear responsibilities and dependencies flowing inward toward the domain.

## Architecture Layers

### Directory Structure

```
src/
├── App/                    # Application Layer
│   ├── Shared/            # Shared application concerns
│   └── User/              # User bounded context
│       ├── Application/   # Application Layer
│       ├── Domain/        # Domain Layer  
│       └── Infrastructure/# Infrastructure Layer
└── UI/                    # User Interface Layer
    ├── Cli/              # Command Line Interface
    └── Http/             # HTTP Interfaces (REST, Web)
```

## Layer Responsibilities

### 1. Domain Layer (`src/App/*/Domain/`)

**Purpose**: Contains the business logic and rules. This is the heart of the application.

**Responsibilities**:
- Business entities and aggregates
- Value objects
- Domain services
- Business rules and invariants
- Domain events
- Repository interfaces (ports)

**Dependencies**: None (pure business logic)

```php
// src/App/User/Domain/User.php
class User extends EventSourcedAggregateRoot
{
    // Pure business logic - no dependencies on infrastructure
    public function changeEmail(
        Email $email,
        UniqueEmailSpecificationInterface $uniqueEmailSpecification
    ): void {
        $uniqueEmailSpecification->isUnique($email);
        $this->apply(new UserEmailChanged($this->uuid, $email, DateTime::now()));
    }
}
```

**Contents**:
```
Domain/
├── Event/              # Domain events
├── Exception/          # Domain-specific exceptions
├── Repository/         # Repository interfaces (ports)
├── Specification/      # Business rule interfaces
├── ValueObject/        # Value objects
└── User.php           # Aggregate root
```

### 2. Application Layer (`src/App/*/Application/`)

**Purpose**: Orchestrates domain objects to fulfill use cases. Acts as a coordination layer.

**Responsibilities**:
- Use case implementation
- Transaction management
- Security and authorization
- Command and query handlers
- Application services

**Dependencies**: Domain layer only

```php
// src/App/User/Application/Command/ChangeEmail/ChangeEmailHandler.php
class ChangeEmailHandler implements CommandHandlerInterface
{
    public function __invoke(ChangeEmailCommand $command): void
    {
        $user = $this->userRepository->get($command->uuid);
        $user->changeEmail($command->email, $this->uniqueEmailSpecification);
        $this->userRepository->store($user);
    }
}
```

**Contents**:
```
Application/
├── Command/           # Command handlers (write operations)
│   ├── ChangeEmail/
│   ├── SignIn/
│   └── SignUp/
└── Query/            # Query handlers (read operations)
    ├── FindByEmail/
    └── GetUser/
```

### 3. Infrastructure Layer (`src/App/*/Infrastructure/`)

**Purpose**: Provides implementations for interfaces defined in domain and application layers.

**Responsibilities**:
- Database access
- External service integration
- Message queues
- File systems
- Repository implementations (adapters)
- Specification implementations

**Dependencies**: Domain and Application layers

```php
// src/App/User/Infrastructure/Repository/UserStore.php
class UserStore implements UserRepositoryInterface
{
    public function store(User $user): void
    {
        // Broadway event store implementation
        $this->repository->save($user);
    }

    public function get(UuidInterface $uuid): User
    {
        return $this->repository->load($uuid->toString());
    }
}
```

**Contents**:
```
Infrastructure/
├── Auth/              # Authentication implementations
├── ReadModel/         # Read model projections
├── Repository/        # Repository implementations
└── Specification/     # Specification implementations
```

### 4. User Interface Layer (`src/UI/`)

**Purpose**: Handles user interaction and presents information.

**Responsibilities**:
- HTTP controllers
- CLI commands
- Request/Response handling
- Input validation
- Presentation logic

**Dependencies**: Application layer (via interfaces)

```php
// src/UI/Http/Rest/Controller/User/ChangeEmailController.php
class ChangeEmailController
{
    public function __invoke(Request $request, string $uuid): JsonResponse
    {
        $command = new ChangeEmailCommand(
            Uuid::fromString($uuid),
            Email::fromString($request->get('email'))
        );

        $this->commandBus->handle($command);

        return new JsonResponse(null, Response::HTTP_OK);
    }
}
```

**Contents**:
```
UI/
├── Cli/              # Command line interfaces
│   └── Command/
└── Http/             # HTTP interfaces
    ├── Rest/         # REST API controllers
    └── Web/          # Web UI controllers
```

## Dependency Flow

### Dependency Rule

**Dependencies only point inward**:

```
UI Layer → Application Layer → Domain Layer
Infrastructure Layer → Domain Layer
```

### Dependency Inversion

The domain layer defines interfaces that infrastructure implements:

```php
// Domain defines the interface
namespace App\User\Domain\Repository;

interface UserRepositoryInterface
{
    public function get(UuidInterface $uuid): User;
    public function store(User $user): void;
}

// Infrastructure implements it
namespace App\User\Infrastructure\Repository;

class UserStore implements UserRepositoryInterface
{
    // Implementation details...
}
```

## Layer Communication

### Command/Query Flow

```
1. UI Layer receives request
2. UI Layer creates Command/Query
3. UI Layer sends to Application Layer (via Bus)
4. Application Layer handles use case
5. Application Layer uses Domain objects
6. Domain Layer contains business logic
7. Infrastructure Layer provides persistence
```

### Example Flow: Change Email

```php
// 1. UI Layer - REST Controller
class ChangeEmailController
{
    public function __invoke(Request $request, string $uuid): JsonResponse
    {
        // 2. Create command
        $command = new ChangeEmailCommand(
            Uuid::fromString($uuid),
            Email::fromString($request->get('email'))
        );

        // 3. Send to application layer
        $this->commandBus->handle($command);

        return new JsonResponse(null, Response::HTTP_OK);
    }
}

// 4. Application Layer - Command Handler
class ChangeEmailHandler
{
    public function __invoke(ChangeEmailCommand $command): void
    {
        // 5. Use domain objects
        $user = $this->userRepository->get($command->uuid);
        $user->changeEmail($command->email, $this->uniqueEmailSpecification);
        
        // 7. Infrastructure provides persistence
        $this->userRepository->store($user);
    }
}

// 6. Domain Layer - Business Logic
class User extends EventSourcedAggregateRoot
{
    public function changeEmail(Email $email, UniqueEmailSpecificationInterface $spec): void
    {
        $spec->isUnique($email);
        $this->apply(new UserEmailChanged($this->uuid, $email, DateTime::now()));
    }
}
```

## Benefits of This Architecture

### 1. **Testability**

Each layer can be tested in isolation:

```php
// Test domain logic without infrastructure
class UserTest extends TestCase
{
    public function testUserCanChangeEmail(): void
    {
        $user = User::create($uuid, $credentials, $mock);
        $user->changeEmail($newEmail, $mock);
        
        $this->assertEquals($newEmail->toString(), $user->email());
    }
}

// Test application logic with mocked infrastructure
class ChangeEmailHandlerTest extends TestCase
{
    public function testHandleChangeEmail(): void
    {
        $mockRepo = $this->createMock(UserRepositoryInterface::class);
        $handler = new ChangeEmailHandler($mockRepo, $mockSpec);
        
        $handler->handle($command);
        
        // Assert expectations
    }
}
```

### 2. **Flexibility**

Infrastructure can be changed without affecting business logic:

```php
// Can switch from EventStore to traditional database
class DoctrineUserRepository implements UserRepositoryInterface
{
    // Different implementation, same interface
}
```

### 3. **Maintainability**

Clear separation of concerns makes code easier to understand and modify.

### 4. **Business Focus**

Domain layer contains only business logic, making it easier to validate with domain experts.

## Layer Enforcement

### Deptrac Configuration

The project uses Deptrac to enforce architectural boundaries:

```yaml
# deptrac.yaml
layers:
  - name: UI
    collectors:
      - type: directory
        regex: src/UI/.*
  
  - name: Application
    collectors:
      - type: directory
        regex: src/App/.*/Application/.*
  
  - name: Domain
    collectors:
      - type: directory
        regex: src/App/.*/Domain/.*
  
  - name: Infrastructure
    collectors:
      - type: directory
        regex: src/App/.*/Infrastructure/.*

ruleset:
  UI:
    - Application
  Application:
    - Domain
  Infrastructure:
    - Domain
  Domain: []  # Domain has no dependencies
```

Run with: `make layer`

### Symfony Configuration

Dependency injection configuration enforces layer boundaries:

```yaml
# config/services.yaml
services:
    # Domain services have no dependencies on infrastructure
    App\User\Domain\:
        resource: '../src/App/User/Domain/'
        exclude: '../src/App/User/Domain/{Repository,Specification}'

    # Application services depend only on domain
    App\User\Application\:
        resource: '../src/App/User/Application/'
        
    # Infrastructure implements domain interfaces
    App\User\Infrastructure\:
        resource: '../src/App/User/Infrastructure/'
```

## Anti-Patterns to Avoid

### 1. **Layer Jumping**

❌ Don't skip layers:
```php
// Bad: UI directly accessing infrastructure
class Controller
{
    public function action()
    {
        $user = $this->entityManager->find(User::class, $id); // ❌
    }
}
```

✅ Use proper layering:
```php
// Good: UI uses application layer
class Controller
{
    public function action()
    {
        $query = new GetUserQuery($id);
        $user = $this->queryBus->ask($query); // ✅
    }
}
```

### 2. **Reverse Dependencies**

❌ Don't let inner layers depend on outer layers:
```php
// Bad: Domain depending on infrastructure
namespace App\User\Domain;

use Doctrine\ORM\EntityManager; // ❌

class User
{
    public function save(EntityManager $em): void // ❌
    {
        $em->persist($this);
    }
}
```

### 3. **Anemic Domain Model**

❌ Don't put business logic in application or infrastructure layers:
```php
// Bad: Business logic in application layer
class ChangeEmailHandler
{
    public function handle(ChangeEmailCommand $command): void
    {
        $user = $this->repository->get($command->uuid);
        
        // Business logic should be in domain
        if ($this->emailExists($command->email)) { // ❌
            throw new EmailAlreadyExistsException();
        }
        
        $user->setEmail($command->email); // ❌ Anemic setter
        $this->repository->store($user);
    }
}
```

✅ Keep business logic in domain:
```php
// Good: Business logic in domain layer
class ChangeEmailHandler
{
    public function handle(ChangeEmailCommand $command): void
    {
        $user = $this->repository->get($command->uuid);
        
        // Domain object contains business logic
        $user->changeEmail($command->email, $this->specification); // ✅
        
        $this->repository->store($user);
    }
}
```

## Testing Strategy by Layer

### Domain Layer Tests
- Unit tests for aggregates, value objects, and domain services
- No mocking of infrastructure (pure domain logic)
- Fast execution

### Application Layer Tests
- Unit tests with mocked dependencies
- Integration tests with real infrastructure
- Test use case orchestration

### Infrastructure Layer Tests
- Integration tests with real external systems
- Repository tests with real databases
- Specification tests with real data

### UI Layer Tests
- Controller tests with mocked application layer
- End-to-end tests for complete workflows
- API contract tests

This layered architecture provides a solid foundation for building maintainable, testable, and flexible applications that can evolve with changing business requirements.