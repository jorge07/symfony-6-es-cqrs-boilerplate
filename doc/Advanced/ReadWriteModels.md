# Read and Write Models

## Overview

In CQRS applications, **Read Models** and **Write Models** serve different purposes and are optimized for their specific use cases. This separation allows for independent optimization, scaling, and evolution of read and write operations.

## Write Models

Write models focus on business logic, consistency, and command processing. They prioritize correctness over performance.

### Characteristics

- **Business Logic Focus**: Contain domain rules and invariants
- **Consistency**: Strong consistency and transactional integrity
- **Normalized Structure**: Proper domain model structure
- **Event Sourcing**: State changes captured as events

### Implementation in This Boilerplate

#### Domain Aggregates as Write Models

```php
// src/App/User/Domain/User.php
class User extends EventSourcedAggregateRoot
{
    private UuidInterface $uuid;
    private Email $email;
    private HashedPassword $hashedPassword;
    private ?DateTime $createdAt = null;
    private ?DateTime $updatedAt = null;

    // Business operations that modify state
    public static function create(
        UuidInterface $uuid,
        Credentials $credentials,
        UniqueEmailSpecificationInterface $uniqueEmailSpecification
    ): self {
        // Business rule enforcement
        $uniqueEmailSpecification->isUnique($credentials->email);

        $user = new self();
        // State change through events
        $user->apply(new UserWasCreated($uuid, $credentials, DateTime::now()));

        return $user;
    }

    public function changeEmail(
        Email $email,
        UniqueEmailSpecificationInterface $uniqueEmailSpecification
    ): void {
        // Business rule enforcement
        $uniqueEmailSpecification->isUnique($email);
        
        // State change through events
        $this->apply(new UserEmailChanged($this->uuid, $email, DateTime::now()));
    }

    public function signIn(string $plainPassword): void
    {
        // Business rule enforcement
        if (!$this->hashedPassword->match($plainPassword)) {
            throw new InvalidCredentialsException('Invalid credentials entered.');
        }

        // Record business event
        $this->apply(new UserSignedIn($this->uuid, $this->email));
    }
}
```

#### Write Model Repository

```php
// src/App/User/Infrastructure/Repository/UserStore.php
class UserStore implements UserRepositoryInterface
{
    public function __construct(
        private readonly EventSourcingRepository $repository
    ) {}

    public function get(UuidInterface $uuid): User
    {
        try {
            // Load aggregate from event stream
            return $this->repository->load($uuid->toString());
        } catch (AggregateNotFoundException $e) {
            throw UserNotFoundException::withId($uuid);
        }
    }

    public function store(User $user): void
    {
        // Store events, not current state
        $this->repository->save($user);
    }
}
```

### Write Model Benefits

1. **Business Logic Centralization**: All business rules in one place
2. **Consistency**: ACID properties for critical operations
3. **Audit Trail**: Complete history through event sourcing
4. **Domain Integrity**: Type safety and validation

## Read Models

Read models focus on data retrieval, performance, and user experience. They prioritize speed and usability over consistency.

### Characteristics

- **Performance Optimized**: Fast queries and data retrieval
- **Denormalized**: Structured for specific read use cases
- **Eventually Consistent**: May lag behind write model
- **Query Specific**: Tailored to UI and reporting needs

### Implementation in This Boilerplate

#### Read Model Data Structure

```php
// src/App/User/Infrastructure/ReadModel/UserView.php
class UserView implements SerializableReadModel
{
    public function __construct(
        private readonly string $uuid,
        private readonly string $email,
        private readonly string $createdAt,
        private readonly ?string $updatedAt = null
    ) {}

    // Optimized for JSON serialization
    public function jsonSerialize(): array
    {
        return [
            'uuid' => $this->uuid,
            'email' => $this->email,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt
        ];
    }

    // Factory methods for different sources
    public static function fromArray(array $data): self
    {
        return new self(
            $data['uuid'],
            $data['email'],
            $data['createdAt'],
            $data['updatedAt'] ?? null
        );
    }

    public static function fromEvent(UserWasCreated $event): self
    {
        return new self(
            $event->uuid->toString(),
            $event->credentials->email->toString(),
            $event->occurredOn->toString()
        );
    }

    // Getters for specific use cases
    public function uuid(): string
    {
        return $this->uuid;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function createdAt(): string
    {
        return $this->createdAt;
    }

    public function updatedAt(): ?string
    {
        return $this->updatedAt;
    }
}
```

