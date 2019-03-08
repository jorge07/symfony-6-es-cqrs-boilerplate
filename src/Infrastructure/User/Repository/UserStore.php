<?php

declare(strict_types=1);

namespace App\Infrastructure\User\Repository;

use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\User;
use App\Infrastructure\Share\Event\Store\EventStore;
use Ramsey\Uuid\UuidInterface;

final class UserStore extends EventStore implements UserRepositoryInterface
{
    public function store(User $user): void
    {
        $this->save($user);
    }

    public function get(UuidInterface $uuid): User
    {
        /** @var User $user */
        $user = $this->load($uuid->toString());

        return $user;
    }

    protected static function aggregate(): string
    {
        return User::class;
    }
}
