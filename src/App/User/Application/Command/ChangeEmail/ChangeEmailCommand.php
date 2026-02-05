<?php

declare(strict_types=1);

namespace App\User\Application\Command\ChangeEmail;

use App\Shared\Application\Command\CommandInterface;
use App\User\Domain\ValueObject\Email;
use Assert\AssertionFailedException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class ChangeEmailCommand implements CommandInterface
{
    public readonly UuidInterface $userUuid;

    public readonly Email $email;

    /**
     * @throws AssertionFailedException
     */
    public function __construct(string $userUuid, string $email)
    {
        $this->userUuid = Uuid::fromString($userUuid);
        $this->email = Email::fromString($email);
    }
}
