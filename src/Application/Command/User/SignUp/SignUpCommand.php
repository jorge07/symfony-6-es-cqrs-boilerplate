<?php

declare(strict_types=1);

namespace App\Application\Command\User\SignUp;

use App\Domain\User\ValueObject\Auth\Credentials;
use App\Domain\User\ValueObject\Auth\HashedPassword;
use App\Domain\User\ValueObject\Email;
use App\Infrastructure\Share\Bus\CommandInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class SignUpCommand implements CommandInterface
{
    /** @var UuidInterface */
    public $uuid;

    /** @var Credentials */
    public $credentials;

    /**
     * @throws \Assert\AssertionFailedException
     */
    public function __construct(string $uuid, string $email, string $plainPassword)
    {
        $this->uuid = Uuid::fromString($uuid);
        $this->credentials = new Credentials(Email::fromString($email), HashedPassword::encode($plainPassword));
    }
}
