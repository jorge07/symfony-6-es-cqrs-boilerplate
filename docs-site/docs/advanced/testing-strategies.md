---
title: Testing Strategies
slug: /advanced/testing-strategies
---

# Testing Strategies for DDD/CQRS Applications

## Testing Pyramid for DDD/CQRS

DDD/CQRS applications require a well-structured testing approach that respects architectural boundaries while ensuring business logic correctness.

```
                    /\
                   /  \
                  / E2E \
                 /______\
                /        \
               /Integration\
              /___________ \
             /             \
            /     Unit       \
           /_________________\
```

## Unit Testing

### Domain Layer Testing

Test business logic in isolation without infrastructure dependencies.

#### Testing Value Objects

```php
// tests/App/User/Domain/ValueObject/EmailTest.php
class EmailTest extends TestCase
{
    public function testCanCreateValidEmail(): void
    {
        $email = Email::fromString('test@example.com');
        
        $this->assertEquals('test@example.com', $email->toString());
    }

    public function testThrowsExceptionForInvalidEmail(): void
    {
        $this->expectException(AssertionFailedException::class);
        
        Email::fromString('invalid-email');
    }

    public function testEmailEquality(): void
    {
        $email1 = Email::fromString('test@example.com');
        $email2 = Email::fromString('test@example.com');
        
        $this->assertTrue($email1->equals($email2));
    }
}
```

#### Testing Aggregates

```php
// tests/App/User/Domain/UserTest.php
class UserTest extends TestCase
{
    private UuidInterface $uuid;
    private Credentials $credentials;
    private UniqueEmailSpecificationInterface $uniqueEmailSpec;

    protected function setUp(): void
    {
        $this->uuid = Uuid::uuid4();
        $this->credentials = Credentials::fromArray([
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);
        $this->uniqueEmailSpec = $this->createMock(UniqueEmailSpecificationInterface::class);
    }

    public function testCanCreateUser(): void
    {
        $this->uniqueEmailSpec
            ->expects($this->once())
            ->method('isUnique')
            ->with($this->credentials->email);

        $user = User::create($this->uuid, $this->credentials, $this->uniqueEmailSpec);

        $this->assertEquals($this->uuid->toString(), $user->uuid());
        $this->assertEquals($this->credentials->email->toString(), $user->email());
    }

    public function testCanChangeEmail(): void
    {
        $user = User::create($this->uuid, $this->credentials, $this->uniqueEmailSpec);
        $newEmail = Email::fromString('new@example.com');

        $this->uniqueEmailSpec
            ->expects($this->once())
            ->method('isUnique')
            ->with($newEmail);

        $user->changeEmail($newEmail, $this->uniqueEmailSpec);

        $this->assertEquals('new@example.com', $user->email());
    }

    public function testEmitsEventWhenCreated(): void
    {
        $user = User::create($this->uuid, $this->credentials, $this->uniqueEmailSpec);

        $events = $user->getUncommittedEvents();
        
        $this->assertCount(1, $events);
        $this->assertInstanceOf(UserWasCreated::class, $events[0]);
    }

    public function testThrowsExceptionForInvalidCredentials(): void
    {
        $user = User::create($this->uuid, $this->credentials, $this->uniqueEmailSpec);
        
        $this->expectException(InvalidCredentialsException::class);
        
        $user->signIn('wrong-password');
    }
}
```

#### Testing Domain Services

```php
// tests/App/User/Domain/Service/PasswordHashingServiceTest.php
class PasswordHashingServiceTest extends TestCase
{
    private PasswordHashingService $service;

    protected function setUp(): void
    {
        $this->service = new PasswordHashingService();
    }

    public function testCanHashPassword(): void
    {
        $plainPassword = 'password123';
        
        $hashedPassword = $this->service->hash($plainPassword);
        
        $this->assertNotEquals($plainPassword, $hashedPassword->toString());
        $this->assertTrue($hashedPassword->match($plainPassword));
    }

    public function testDifferentPasswordsProduceDifferentHashes(): void
    {
        $hash1 = $this->service->hash('password1');
        $hash2 = $this->service->hash('password2');
        
        $this->assertNotEquals($hash1->toString(), $hash2->toString());
    }
}
```

### Application Layer Testing

Test use case orchestration with mocked dependencies.

#### Testing Command Handlers

