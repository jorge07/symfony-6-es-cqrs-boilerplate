<?php

declare(strict_types=1);

namespace App\Infrastructure\User\Repository;

use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\User;
use App\Domain\User\ValueObject\Uuid;
use Broadway\EventHandling\EventBus;
use Broadway\EventSourcing\AggregateFactory\PublicConstructorAggregateFactory;
use Broadway\EventSourcing\EventSourcingRepository;
use Broadway\EventStore\EventStore;

class UserStore extends EventSourcingRepository implements UserRepositoryInterface
{
    public function store(User $user): void
    {
        $this->save($user);
    }

    public function get(Uuid $uuid): User
    {
        /** @var User $user */
        $user = $this->load((string) $uuid);

        return $user;
    }

    public function __construct(
        EventStore $eventStore,
        EventBus $eventBus,
        array $eventStreamDecorators = []
    ) {
        parent::__construct(
            $eventStore,
            $eventBus,
            User::class,
            new PublicConstructorAggregateFactory(),
            $eventStreamDecorators
        );
    }
}
