<?php

declare(strict_types=1);

namespace App\Application\Command\User\SignIn;

use App\Application\Command\CommandMessage;
use App\Domain\User\ValueObject\Email;

class SignInCommand implements CommandMessage
{
    /**
     * @var Email
     */
    public $email;

    /**
     * @var string
     */
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
