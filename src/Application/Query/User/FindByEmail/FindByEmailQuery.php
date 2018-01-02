<?php

namespace App\Application\Query\User\FindByEmail;

use App\Domain\User\ValueObject\Email;

class FindByEmailQuery
{
    /**
     * @var Email
     */
    public $email;

    public function __construct(string $email)
    {
        $this->email = Email::fromString($email);
    }
}
