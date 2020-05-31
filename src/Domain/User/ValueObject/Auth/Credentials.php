<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObject\Auth;

use App\Domain\User\ValueObject\Email;
use Symfony\Component\Serializer\Annotation\Groups;

class Credentials
{
    /** @Groups({"credentials", "credentials_sensitive"}) */
    public Email $email;

    /** @Groups("credentials_sensitive") */
    public HashedPassword $password;

    public function __construct(Email $email, HashedPassword $password)
    {
        $this->email = $email;
        $this->password = $password;
    }
}
