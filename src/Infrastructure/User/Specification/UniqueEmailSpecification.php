<?php

declare(strict_types=1);

namespace App\Infrastructure\User\Specification;

use App\Domain\User\Exception\EmailAlreadyExistException;
use App\Domain\User\Repository\CheckUserByEmailInterface;
use App\Domain\User\Specification\UniqueEmailSpecificationInterface;
use App\Domain\User\ValueObject\Email;

final class UniqueEmailSpecification implements UniqueEmailSpecificationInterface
{
    /**
     * @throws EmailAlreadyExistException
     */
    public function isUnique(Email $email): bool
    {
        if ($this->checkUserByEmail->existsEmail($email)) {
            throw new EmailAlreadyExistException();
        }

        return true;
    }

    public function __construct(CheckUserByEmailInterface $checkUserByEmail)
    {
        $this->checkUserByEmail = $checkUserByEmail;
    }

    /**
     * @var CheckUserByEmailInterface
     */
    private $checkUserByEmail;
}
