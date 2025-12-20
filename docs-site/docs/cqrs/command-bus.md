---
title: Command Bus
slug: /cqrs/command-bus
---

# Command Bus

## What is a Command Bus?

A **Command Bus** is a pattern that encapsulates and routes commands to their appropriate handlers. It acts as a mediator between the user interface and the application layer, providing a clean way to execute business operations.

## Benefits of Command Bus

### 1. **Decoupling**

The UI doesn't need to know about specific handlers:

```php
// UI only knows about the command and bus
class SignUpController
{
    public function __invoke(Request $request): JsonResponse
    {
        $command = new SignUpCommand(
            Uuid::uuid4(),
            Credentials::fromArray($request->toArray())
        );

        // Don't need to know about SignUpHandler
        $this->commandBus->handle($command);

        return new JsonResponse(null, Response::HTTP_CREATED);
    }
}
```

### 2. **Cross-Cutting Concerns**

Bus can handle common concerns for all commands:

- Validation
- Authorization
- Logging
- Transactions
- Metrics

### 3. **Single Entry Point**

All business operations go through the bus, making it easy to:

- Add middleware
- Monitor performance
- Implement security
- Debug issues

## Implementation in This Boilerplate

### Command Interface

All commands implement a marker interface:

```php
// src/App/Shared/Infrastructure/Bus/Command/CommandInterface.php
interface CommandInterface
{
    // Marker interface - no methods required
}
```

### Command Examples

```php
// src/App/User/Application/Command/SignUp/SignUpCommand.php
class SignUpCommand implements CommandInterface
{
    public function __construct(
        public readonly UuidInterface $uuid,
        public readonly Credentials $credentials
    ) {}
}

// src/App/User/Application/Command/ChangeEmail/ChangeEmailCommand.php
class ChangeEmailCommand implements CommandInterface
{
    public function __construct(
        public readonly UuidInterface $uuid,
        public readonly Email $email
    ) {}
}
```

### Command Handlers

Each command has a corresponding handler:

```php
// src/App/User/Application/Command/SignUp/SignUpHandler.php
class SignUpHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly UniqueEmailSpecificationInterface $uniqueEmailSpecification
    ) {}

    public function __invoke(SignUpCommand $command): void
    {
        $user = User::create(
            $command->uuid,
            $command->credentials,
            $this->uniqueEmailSpecification
        );

        $this->userRepository->store($user);
    }
}
```

### Command Bus Implementation

The bus uses Symfony Messenger:

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
            // Unwrap the actual exception
            while ($e instanceof HandlerFailedException) {
                $e = $e->getPrevious();
            }

            throw $e;
        }
    }
}
```

## Command Design Patterns

### 1. **Command as Data Structure**

Commands should be simple data containers:

```php
class ChangeEmailCommand implements CommandInterface
{
    public function __construct(
        public readonly UuidInterface $uuid,
        public readonly Email $email
    ) {}

    // No business logic in commands
    // No methods other than constructor
}
```

### 2. **Immutable Commands**

Commands should not be modified after creation:

```php
class SignUpCommand implements CommandInterface
{
    public function __construct(
        public readonly UuidInterface $uuid,    // readonly
        public readonly Credentials $credentials // readonly
    ) {}

    // Properties are readonly - cannot be changed
}
```

### 3. **Factory Methods for Complex Commands**

Use factory methods for complex command creation:

```php
class SignUpCommand implements CommandInterface
{
    public function __construct(
        public readonly UuidInterface $uuid,
        public readonly Credentials $credentials
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            Uuid::uuid4(),
            Credentials::fromArray([
                'email' => $request->get('email'),
                'password' => $request->get('password')
            ])
        );
    }
}

// Usage in controller
$command = SignUpCommand::fromRequest($request);
$this->commandBus->handle($command);
```

## Middleware and Cross-Cutting Concerns

### Validation Middleware

```php
class ValidateCommandMiddleware implements MiddlewareInterface
{
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $command = $envelope->getMessage();

        if ($command instanceof CommandInterface) {
            $this->validator->validate($command);
        }

        return $stack->next()->handle($envelope, $stack);
    }
}
```

### Authorization Middleware

```php
class AuthorizeCommandMiddleware implements MiddlewareInterface
{
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $command = $envelope->getMessage();

        if ($command instanceof CommandInterface) {
            $this->authorizationService->authorize($command);
        }

        return $stack->next()->handle($envelope, $stack);
    }
}
```

### Logging Middleware

```php
class LogCommandMiddleware implements MiddlewareInterface
{
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $command = $envelope->getMessage();

        $this->logger->info('Executing command', [
            'command' => get_class($command),
            'data' => $this->serializer->serialize($command)
        ]);

