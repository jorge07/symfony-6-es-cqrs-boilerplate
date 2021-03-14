<?php

declare(strict_types=1);

namespace App\User\Domain\Specification;

use App\User\Domain\Exception\EmailAlreadyExistException;
use App\User\Domain\ValueObject\Email;

interface UniqueEmailSpecificationInterface
{
    /**
     * @throws EmailAlreadyExistException
     */
    public function isUnique(Email $email): bool;
}
