<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObject;

use Assert\Assertion;

class Email
{
    /**
     * @throws \Assert\AssertionFailedException
     */
    public static function fromString(string $email): self
    {
        Assertion::email($email, 'Not a valid email');

        $mail = new self();

        $mail->email = $email;

        return $mail;
    }

    public function toString(): string
    {
        return $this->email;
    }

    public function __toString(): string
    {
        return $this->email;
    }

    private function __construct()
    {
    }

    /** @var string */
    private $email;
}
