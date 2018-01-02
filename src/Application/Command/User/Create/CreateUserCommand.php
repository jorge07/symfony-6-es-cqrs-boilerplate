<?php

namespace App\Application\Command\User\Create;

use App\Domain\User\ValueObject\Email;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class CreateUserCommand
{
    /**
     * @var UuidInterface
     */
    public $uuid;

    /**
     * @var Email
     */
    public $email;

    public function __construct(string $uuid, string $email)
    {
        $this->uuid = Uuid::fromString($uuid);
        $this->email = Email::fromString($email);
    }
}