#### Read Model Repository

```php
// src/App/User/Infrastructure/ReadModel/Repository/UserReadModelElasticRepository.php
class UserReadModelElasticRepository implements UserReadModelRepositoryInterface
{
    public function __construct(
        private readonly Client $client,
        private readonly string $indexName = 'users'
    ) {}

    public function save(UserView $userView): void
    {
        $this->client->index([
            'index' => $this->indexName,
            'id' => $userView->uuid(),
            'body' => $userView->jsonSerialize()
        ]);
    }

    public function oneByEmailOrNull(Email $email): ?UserView
    {
        $response = $this->client->search([
            'index' => $this->indexName,
            'body' => [
                'query' => [
                    'term' => ['email' => $email->toString()]
                ]
            ]
        ]);

        if (empty($response['hits']['hits'])) {
            return null;
        }

        return UserView::fromArray($response['hits']['hits'][0]['_source']);
    }

    public function findByUuid(UuidInterface $uuid): ?UserView
    {
        try {
            $response = $this->client->get([
                'index' => $this->indexName,
                'id' => $uuid->toString()
            ]);

            return UserView::fromArray($response['_source']);
        } catch (Missing404Exception $e) {
            return null;
        }
    }

    // Complex queries optimized for read use cases
    public function searchUsers(UserSearchCriteria $criteria): Collection
    {
        $query = [
            'index' => $this->indexName,
            'body' => [
                'query' => $this->buildSearchQuery($criteria),
                'sort' => $this->buildSortQuery($criteria),
                'from' => $criteria->offset(),
                'size' => $criteria->limit()
            ]
        ];

        $response = $this->client->search($query);

        $users = array_map(
            fn(array $hit) => UserView::fromArray($hit['_source']),
            $response['hits']['hits']
        );

        return new Collection(
            $criteria->page(),
            $criteria->limit(),
            $response['hits']['total']['value'],
            $users
        );
    }

    private function buildSearchQuery(UserSearchCriteria $criteria): array
    {
        $must = [];

        if ($criteria->hasEmailFilter()) {
            $must[] = [
                'wildcard' => [
                    'email' => '*' . $criteria->emailFilter() . '*'
                ]
            ];
        }

        if ($criteria->hasDateRange()) {
            $must[] = [
                'range' => [
                    'createdAt' => [
                        'gte' => $criteria->dateFrom(),
                        'lte' => $criteria->dateTo()
                    ]
                ]
            ];
        }

        return [
            'bool' => [
                'must' => $must
            ]
        ];
    }
}
```

### Read Model Projections

Read models are built from domain events through projections:

```php
// src/App/User/Infrastructure/ReadModel/Projection/UserProjector.php
class UserProjector extends Projector
{
    public function __construct(
        private readonly UserReadModelRepositoryInterface $repository
    ) {}

    protected function applyUserWasCreated(UserWasCreated $event): void
    {
        $userView = UserView::fromEvent($event);
        $this->repository->save($userView);
    }

    protected function applyUserEmailChanged(UserEmailChanged $event): void
    {
        // Load existing read model
        $userView = $this->repository->findByUuid($event->uuid);
        
        if ($userView) {
            // Update with new data
            $updatedView = new UserView(
                $userView->uuid(),
                $event->email->toString(),
                $userView->createdAt(),
                $event->occurredOn->toString()
            );
            
            $this->repository->save($updatedView);
        }
    }

    protected function applyUserSignedIn(UserSignedIn $event): void
    {
        // Update last sign-in timestamp
        $userView = $this->repository->findByUuid($event->uuid);
        
        if ($userView) {
            $updatedView = new UserView(
                $userView->uuid(),
                $userView->email(),
                $userView->createdAt(),
                DateTime::now()->toString(),
                $event->occurredOn->toString() // lastSignInAt
            );
            
            $this->repository->save($updatedView);
        }
    }
}
```

## Multiple Read Models

Different read models can be created for different use cases:

### User Summary Read Model

