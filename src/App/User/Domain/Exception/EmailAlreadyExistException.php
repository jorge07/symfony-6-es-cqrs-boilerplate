<?php

declare(strict_types=1);

namespace App\User\Domain\Exception;

class EmailAlreadyExistException extends \InvalidArgumentException implements \Throwable
{
    public function __construct()
    {
        parent::__construct('Email already registered.');
    }
}
