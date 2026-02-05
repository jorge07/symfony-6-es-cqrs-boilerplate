<?php

declare(strict_types=1);

namespace App\User\Application\Command\SignUp;

use App\Shared\Application\Command\CommandInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class SignUpCommand implements CommandInterface
{
    public readonly UuidInterface $uuid;

    public function __construct(string $uuid, public readonly string $email, public readonly string $plainPassword)
    {
        $this->uuid = Uuid::fromString($uuid);
    }
}
