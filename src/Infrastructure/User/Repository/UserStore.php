<?php

declare(strict_types=1);

namespace App\Infrastructure\User\Repository;

use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\User;
use Broadway\Domain\AggregateRoot;
use Broadway\EventHandling\EventBus;
use Broadway\EventSourcing\AggregateFactory\PublicConstructorAggregateFactory;
use Broadway\EventSourcing\EventSourcingRepository;
use Broadway\EventStore\EventStore;
use Ramsey\Uuid\UuidInterface;

class UserStore extends EventSourcingRepository implements UserRepositoryInterface
{
    public function store(User $user): void
    {
        $this->save($user);
    }

    public function get(UuidInterface $uuid): User
    {
        /** @var AggregateRoot|User $user */
        $user = $this->load((string) $uuid);

        return $user;
    }

    public function __construct(
        EventStore $eventStore,
        EventBus $broadwayBusBridge,
        array $eventStreamDecorators = array()
    ) {
        parent::__construct(
            $eventStore,
            $broadwayBusBridge,
            User::class,
            new PublicConstructorAggregateFactory(),
            $eventStreamDecorators
        );
    }
}
