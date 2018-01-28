<?php

declare(strict_types=1);

namespace App\Infrastructure\User\Query\Mysql;

use App\Domain\User\Query\UserRead;
use App\Domain\User\Query\UserReadModelRepositoryInterface;
use App\Domain\User\ValueObject\Email;
use App\Infrastructure\Share\Query\Repository\MysqlRepository;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\UuidInterface;

class MysqlUserReadModelRepository extends MysqlRepository implements UserReadModelRepositoryInterface
{
    public function oneByUuid(UuidInterface $uuid): UserRead
    {
        $qb = $this->repository
            ->createQueryBuilder('user')
            ->where('user.uuid = :uuid')
            ->setParameter('uuid', $uuid->getBytes())
        ;

        return $this->oneOrException($qb);
    }

    public function oneByEmail(Email $email): UserRead
    {
        $qb = $this->repository
            ->createQueryBuilder('user')
            ->where('user.email = :email')
            ->setParameter('email', $email->toString())
        ;

        return $this->oneOrException($qb);
    }

    public function add(UserRead $userRead): void
    {
        $this->register($userRead);
    }

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager);

        $this->setRepository(UserRead::class);
    }
}
