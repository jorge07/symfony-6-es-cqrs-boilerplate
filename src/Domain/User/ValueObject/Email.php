<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObject;

use App\Domain\Shared\ValueObject\AbstractString;
use Assert\Assertion;
use Assert\AssertionFailedException;

class Email extends AbstractString
{
    /**
     * @throws AssertionFailedException
     */
    protected function __construct(string $value)
    {
        Assertion::email($value, 'Not a valid email');

        parent::__construct($value);
    }
}
