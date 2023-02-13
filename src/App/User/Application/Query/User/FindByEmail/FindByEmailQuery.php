<?php

declare(strict_types=1);

namespace App\User\Application\Query\User\FindByEmail;

use App\Shared\Application\Query\QueryInterface;
use App\User\Domain\ValueObject\Email;
use Assert\AssertionFailedException;

final class FindByEmailQuery implements QueryInterface
{
    /** @psalm-readonly */
    public Email $email;

    /**
     * @throws AssertionFailedException
     */
    public function __construct(string $email)
    {
        $this->email = Email::fromString($email);
    }
}
