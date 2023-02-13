<?php

declare(strict_types=1);

namespace App\User\Application\Command\SignIn;

use App\Shared\Application\Command\CommandInterface;
use App\User\Domain\ValueObject\Email;
use Assert\AssertionFailedException;

final class SignInCommand implements CommandInterface
{
    /** @psalm-readonly */
    public Email $email;

    /**
     * @throws AssertionFailedException
     */
    public function __construct(string $email, /** @psalm-readonly */
    public string $plainPassword)
    {
        $this->email = Email::fromString($email);
    }
}