```php
// tests/App/User/Application/Command/SignUp/SignUpHandlerTest.php
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
        $command = new SignUpCommand(
            Uuid::uuid4(),
            Credentials::fromArray([
                'email' => 'test@example.com',
                'password' => 'password123'
            ])
        );

        $this->uniqueEmailSpec
            ->expects($this->once())
            ->method('isUnique')
            ->with($command->credentials->email);

        $this->userRepository
            ->expects($this->once())
            ->method('store')
            ->with($this->isInstanceOf(User::class));

        $this->handler->__invoke($command);
    }

    public function testThrowsExceptionForDuplicateEmail(): void
    {
        $command = new SignUpCommand(
            Uuid::uuid4(),
            Credentials::fromArray([
                'email' => 'duplicate@example.com',
                'password' => 'password123'
            ])
        );

        $this->uniqueEmailSpec
            ->expects($this->once())
            ->method('isUnique')
            ->willThrowException(new EmailAlreadyExistsException());

        $this->expectException(EmailAlreadyExistsException::class);

        $this->handler->__invoke($command);
    }
}
```

#### Testing Query Handlers

```php
// tests/App/User/Application/Query/FindByEmail/FindByEmailHandlerTest.php
class FindByEmailHandlerTest extends TestCase
{
    private FindByEmailHandler $handler;
    private UserReadModelRepositoryInterface $repository;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(UserReadModelRepositoryInterface::class);
        $this->handler = new FindByEmailHandler($this->repository);
    }

    public function testCanFindUserByEmail(): void
    {
        $email = Email::fromString('test@example.com');
        $userView = new UserView(
            'uuid-123',
            'test@example.com',
            '2023-01-01T00:00:00Z'
        );

        $this->repository
            ->expects($this->once())
            ->method('oneByEmailOrNull')
            ->with($email)
            ->willReturn($userView);

        $query = new FindByEmailQuery($email);
        $result = $this->handler->__invoke($query);

        $this->assertSame($userView, $result);
    }

    public function testReturnsNullWhenUserNotFound(): void
    {
        $email = Email::fromString('notfound@example.com');

        $this->repository
            ->expects($this->once())
            ->method('oneByEmailOrNull')
            ->with($email)
            ->willReturn(null);

        $query = new FindByEmailQuery($email);
        $result = $this->handler->__invoke($query);

        $this->assertNull($result);
    }
}
```

## Integration Testing

Test how components work together with real infrastructure.

### Repository Integration Tests

```php
// tests/App/User/Infrastructure/Repository/UserStoreTest.php
class UserStoreTest extends TestCase
{
    use ResetDatabase;

    private UserStore $userStore;
    private UniqueEmailSpecificationInterface $uniqueEmailSpec;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->userStore = $this->getContainer()->get(UserStore::class);
        $this->uniqueEmailSpec = $this->getContainer()->get(UniqueEmailSpecificationInterface::class);
    }

    public function testCanStoreAndRetrieveUser(): void
    {
        $uuid = Uuid::uuid4();
        $credentials = Credentials::fromArray([
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        // Create and store user
        $user = User::create($uuid, $credentials, $this->uniqueEmailSpec);
        $this->userStore->store($user);

        // Retrieve and verify
        $retrievedUser = $this->userStore->get($uuid);

        $this->assertEquals($uuid->toString(), $retrievedUser->uuid());
        $this->assertEquals($credentials->email->toString(), $retrievedUser->email());
    }

    public function testThrowsExceptionWhenUserNotFound(): void
    {
        $nonExistentUuid = Uuid::uuid4();

        $this->expectException(UserNotFoundException::class);

        $this->userStore->get($nonExistentUuid);
    }

    public function testUserStateIsReconstructedFromEvents(): void
    {
        $uuid = Uuid::uuid4();
        $credentials = Credentials::fromArray([
            'email' => 'original@example.com',
            'password' => 'password123'
        ]);

        // Create user
        $user = User::create($uuid, $credentials, $this->uniqueEmailSpec);
        
        // Change email
        $newEmail = Email::fromString('changed@example.com');
        $user->changeEmail($newEmail, $this->uniqueEmailSpec);
        
        // Store user
        $this->userStore->store($user);

        // Retrieve and verify final state
        $retrievedUser = $this->userStore->get($uuid);
        
        $this->assertEquals('changed@example.com', $retrievedUser->email());
    }
}
```

### Command Bus Integration Tests

