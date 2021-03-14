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

    /** @psalm-readonly */
    public string $plainPassword;

    /**
     * @throws AssertionFailedException
     */
    public function __construct(string $email, string $plainPassword)
    {
        $this->email = Email::fromString($email);
        $this->plainPassword = $plainPassword;
    }
}
