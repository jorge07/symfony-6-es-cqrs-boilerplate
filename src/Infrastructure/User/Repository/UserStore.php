<?php

declare(strict_types=1);

namespace App\Infrastructure\User\Repository;

use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\User;
use Messenger\Aggregate\AggregateRootId;
use Messenger\Repository\EventSourcingRepository;
use Ramsey\Uuid\UuidInterface;

final class UserStore extends EventSourcingRepository implements UserRepositoryInterface
{
    public function store(User $user): void
    {
        $this->save($user);
    }

    public function get(UuidInterface $uuid): User
    {
        /** @var User $user */
        $user = $this->load(AggregateRootId::fromUUID($uuid));

        return $user;
    }

    public function getAggregateRoot(): string
    {
        return User::class;
    }
}