```php
// For dashboard and listing views
class UserSummaryView
{
    public function __construct(
        public readonly string $uuid,
        public readonly string $email,
        public readonly string $displayName,
        public readonly string $status,
        public readonly int $signInCount,
        public readonly ?string $lastSignInAt
    ) {}
}

class UserSummaryProjector extends Projector
{
    protected function applyUserWasCreated(UserWasCreated $event): void
    {
        $summary = new UserSummaryView(
            $event->uuid->toString(),
            $event->credentials->email->toString(),
            $event->credentials->email->toString(), // Use email as display name initially
            'active',
            0,
            null
        );
        
        $this->repository->save($summary);
    }

    protected function applyUserSignedIn(UserSignedIn $event): void
    {
        $summary = $this->repository->findByUuid($event->uuid);
        
        if ($summary) {
            $updated = new UserSummaryView(
                $summary->uuid,
                $summary->email,
                $summary->displayName,
                $summary->status,
                $summary->signInCount + 1,
                DateTime::now()->toString()
            );
            
            $this->repository->save($updated);
        }
    }
}
```

### User Analytics Read Model

```php
// For reporting and analytics
class UserAnalyticsView
{
    public function __construct(
        public readonly string $month,
        public readonly int $newUsers,
        public readonly int $activeUsers,
        public readonly int $totalSignIns
    ) {}
}

class UserAnalyticsProjector extends Projector
{
    protected function applyUserWasCreated(UserWasCreated $event): void
    {
        $month = $event->occurredOn->format('Y-m');
        
        $analytics = $this->repository->findByMonth($month) ?? new UserAnalyticsView($month, 0, 0, 0);
        
        $updated = new UserAnalyticsView(
            $analytics->month,
            $analytics->newUsers + 1,
            $analytics->activeUsers,
            $analytics->totalSignIns
        );
        
        $this->repository->save($updated);
    }

    protected function applyUserSignedIn(UserSignedIn $event): void
    {
        $month = DateTime::now()->format('Y-m');
        
        $analytics = $this->repository->findByMonth($month) ?? new UserAnalyticsView($month, 0, 0, 0);
        
        $updated = new UserAnalyticsView(
            $analytics->month,
            $analytics->newUsers,
            $analytics->activeUsers + 1,
            $analytics->totalSignIns + 1
        );
        
        $this->repository->save($updated);
    }
}
```

## Storage Strategies

### Different Storage for Different Needs

```yaml
# config/packages/doctrine.yaml
doctrine:
    dbal:
        connections:
            # Write model - Event store
            write:
                url: '%env(DATABASE_URL)%'
                driver: 'pdo_mysql'
                charset: utf8mb4
                
            # Read model - Optimized for queries
            read:
                url: '%env(READ_DATABASE_URL)%'
                driver: 'pdo_mysql'
                charset: utf8mb4

# ElasticSearch for search-optimized read models
elasticsearch:
    connections:
        users:
            hosts: ['%env(ELASTICSEARCH_URL)%']
            indexes:
                users:
                    settings:
                        number_of_shards: 1
                        number_of_replicas: 0
```

### Read Model Optimization

```php
// Cached read model repository
class CachedUserReadModelRepository implements UserReadModelRepositoryInterface
{
    public function __construct(
        private readonly UserReadModelRepositoryInterface $repository,
        private readonly CacheInterface $cache,
        private readonly int $ttl = 3600
    ) {}

    public function findByUuid(UuidInterface $uuid): ?UserView
    {
        $cacheKey = "user_view:{$uuid->toString()}";
        
        return $this->cache->get($cacheKey, function() use ($uuid) {
            return $this->repository->findByUuid($uuid);
        }, $this->ttl);
    }

    public function oneByEmailOrNull(Email $email): ?UserView
    {
        $cacheKey = "user_view_email:{$email->toString()}";
        
        return $this->cache->get($cacheKey, function() use ($email) {
            return $this->repository->oneByEmailOrNull($email);
        }, $this->ttl);
    }

    public function save(UserView $userView): void
    {
        // Update repository
        $this->repository->save($userView);
        
        // Invalidate relevant cache entries
        $this->cache->delete("user_view:{$userView->uuid()}");
        $this->cache->delete("user_view_email:{$userView->email()}");
    }
}
```

## Synchronization Between Models

### Event-Driven Synchronization

```php
// Events automatically synchronize read models
class UserEventSubscriber
{
    public function __construct(
        private readonly UserReadModelRepositoryInterface $readRepository,
        private readonly UserSummaryRepositoryInterface $summaryRepository,
        private readonly UserAnalyticsRepositoryInterface $analyticsRepository
    ) {}

    public function handleUserWasCreated(UserWasCreated $event): void
    {
        // Update all relevant read models
        $this->updateUserReadModel($event);
        $this->updateUserSummary($event);
        $this->updateAnalytics($event);
    }

    private function updateUserReadModel(UserWasCreated $event): void
    {
        $userView = UserView::fromEvent($event);
        $this->readRepository->save($userView);
    }

    private function updateUserSummary(UserWasCreated $event): void
    {
        $summary = UserSummaryView::fromEvent($event);
        $this->summaryRepository->save($summary);
    }

    private function updateAnalytics(UserWasCreated $event): void
    {
        $month = $event->occurredOn->format('Y-m');
        $analytics = $this->analyticsRepository->incrementNewUsers($month);
    }
}
```

