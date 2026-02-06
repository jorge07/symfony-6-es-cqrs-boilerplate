<?php

declare(strict_types=1);

namespace App\User\Domain\ValueObject\Auth;

use App\User\Domain\ValueObject\Email;

class Credentials
{
    public function __construct(public Email $email, public HashedPassword $password)
    {
    }
}
