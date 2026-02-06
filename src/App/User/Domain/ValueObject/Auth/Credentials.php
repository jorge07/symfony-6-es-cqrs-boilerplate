<?php

declare(strict_types=1);

namespace App\User\Domain\ValueObject\Auth;

use App\User\Domain\ValueObject\Email;

final class Credentials
{
    public function __construct(
        public readonly Email $email,
        public readonly HashedPassword $password,
    ) {
    }

    public function equals(self $other): bool
    {
        return $this->email->equals($other->email)
            && $this->password->equals($other->password);
    }
}
