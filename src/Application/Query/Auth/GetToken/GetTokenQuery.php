<?php

declare(strict_types=1);

namespace App\Application\Query\Auth\GetToken;

use App\Domain\User\ValueObject\Email;
use App\Infrastructure\Share\Bus\QueryInterface;

class GetTokenQuery implements QueryInterface
{
    /** @var Email */
    public $email;

    public function __construct(string $email)
    {
        $this->email = Email::fromString($email);
    }
}
