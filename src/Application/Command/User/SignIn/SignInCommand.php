<?php

declare(strict_types=1);

namespace App\Application\Command\User\SignIn;

use App\Domain\User\ValueObject\Email;
use App\Infrastructure\Share\Bus\CommandInterface;

class SignInCommand implements CommandInterface
{
    /** @var Email */
    public $email;

    /** @var string */
    public $plainPassword;

    /**
     * @throws \Assert\AssertionFailedException
     */
    public function __construct(string $email, string $plainPassword)
    {
        $this->email = Email::fromString($email);
        $this->plainPassword = $plainPassword;
    }
}