        try {
            $result = $stack->next()->handle($envelope, $stack);
            
            $this->logger->info('Command executed successfully');
            
            return $result;
        } catch (\Exception $e) {
            $this->logger->error('Command execution failed', [
                'exception' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }
}
```

## Error Handling

### Command Validation

Commands should be validated before execution:

```php
class SignUpHandler implements CommandHandlerInterface
{
    public function __invoke(SignUpCommand $command): void
    {
        // Domain validation happens in value objects and aggregates
        try {
            $user = User::create(
                $command->uuid,
                $command->credentials,
                $this->uniqueEmailSpecification
            );

            $this->userRepository->store($user);
        } catch (EmailAlreadyExistsException $e) {
            // Convert domain exception to application exception
            throw new CommandExecutionException(
                'User registration failed: ' . $e->getMessage(),
                previous: $e
            );
        }
    }
}
```

### Exception Handling in Bus

```php
class MessengerCommandBus implements CommandBusInterface
{
    public function handle(CommandInterface $command): void
    {
        try {
            $this->messageBus->dispatch($command);
        } catch (HandlerFailedException $e) {
            // Unwrap messenger exceptions
            $exception = $e->getPrevious();

            if ($exception instanceof DomainException) {
                throw new CommandExecutionException(
                    'Command failed due to business rule violation',
                    previous: $exception
                );
            }

            throw $exception;
        }
    }
}
```

## Testing Commands and Handlers

### Testing Commands

Commands are simple data structures - minimal testing needed:

```php
class SignUpCommandTest extends TestCase
{
    public function testCanCreateFromValidData(): void
    {
        $uuid = Uuid::uuid4();
        $credentials = Credentials::fromArray([
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $command = new SignUpCommand($uuid, $credentials);

        $this->assertEquals($uuid, $command->uuid);
        $this->assertEquals($credentials, $command->credentials);
    }
}
```

### Testing Handlers

Focus on business logic and interactions:

```php
class SignUpHandlerTest extends TestCase
{
    private SignUpHandler $handler;
    private UserRepositoryInterface $userRepository;
    private UniqueEmailSpecificationInterface $uniqueEmailSpec;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->uniqueEmailSpec = $this->createMock(UniqueEmailSpecificationInterface::class);
        
        $this->handler = new SignUpHandler(
            $this->userRepository,
            $this->uniqueEmailSpec
        );
    }

    public function testCanSignUpUser(): void
    {
        // Arrange
        $command = new SignUpCommand($uuid, $credentials);
        
        $this->uniqueEmailSpec
            ->expects($this->once())
            ->method('isUnique')
            ->with($credentials->email);

        $this->userRepository
            ->expects($this->once())
            ->method('store')
            ->with($this->isInstanceOf(User::class));

        // Act
        $this->handler->__invoke($command);

        // Assert - expectations verified by mocks
    }

    public function testThrowsExceptionForDuplicateEmail(): void
    {
        $command = new SignUpCommand($uuid, $credentials);
        
        $this->uniqueEmailSpec
            ->expects($this->once())
            ->method('isUnique')
            ->will($this->throwException(new EmailAlreadyExistsException()));

        $this->expectException(EmailAlreadyExistsException::class);

        $this->handler->__invoke($command);
    }
}
```

### Integration Testing

Test the complete command flow:

```php
class SignUpIntegrationTest extends TestCase
{
    public function testCanSignUpUserThroughBus(): void
    {
        // Arrange
        $command = new SignUpCommand(
            Uuid::uuid4(),
            Credentials::fromArray([
                'email' => 'test@example.com',
                'password' => 'password123'
            ])
        );

        // Act
        $this->commandBus->handle($command);

        // Assert
        $user = $this->userRepository->get($command->uuid);
        $this->assertEquals($command->credentials->email, $user->email());
    }
}
```

## Command Bus Configuration

### Symfony Messenger Configuration

```yaml
# config/packages/messenger.yaml
framework:
    messenger:
        default_bus: command.bus
        buses:
            command.bus:
                middleware:
                    - validation
                    - doctrine_transaction
```

### Service Configuration

```yaml
# config/services.yaml
services:
    # Command Bus
    App\Shared\Infrastructure\Bus\Command\MessengerCommandBus:
        arguments:
            $messageBus: '@command.bus'

    # Command Handlers
    App\User\Application\Command\SignUp\SignUpHandler:
        tags:
            - { name: messenger.message_handler, bus: command.bus }

    App\User\Application\Command\ChangeEmail\ChangeEmailHandler:
        tags:
            - { name: messenger.message_handler, bus: command.bus }
```

## Best Practices

### 1. **Commands Should Be Imperative**

Use imperative verbs that describe what should happen:

```php
// Good
class SignUpCommand {}
class ChangeEmailCommand {}
class DeactivateUserCommand {}

// Avoid
class UserCommand {}           // Too generic
class UserDataCommand {}       // Unclear intention
```

### 2. **One Handler Per Command**

Each command should have exactly one handler:

```php
// Good - clear 1:1 mapping
class SignUpCommand {}
class SignUpHandler {}

// Avoid - multiple handlers for same command
class SignUpHandler {}
class SignUpEmailHandler {}    // Confusing
```

### 3. **Commands Should Not Return Data**

Commands change state, they don't return data:

```php
// Good
public function handle(SignUpCommand $command): void
{
    // Create user, no return value
}

// Avoid
public function handle(SignUpCommand $command): User
{
    // Don't return data from commands
}
```

### 4. **Use Value Objects in Commands**

Leverage domain value objects for type safety:

```php
// Good
class ChangeEmailCommand 
{
    public function __construct(
        public readonly UuidInterface $uuid,  // Value object
        public readonly Email $email          // Value object
    ) {}
}

// Avoid
class ChangeEmailCommand 
{
    public function __construct(
        public readonly string $uuid,    // Primitive
        public readonly string $email    // Primitive
    ) {}
}
```

The Command Bus provides a clean, testable, and maintainable way to execute business operations while keeping concerns properly separated.