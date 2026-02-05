<?php

declare(strict_types=1);

namespace App\User\Domain\ValueObject\Auth;

use App\User\Domain\ValueObject\Email;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
class Credentials
{
    #[ORM\Column(name: 'email', type: 'email', unique: true)]
    public Email $email;

    #[ORM\Column(name: 'password', type: 'hashed_password')]
    public HashedPassword $password;

    public function __construct(Email $email, HashedPassword $password)
    {
        $this->email = $email;
        $this->password = $password;
    }
}
