<?php

declare(strict_types=1);

namespace App\Application\Command\User\ChangeEmail;

use App\Domain\User\ValueObject\Email;
use Ramsey\Uuid\Uuid;

class ChangeEmailCommand
{
    /** @var \Ramsey\Uuid\UuidInterface */
    public $userUuid;

    /** @var Email */
    public $email;

    public function __construct(string $userUuid, string $email)
    {
        $this->userUuid = Uuid::fromString($userUuid);
        $this->email = Email::fromString($email);
    }
}
