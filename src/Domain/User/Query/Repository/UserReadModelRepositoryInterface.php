<?php

namespace App\Domain\User\Query\Repository;

use App\Domain\User\ValueObject\Email;
use App\Infrastructure\User\Query\UserView;
use Ramsey\Uuid\UuidInterface;

interface UserReadModelRepositoryInterface
{
    public function oneByUuid(UuidInterface $uuid): UserView;

    public function oneByEmail(Email $email): UserView;

    public function add(UserView $userRead): void;

    public function apply(): void;
}
