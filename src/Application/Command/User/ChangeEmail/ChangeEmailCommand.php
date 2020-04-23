<?php

declare(strict_types=1);

namespace App\Application\Command\User\ChangeEmail;

use App\Domain\User\ValueObject\Email;
use App\Infrastructure\Share\Bus\Command\CommandInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class ChangeEmailCommand implements CommandInterface
{
    private UuidInterface $userUuid;

    private Email $email;

    /**
     * @throws \Assert\AssertionFailedException
     */
    public function __construct(string $userUuid, string $email)
    {
        $this->userUuid = Uuid::fromString($userUuid);
        $this->email = Email::fromString($email);
    }

    public function getUserUuid(): UuidInterface
    {
        return $this->userUuid;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }
}