### Rebuilding Read Models

```php
// Command to rebuild read models from events
class RebuildReadModelsCommand extends Command
{
    protected static $defaultName = 'app:rebuild-read-models';

    public function __construct(
        private readonly EventStoreInterface $eventStore,
        private readonly UserReadModelRepositoryInterface $readRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Rebuilding user read models...');

        // Clear existing read models
        $this->readRepository->clear();

        // Replay all events
        $events = $this->eventStore->loadAll();
        $projector = new UserProjector($this->readRepository);

        foreach ($events as $event) {
            $projector->handle($event);
        }

        $output->writeln('Read models rebuilt successfully');

        return Command::SUCCESS;
    }
}
```

## Testing Read and Write Models

### Testing Write Models

```php
class UserWriteModelTest extends TestCase
{
    public function testCanCreateUser(): void
    {
        $user = User::create($uuid, $credentials, $uniqueEmailSpec);
        
        // Test business logic
        $this->assertEquals($credentials->email->toString(), $user->email());
        
        // Test events
        $events = $user->getUncommittedEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(UserWasCreated::class, $events[0]);
    }
}
```

### Testing Read Models

```php
class UserReadModelTest extends TestCase
{
    public function testCanCreateFromEvent(): void
    {
        $event = new UserWasCreated($uuid, $credentials, DateTime::now());
        
        $userView = UserView::fromEvent($event);
        
        $this->assertEquals($uuid->toString(), $userView->uuid());
        $this->assertEquals($credentials->email->toString(), $userView->email());
    }
}
```

### Testing Projections

```php
class UserProjectorTest extends TestCase
{
    public function testProjectsUserWasCreatedEvent(): void
    {
        $repository = $this->createMock(UserReadModelRepositoryInterface::class);
        $projector = new UserProjector($repository);
        
        $event = new UserWasCreated($uuid, $credentials, DateTime::now());
        
        $repository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(UserView::class));
        
        $projector->handle($event);
    }
}
```

## Best Practices

### 1. **Design for Queries**

Structure read models to match query patterns:

```php
// Bad - forces complex queries
class UserView
{
    public string $data; // JSON blob
}

// Good - structured for specific queries
class UserView
{
    public string $uuid;
    public string $email;
    public string $status;
    public string $lastSignInAt;
}
```

### 2. **Embrace Denormalization**

Don't be afraid to duplicate data in read models:

```php
class OrderView
{
    public string $orderId;
    public string $customerEmail;    // Denormalized from customer
    public string $customerName;     // Denormalized from customer
    public array $productNames;      // Denormalized from products
    public float $totalAmount;
}
```

### 3. **Version Read Models**

Plan for read model evolution:

```php
class UserView
{
    public const VERSION = 2;
    
    public string $uuid;
    public string $email;
    public string $displayName; // Added in version 2
}

class UserViewMigrator
{
    public function migrate(array $data): UserView
    {
        $version = $data['version'] ?? 1;
        
        if ($version === 1) {
            $data['displayName'] = $data['email']; // Default value
            $data['version'] = 2;
        }
        
        return UserView::fromArray($data);
    }
}
```

### 4. **Monitor Read Model Lag**

Track how far behind read models are:

```php
class ReadModelHealthCheck
{
    public function checkUserReadModelLag(): array
    {
        $lastEventTime = $this->eventStore->getLastEventTime();
        $lastProjectionTime = $this->userReadRepository->getLastUpdateTime();
        
        $lagSeconds = $lastEventTime->getTimestamp() - $lastProjectionTime->getTimestamp();
        
        return [
            'status' => $lagSeconds < 60 ? 'healthy' : 'lagging',
            'lag_seconds' => $lagSeconds,
            'last_event' => $lastEventTime->format('c'),
            'last_projection' => $lastProjectionTime->format('c')
        ];
    }
}
```

The separation of read and write models enables optimization for specific use cases while maintaining data consistency through event-driven synchronization.