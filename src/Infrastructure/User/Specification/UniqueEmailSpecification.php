<?php

declare(strict_types=1);

namespace App\Infrastructure\User\Specification;

use App\Domain\Shared\Specification\AbstractSpecification;
use App\Domain\User\Exception\EmailAlreadyExistException;
use App\Domain\User\Repository\CheckUserByEmailInterface;
use App\Domain\User\Specification\UniqueEmailSpecificationInterface;
use App\Domain\User\ValueObject\Email;
use Doctrine\ORM\NonUniqueResultException;

final class UniqueEmailSpecification extends AbstractSpecification implements UniqueEmailSpecificationInterface
{
    private CheckUserByEmailInterface $checkUserByEmail;

    public function __construct(CheckUserByEmailInterface $checkUserByEmail)
    {
        $this->checkUserByEmail = $checkUserByEmail;
    }

    /**
     * @throws EmailAlreadyExistException
     */
    public function isUnique(Email $email): bool
    {
        return $this->isSatisfiedBy($email);
    }

    /**
     * @param Email $value
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function isSatisfiedBy($value): bool
    {
        try {
            if ($this->checkUserByEmail->existsEmail($value)) {
                throw new EmailAlreadyExistException();
            }
        } catch (NonUniqueResultException $e) {
            throw new EmailAlreadyExistException();
        }

        return true;
    }
}
