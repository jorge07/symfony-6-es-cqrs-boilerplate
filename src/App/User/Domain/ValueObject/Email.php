<?php

declare(strict_types=1);

namespace App\User\Domain\ValueObject;

use Assert\Assertion;
use Assert\AssertionFailedException;
use JsonSerializable;

final class Email implements JsonSerializable
{
    private string $email;

    private function __construct(string $email)
    {
        $this->email = $email;
    }

    /**
     * @throws AssertionFailedException
     */
    public static function fromString(string $email): self
    {
        Assertion::email($email, 'Not a valid email');

        return new self($email);
    }

    public function toString(): string
    {
        return $this->email;
    }

    public function __toString(): string
    {
        return $this->email;
    }

    public function jsonSerialize(): string
    {
        return $this->toString();
    }
}
