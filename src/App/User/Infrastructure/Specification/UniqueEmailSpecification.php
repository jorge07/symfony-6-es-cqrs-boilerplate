<?php

declare(strict_types=1);

namespace App\User\Infrastructure\Specification;

use App\User\Domain\Exception\EmailAlreadyExistException;
use App\User\Domain\Repository\CheckUserByEmailInterface;
use App\User\Domain\Specification\UniqueEmailSpecificationInterface;
use App\User\Domain\ValueObject\Email;
use Doctrine\ORM\NonUniqueResultException;

/**
 * Application-level uniqueness check. A UNIQUE index on credentials_email
 * (see Version20200727170306 migration) acts as a safety net at the DB level.
 */
final class UniqueEmailSpecification implements UniqueEmailSpecificationInterface
{
    public function __construct(private readonly CheckUserByEmailInterface $checkUserByEmail)
    {
    }

    /**
     * @throws EmailAlreadyExistException
     */
    public function isUnique(Email $email): bool
    {
        try {
            if ($this->checkUserByEmail->findUuidByEmail($email)) {
                throw new EmailAlreadyExistException();
            }
        } catch (NonUniqueResultException) {
            throw new EmailAlreadyExistException();
        }

        return true;
    }
}
