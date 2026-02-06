# Anti-Corruption Layer

## What is an Anti-Corruption Layer?

An **Anti-Corruption Layer (ACL)** is a pattern that creates an isolating layer to provide clients with functionality in terms of their own domain model. This layer acts as a translator between different bounded contexts or between your domain and external systems.

## Purpose and Benefits

### 1. **Protect Domain Model**

The ACL prevents external systems from "corrupting" your domain model with their concepts, data structures, and conventions.

### 2. **Translation Between Models**

It translates between different representation models, ensuring each bounded context maintains its own ubiquitous language.

### 3. **Isolate External Dependencies**

Changes in external systems don't directly impact your domain model - they only affect the ACL.

### 4. **Maintain Domain Integrity**

Your domain remains focused on business logic rather than dealing with external system complexities.

## Implementation Patterns

### 1. **Adapter Pattern**

Transform external interfaces to match domain interfaces:

```php
// External system representation
class ExternalUserData
{
    public string $id;
    public string $email_address;
    public string $password_hash;
    public string $created_timestamp;
    public bool $is_active;
}

// Our domain interface
interface UserRepositoryInterface
{
    public function get(UuidInterface $uuid): User;
    public function store(User $user): void;
}

// Anti-Corruption Layer - Adapter
class ExternalUserRepositoryAdapter implements UserRepositoryInterface
{
    public function __construct(
        private readonly ExternalUserService $externalService,
        private readonly UserTranslator $translator
    ) {}

    public function get(UuidInterface $uuid): User
    {
        // Get data from external system
        $externalData = $this->externalService->getUserById($uuid->toString());
        
        // Translate to our domain model
        return $this->translator->toDomainUser($externalData);
    }

    public function store(User $user): void
    {
        // Translate from our domain to external format
        $externalData = $this->translator->toExternalFormat($user);
        
        // Store in external system
        $this->externalService->saveUser($externalData);
    }
}
```

### 2. **Translator Pattern**

Dedicated objects for model translation:

```php
class UserTranslator
{
    public function toDomainUser(ExternalUserData $external): User
    {
        // Translate external format to domain objects
        $uuid = Uuid::fromString($external->id);
        $email = Email::fromString($external->email_address);
        $password = HashedPassword::fromHash($external->password_hash);
        $createdAt = DateTime::fromString($external->created_timestamp);
        
        // Use domain factory method
        return User::fromExternalData($uuid, $email, $password, $createdAt);
    }

    public function toExternalFormat(User $user): ExternalUserData
    {
        $external = new ExternalUserData();
        $external->id = $user->uuid();
        $external->email_address = $user->email();
        $external->password_hash = $user->hashedPassword()->toString();
        $external->created_timestamp = $user->createdAt();
        $external->is_active = true;
        
        return $external;
    }
}
```

### 3. **Facade Pattern**

Simplify complex external APIs:

```php
// Complex external payment system
class ComplexPaymentGateway
{
    public function initializePaymentSession(array $config): PaymentSession {}
    public function configurePaymentMethods(PaymentSession $session, array $methods): void {}
    public function setCustomerData(PaymentSession $session, CustomerData $data): void {}
    public function processPayment(PaymentSession $session, PaymentData $data): PaymentResult {}
}

// Domain interface
interface PaymentServiceInterface
{
    public function processPayment(PaymentRequest $request): PaymentResult;
}

// Anti-Corruption Layer - Facade
class PaymentServiceAdapter implements PaymentServiceInterface
{
    public function __construct(
        private readonly ComplexPaymentGateway $gateway,
        private readonly PaymentTranslator $translator
    ) {}

    public function processPayment(PaymentRequest $request): PaymentResult
    {
        // Simplify complex external API
        $session = $this->gateway->initializePaymentSession([
            'currency' => $request->amount->currency(),
            'environment' => 'production'
        ]);

        $this->gateway->configurePaymentMethods($session, ['card', 'paypal']);
        
        $customerData = $this->translator->toExternalCustomer($request->customer);
        $this->gateway->setCustomerData($session, $customerData);
        
        $paymentData = $this->translator->toExternalPayment($request);
        $result = $this->gateway->processPayment($session, $paymentData);
        
        return $this->translator->toDomainResult($result);
    }
}
```

