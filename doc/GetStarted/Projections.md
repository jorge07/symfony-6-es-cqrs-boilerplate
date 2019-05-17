# Creating a new Projection

A Projection is a representation of a stream of events (aggregates) into a structural representation, usually called, read model.

Let's say we want to store the list of emails in a separated ElasticSearch index for testing purpose.


#### Infrastructure implementation

```php
<?php

declare(strict_types=1);

namespace App\Infrastructure\User\Query\Projections;

use App\Domain\User\ValueObject\Email;
use Broadway\Serializer\Serializable;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class UserListProjection implements SerializableReadModel
{
    /** @var UuidInterface */
    public $uuid;

    /** @var Email */
    public $email;

    public static function fromSerializable(Serializable $event): self
    {
        return self::deserialize($event->serialize());
    }

    public static function deserialize(array $data): self
    {
        $instance = new self();

        $instance->uuid = Uuid::fromString($data['uuid']);
        $instance->email = Email::fromString($data['email']);

        return $instance;
    }

    public function serialize(): array
    {
        return [
            'uuid'  => $this->getId(),
            'email' => (string) $this->email,
        ];
    }

    public function getId(): string
    {
        return $this->uuid->toString();
    }
}
```

> Then you need to implement the Infrastructure for this. Something like `App\Infrastructure\User\Query\Repository\UserEmailListElasticSearchRepository`

#### Create the Projector Listener

```php
<?php

declare(strict_types=1);

namespace App\Infrastructure\User\Query;

use App\Domain\User\Event\UserWasCreated;
use App\Infrastructure\User\Query\Projections\UserListProjection;
use Broadway\ReadModel\Projector;

class UserEmailReadProjectionFactory extends Projector
{
    protected function applyUserWasCreated(UserWasCreated $userWasCreated): void
    {
        $userReadModel = UserListProjection::deserialize([
            'uuid' => $userWasCreated->uuid,
            'email' => $userWasCreated->credentials->email
		]);

        $this->repository->add($userReadModel);
    }

    protected function applyUserEmailChanged(UserEmailChanged $emailChanged): void
    {
        $this->repository->replace($emailChanged->uuid, $emailChanged->email);
    }
    public function __construct(UserEmailListReadModelRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /** @var UserEmailListReadModelRepositoryInterface */
    private $repository;
}
```

And you're done. 

### Why this works?

`Broadway\ReadModel\Projector` implements `Broadway\EventHandling\EventListener` so it's automatically added to the service container and tagged as a Broadway event listener.

`config/services.yaml`
```yaml

services:
    ...
    _instanceof:
        ...
        Broadway\EventHandling\EventListener:
          public: true
          tags:
              - { name: broadway.domain.event_listener }
```
The `Broadway/EventSourcing/EventSourcingRepository::save` method will store the events in the EventStore and publish all the events in the event bus: 

```php
<?php
...
	public function save(AggregateRoot $aggregate): void
	{
	    // maybe we can get generics one day.... ;)
	    Assert::isInstanceOf($aggregate, $this->aggregateClass);
	    $domainEventStream = $aggregate->getUncommittedEvents();
	    $eventStream = $this->decorateForWrite($aggregate, $domainEventStream);
	    $this->eventStore->append($aggregate->getAggregateRootId(), $eventStream);
	    $this->eventBus->publish($eventStream);
	}
```

The projections are automatically added to the EventBus by the Compiler pass of `broadway-bundle`, [see here](https://github.com/broadway/broadway-bundle/blob/master/src/DependencyInjection/RegisterBusSubscribersCompilerPass.php#L66)
