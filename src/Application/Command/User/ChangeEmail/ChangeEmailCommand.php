<?php

declare(strict_types=1);

namespace App\Application\Command\User\ChangeEmail;

use App\Application\Command\CommandInterface;
use App\Domain\User\ValueObject\Email;
use Assert\AssertionFailedException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class ChangeEmailCommand implements CommandInterface
{
    /** @psalm-readonly */
    public UuidInterface $userUuid;

    /** @psalm-readonly */
    public Email $email;

    /**
     * @throws AssertionFailedException
     */
    public function __construct(string $userUuid, string $email)
    {
        $this->userUuid = Uuid::fromString($userUuid);
        $this->email = Email::fromString($email);
    }
}
