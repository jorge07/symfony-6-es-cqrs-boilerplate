<?php

declare(strict_types=1);

namespace App\User\Domain\Exception;

class EmailAlreadyExistException extends \LogicException
{
    public function __construct()
    {
        parent::__construct('Email already registered.');
    }
}
