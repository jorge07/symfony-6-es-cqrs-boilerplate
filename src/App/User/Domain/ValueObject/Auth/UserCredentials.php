<?php

declare(strict_types=1);

namespace App\User\Domain\ValueObject\Auth;

use App\User\Domain\ValueObject\Email;
use Ramsey\Uuid\UuidInterface;

final class UserCredentials
{
    public function __construct(
        public readonly UuidInterface $uuid,
        public readonly Email $email,
        public readonly HashedPassword $password,
    ) {
    }
}
