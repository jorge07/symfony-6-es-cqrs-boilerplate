<?php

declare(strict_types=1);

namespace App\User\Domain\ValueObject\Auth;

use App\User\Domain\ValueObject\Email;

class Credentials
{
    public Email $email;

    public HashedPassword $password;

    public function __construct(Email $email, HashedPassword $password)
    {
        $this->email = $email;
        $this->password = $password;
    }
}
