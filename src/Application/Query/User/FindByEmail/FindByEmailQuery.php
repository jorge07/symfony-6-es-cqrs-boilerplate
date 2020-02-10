<?php

declare(strict_types=1);

namespace App\Application\Query\User\FindByEmail;

use App\Domain\User\ValueObject\Email;
use App\Infrastructure\Share\Bus\QueryInterface;

class FindByEmailQuery implements QueryInterface
{
    /** @var Email */
    public $email;

    /**
     * @throws \Assert\AssertionFailedException
     */
    public function __construct(string $email)
    {
        $this->email = Email::fromString($email);
    }
}