## Real-World Examples in This Boilerplate

### 1. **Email Specification ACL**

The `UniqueEmailSpecification` acts as an ACL for email uniqueness validation:

```php
// Domain interface
namespace App\User\Domain\Specification;

interface UniqueEmailSpecificationInterface
{
    public function isUnique(Email $email): void;
}

// Infrastructure implementation (ACL)
namespace App\User\Infrastructure\Specification;

class UniqueEmailSpecification implements UniqueEmailSpecificationInterface
{
    public function __construct(
        private readonly UserReadModelRepositoryInterface $readModelRepository
    ) {}

    public function isUnique(Email $email): void
    {
        // Translate domain concept to infrastructure query
        $existingUser = $this->readModelRepository->oneByEmailOrNull($email);
        
        if (null !== $existingUser) {
            throw new EmailAlreadyExistsException(
                sprintf('Email %s already exists', $email->toString())
            );
        }
    }
}
```

### 2. **Event Publishing ACL**

The async event publisher acts as an ACL between domain events and RabbitMQ:

```php
// Domain publishes events in domain language
$this->apply(new UserWasCreated($uuid, $credentials, DateTime::now()));

// ACL translates to message queue format
class AsyncEventPublisher implements EventListener
{
    public function handle($event): void
    {
        // Translate domain event to message queue format
        $message = $this->eventSerializer->serialize($event);
        $routingKey = $this->createRoutingKey($event);
        
        // Store for later publishing (after transaction)
        $this->collectedEvents[] = [
            'message' => $message,
            'routing_key' => $routingKey
        ];
    }

    private function createRoutingKey($event): string
    {
        // Translate namespace to routing key
        return str_replace('\\', '.', get_class($event));
    }
}
```

### 3. **Repository ACL**

The event store repository acts as an ACL between domain aggregates and Broadway event store:

```php
// Domain expects simple repository interface
interface UserRepositoryInterface
{
    public function get(UuidInterface $uuid): User;
    public function store(User $user): void;
}

// ACL implementation for Broadway Event Store
class UserStore implements UserRepositoryInterface
{
    public function __construct(
        private readonly EventSourcingRepository $repository
    ) {}

    public function get(UuidInterface $uuid): User
    {
        try {
            // Translate to Broadway format and back
            return $this->repository->load($uuid->toString());
        } catch (AggregateNotFoundException $e) {
            throw UserNotFoundException::withId($uuid);
        }
    }

    public function store(User $user): void
    {
        // Broadway handles event sourcing complexity
        $this->repository->save($user);
    }
}
```

## ACL Design Strategies

### 1. **Conformist**

Accept the external model as-is (minimal ACL):

```php
// When external system model is good enough
class ExternalApiClient implements ExternalServiceInterface
{
    public function getData(string $id): ExternalData
    {
        // Direct usage of external API
        return $this->httpClient->get("/api/data/{$id}");
    }
}
```

### 2. **Translator**

Translate between models (common approach):

```php
class TranslatingApiClient implements DomainServiceInterface
{
    public function getDomainData(DomainId $id): DomainData
    {
        $external = $this->externalApi->getData($id->toString());
        return $this->translator->toDomain($external);
    }
}
```

### 3. **Wrapper**

Wrap external concepts in domain concepts:

```php
class DomainPaymentService implements PaymentServiceInterface
{
    public function processPayment(PaymentRequest $request): PaymentResult
    {
        $externalRequest = ExternalPaymentRequest::fromDomain($request);
        $externalResult = $this->externalService->process($externalRequest);
        
        return PaymentResult::fromExternal($externalResult);
    }
}
```

## Testing Anti-Corruption Layers

### Unit Testing

Test translation logic in isolation:

```php
class UserTranslatorTest extends TestCase
{
    public function testTranslatesToDomainUser(): void
    {
        $external = new ExternalUserData();
        $external->id = '123e4567-e89b-12d3-a456-426614174000';
        $external->email_address = 'test@example.com';
        $external->password_hash = 'hashed_password';
        $external->created_timestamp = '2023-01-01T00:00:00Z';
        
        $translator = new UserTranslator();
        $user = $translator->toDomainUser($external);
        
        $this->assertEquals('123e4567-e89b-12d3-a456-426614174000', $user->uuid());
        $this->assertEquals('test@example.com', $user->email());
    }
}
```

### Integration Testing

Test ACL with real external systems:

```php
class ExternalUserRepositoryAdapterTest extends TestCase
{
    public function testCanRetrieveAndStoreUser(): void
    {
        // Use real external service (or test double)
        $adapter = new ExternalUserRepositoryAdapter(
            $this->realExternalService,
            new UserTranslator()
        );
        
        $user = User::create($uuid, $credentials, $specification);
        $adapter->store($user);
        
        $retrievedUser = $adapter->get($uuid);
        
        $this->assertEquals($user->uuid(), $retrievedUser->uuid());
        $this->assertEquals($user->email(), $retrievedUser->email());
    }
}
```

### Contract Testing

Ensure external system contracts are maintained:

```php
class ExternalApiContractTest extends TestCase
{
    public function testExternalApiReturnsExpectedFormat(): void
    {
        $response = $this->externalApi->getUser('123');
        
        // Verify external system still returns expected format
        $this->assertArrayHasKey('id', $response);
        $this->assertArrayHasKey('email_address', $response);
        $this->assertArrayHasKey('created_timestamp', $response);
    }
}
```

## When to Use Anti-Corruption Layer

### Use ACL When:

1. **Integrating with legacy systems** with different models
2. **Consuming third-party APIs** with incompatible structures
3. **Protecting domain model** from external changes
4. **Translating between bounded contexts** with different languages
5. **Wrapping complex external libraries**

### Don't Use ACL When:

1. **External model is already compatible** with your domain
2. **Simple, direct mapping** is sufficient
3. **Performance is critical** and translation overhead is problematic
4. **External system is under your control** and can be modified

## Best Practices

### 1. **Keep ACL Simple**

Don't add unnecessary complexity - only translate what's needed:

```php
// Good: Simple translation
class SimpleTranslator
{
    public function toDomain(ExternalData $external): DomainData
    {
        return new DomainData($external->value);
    }
}

// Avoid: Over-engineering
class ComplexTranslator
{
    // Don't add unnecessary abstractions
}
```

### 2. **Make Translation Explicit**

Be clear about what's being translated and why:

```php
class UserTranslator
{
    /**
     * Translates external user representation to domain User aggregate.
     * External system uses snake_case and different date format.
     */
    public function toDomainUser(ExternalUserData $external): User
    {
        // Clear translation steps
    }
}
```

### 3. **Handle Translation Errors**

Gracefully handle cases where translation fails:

```php
public function toDomainUser(ExternalUserData $external): User
{
    try {
        $email = Email::fromString($external->email_address);
        $uuid = Uuid::fromString($external->id);
        
        return User::fromExternalData($uuid, $email, /* ... */);
    } catch (InvalidEmailException | InvalidUuidException $e) {
        throw new TranslationException(
            "Failed to translate external user data: " . $e->getMessage(),
            previous: $e
        );
    }
}
```

### 4. **Version ACL Interfaces**

Plan for changes in external systems:

```php
interface UserRepositoryV1Interface
{
    public function get(UuidInterface $uuid): User;
}

interface UserRepositoryV2Interface extends UserRepositoryV1Interface
{
    public function getBatch(array $uuids): array;
}

class ExternalUserRepositoryV2Adapter implements UserRepositoryV2Interface
{
    // Implement both versions for gradual migration
}
```

The Anti-Corruption Layer is essential for maintaining clean domain models while integrating with external systems and different bounded contexts. It provides a clear boundary that protects your domain from external complexity and changes.