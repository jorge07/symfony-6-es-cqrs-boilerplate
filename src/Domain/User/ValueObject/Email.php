<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObject;

use App\Domain\Shared\ValueObject\AbstractString;
use Assert\Assertion;
use Assert\AssertionFailedException;

/**
 * @method static Email fromString(string $value)
 */
class Email extends AbstractString
{
    /**
     * @throws AssertionFailedException
     */
    protected static function create(string $value): self
    {
        Assertion::email($value, 'Not a valid email');

        return new self($value);
    }
}
