<?php

namespace App\Domain\User\Query;

use App\Domain\User\ValueObject\Email;
use Ramsey\Uuid\UuidInterface;

interface UserReadModelRepositoryInterface
{
    public function oneByUuid(UuidInterface $uuid): UserRead;

    public function oneByEmail(Email $email): UserRead;

    public function add(UserRead $userRead): void;

    public function apply(): void;
}