```php
// tests/UI/Http/Rest/Controller/User/SignUpControllerTest.php
class SignUpControllerTest extends JsonApiTestCase
{
    use ResetDatabase;

    public function testCanSignUpUser(): void
    {
        $this->client->request('POST', '/api/users', [], [], [], json_encode([
            'email' => 'test@example.com',
            'password' => 'password123'
        ]));

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(201);

        // Verify user was created in database
        $userRepository = $this->getContainer()->get(UserRepositoryInterface::class);
        $users = $userRepository->findAll();
        
        $this->assertCount(1, $users);
        $this->assertEquals('test@example.com', $users[0]->email());
    }

    public function testReturnsErrorForDuplicateEmail(): void
    {
        // Create first user
        $this->client->request('POST', '/api/users', [], [], [], json_encode([
            'email' => 'duplicate@example.com',
            'password' => 'password123'
        ]));

        // Try to create duplicate
        $this->client->request('POST', '/api/users', [], [], [], json_encode([
            'email' => 'duplicate@example.com',
            'password' => 'password456'
        ]));

        $this->assertResponseStatusCodeSame(409);
        
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertStringContainsString('already exists', $response['error']);
    }
}
```

## End-to-End Testing

Test complete user workflows.

### Web UI Testing

```php
// tests/UI/Http/Web/Controller/SignUpControllerTest.php
class SignUpControllerTest extends WebTestCase
{
    use ResetDatabase;

    public function testUserCanSignUpThroughWebInterface(): void
    {
        $this->client->request('GET', '/signup');
        $this->assertResponseIsSuccessful();

        $this->client->submitForm('Sign Up', [
            'signup_form[email]' => 'web@example.com',
            'signup_form[password]' => 'password123'
        ]);

        $this->assertResponseRedirects('/profile');
        
        // Follow redirect
        $this->client->followRedirect();
        $this->assertSelectorTextContains('h1', 'Welcome web@example.com');
    }

    public function testSignUpFormShowsValidationErrors(): void
    {
        $this->client->request('GET', '/signup');
        
        $this->client->submitForm('Sign Up', [
            'signup_form[email]' => 'invalid-email',
            'signup_form[password]' => '123' // Too short
        ]);

        $this->assertSelectorExists('.form-error');
        $this->assertSelectorTextContains('.form-error', 'valid email');
    }
}
```

### Event Flow Testing

```php
// tests/Integration/EventFlowTest.php
class EventFlowTest extends TestCase
{
    use ResetDatabase;

    public function testCompleteUserSignUpFlow(): void
    {
        // 1. Sign up user
        $signUpCommand = new SignUpCommand(
            Uuid::fromString('123e4567-e89b-12d3-a456-426614174000'),
            Credentials::fromArray([
                'email' => 'integration@example.com',
                'password' => 'password123'
            ])
        );

        $this->commandBus->handle($signUpCommand);

        // 2. Verify user can be queried
        $findQuery = new FindByEmailQuery(
            Email::fromString('integration@example.com')
        );

        $userView = $this->queryBus->ask($findQuery);
        
        $this->assertNotNull($userView);
        $this->assertEquals('integration@example.com', $userView->email);

        // 3. Verify events were published
        $this->assertEventWasPublished(UserWasCreated::class);

        // 4. Process async events
        $this->processAsyncEvents();

        // 5. Verify projections were updated
        $elasticRepository = $this->getContainer()->get(UserElasticRepository::class);
        $searchResult = $elasticRepository->findByEmail('integration@example.com');
        
        $this->assertNotNull($searchResult);
    }

    private function assertEventWasPublished(string $eventClass): void
    {
        $eventStore = $this->getContainer()->get(EventStoreInterface::class);
        $events = $eventStore->loadAll();
        
        $found = false;
        foreach ($events as $event) {
            if ($event instanceof $eventClass) {
                $found = true;
                break;
            }
        }
        
        $this->assertTrue($found, "Event {$eventClass} was not published");
    }

    private function processAsyncEvents(): void
    {
        // Simulate processing async events
        $transport = $this->getContainer()->get('messenger.transport.async_events');
        
        while ($envelopes = $transport->get()) {
            foreach ($envelopes as $envelope) {
                $this->eventBus->dispatch($envelope->getMessage());
                $transport->ack($envelope);
            }
        }
    }
}
```

## Testing Patterns and Best Practices

### Test Data Builders

Create reusable test data builders for complex objects:

```php
// tests/Support/UserBuilder.php
class UserBuilder
{
    private UuidInterface $uuid;
    private string $email = 'default@example.com';
    private string $password = 'password123';

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();
    }

    public function withUuid(UuidInterface $uuid): self
    {
        $this->uuid = $uuid;
        return $this;
    }

    public function withEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function withPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function build(): User
    {
        $uniqueEmailSpec = $this->createMock(UniqueEmailSpecificationInterface::class);
        $uniqueEmailSpec->method('isUnique')->willReturn(true);

        return User::create(
            $this->uuid,
            Credentials::fromArray([
                'email' => $this->email,
                'password' => $this->password
            ]),
            $uniqueEmailSpec
        );
    }

    public static function aUser(): self
    {
        return new self();
    }
}

// Usage in tests
class UserTest extends TestCase
{
    public function testUserCreation(): void
    {
        $user = UserBuilder::aUser()
            ->withEmail('custom@example.com')
            ->withPassword('customPassword')
            ->build();

        $this->assertEquals('custom@example.com', $user->email());
    }
}
```

