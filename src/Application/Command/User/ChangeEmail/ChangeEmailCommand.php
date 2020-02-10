<?php

declare(strict_types=1);

namespace App\Application\Command\User\ChangeEmail;

use App\Domain\User\ValueObject\Email;
use App\Infrastructure\Share\Bus\CommandInterface;
use Ramsey\Uuid\Uuid;

class ChangeEmailCommand implements CommandInterface
{
    /** @var \Ramsey\Uuid\UuidInterface */
    public $userUuid;

    /** @var Email */
    public $email;

    /**
     * @throws \Assert\AssertionFailedException
     */
    public function __construct(string $userUuid, string $email)
    {
        $this->userUuid = Uuid::fromString($userUuid);
        $this->email = Email::fromString($email);
    }
}
