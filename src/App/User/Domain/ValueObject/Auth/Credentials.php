<?php

declare(strict_types=1);

namespace App\User\Domain\ValueObject\Auth;

use App\User\Domain\ValueObject\Email;
use Doctrine\ORM\Mapping as ORM;

// ORM attributes are an intentional trade-off: PHP 8 attributes for Doctrine mapping
// avoid the complexity of separate XML/YAML mapping files while keeping the value object
// in the Domain layer. This couples Domain to Doctrine annotations but simplifies the
// mapping configuration significantly for embedded value objects.
#[ORM\Embeddable]
final class Credentials
{
    public function __construct(
        #[ORM\Column(name: 'email', type: 'email', unique: true)]
        public readonly Email $email,
        #[ORM\Column(name: 'password', type: 'hashed_password')]
        public readonly HashedPassword $password,
    ) {
    }

    public function equals(self $other): bool
    {
        return $this->email->equals($other->email)
            && $this->password->equals($other->password);
    }
}