### Mother Objects

Create mother objects for common test scenarios:

```php
// tests/Support/UserMother.php
class UserMother
{
    public static function newUser(): User
    {
        return UserBuilder::aUser()->build();
    }

    public static function userWithEmail(string $email): User
    {
        return UserBuilder::aUser()
            ->withEmail($email)
            ->build();
    }

    public static function adminUser(): User
    {
        return UserBuilder::aUser()
            ->withEmail('admin@example.com')
            ->build();
    }

    public static function userReadyForEmailChange(): User
    {
        $user = self::newUser();
        // Perform any setup needed for email change scenario
        return $user;
    }
}
```

### Custom Assertions

Create domain-specific assertions:

```php
// tests/Support/UserAssertions.php
trait UserAssertions
{
    protected function assertUserHasEmail(User $user, string $expectedEmail): void
    {
        $this->assertEquals($expectedEmail, $user->email(), 
            "Expected user to have email '{$expectedEmail}' but got '{$user->email()}'");
    }

    protected function assertUserWasCreated(User $user): void
    {
        $events = $user->getUncommittedEvents();
        
        $this->assertNotEmpty($events, 'Expected user to have events but found none');
        $this->assertInstanceOf(UserWasCreated::class, $events[0], 
            'Expected first event to be UserWasCreated');
    }

    protected function assertEventWasApplied(User $user, string $eventClass): void
    {
        $events = $user->getUncommittedEvents();
        $found = false;
        
        foreach ($events as $event) {
            if ($event instanceof $eventClass) {
                $found = true;
                break;
            }
        }
        
        $this->assertTrue($found, "Expected event {$eventClass} to be applied");
    }
}

// Usage
class UserTest extends TestCase
{
    use UserAssertions;

    public function testUserCreation(): void
    {
        $user = UserMother::newUser();
        
        $this->assertUserWasCreated($user);
        $this->assertUserHasEmail($user, 'default@example.com');
    }
}
```

### Testing Async Operations

```php
// tests/Integration/AsyncOperationsTest.php
class AsyncOperationsTest extends TestCase
{
    use ResetDatabase;

    public function testAsyncEventProcessing(): void
    {
        // Create user (generates events)
        $command = new SignUpCommand($uuid, $credentials);
        $this->commandBus->handle($command);

        // Verify event was queued
        $transport = $this->getContainer()->get('messenger.transport.async_events');
        $envelopes = $transport->get();
        
        $this->assertCount(1, $envelopes);
        $this->assertInstanceOf(UserWasCreated::class, $envelopes[0]->getMessage());

        // Process the event
        $this->eventBus->dispatch($envelopes[0]->getMessage());
        $transport->ack($envelopes[0]);

        // Verify projection was updated
        $userView = $this->userReadRepository->find($uuid);
        $this->assertNotNull($userView);
    }
}
```

## Testing Tools and Configuration

### PHPUnit Configuration

```xml
<!-- phpunit.xml.dist -->
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         colors="true"
         bootstrap="tests/bootstrap.php">
    
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/App</directory>
        </testsuite>
        <testsuite name="Integration">
            <directory>tests/Integration</directory>
        </testsuite>
        <testsuite name="UI">
            <directory>tests/UI</directory>
        </testsuite>
    </testsuites>

    <coverage>
        <include>
            <directory>src</directory>
        </include>
        <exclude>
            <directory>src/*/Infrastructure</directory>
        </exclude>
    </coverage>

    <php>
        <env name="APP_ENV" value="test"/>
        <env name="DATABASE_URL" value="mysql://root:root@database:3306/test_db"/>
    </php>
</phpunit>
```

### Test Database Management

```php
// tests/Support/ResetDatabase.php
trait ResetDatabase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->resetDatabase();
    }

    private function resetDatabase(): void
    {
        $application = new Application($this->getContainer()->get(KernelInterface::class));
        
        // Drop and recreate database
        $application->run(new ArrayInput([
            'command' => 'doctrine:database:drop',
            '--force' => true,
            '--if-exists' => true
        ]));

        $application->run(new ArrayInput([
            'command' => 'doctrine:database:create'
        ]));

        $application->run(new ArrayInput([
            'command' => 'doctrine:migrations:migrate',
            '--no-interaction' => true
        ]));
    }
}
```

This comprehensive testing strategy ensures that your DDD/CQRS application is reliable, maintainable, and behaves correctly across all architectural layers.