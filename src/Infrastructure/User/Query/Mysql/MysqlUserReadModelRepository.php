<?php

declare(strict_types=1);

namespace App\Infrastructure\User\Query\Mysql;

use App\Domain\User\Query\Projections\UserViewInterface;
use App\Domain\User\Query\Repository\UserReadModelRepositoryInterface;
use App\Domain\User\Repository\CheckUserByEmailInterface;
use App\Domain\User\ValueObject\Email;
use App\Domain\User\ValueObject\Uuid;
use App\Infrastructure\Share\Query\Repository\AbstractMysqlRepository;
use App\Infrastructure\User\Query\Projections\UserView;
use Doctrine\ORM\EntityManagerInterface;

class MysqlUserReadModelRepository extends AbstractMysqlRepository implements UserReadModelRepositoryInterface, CheckUserByEmailInterface
{
    /**
     * @throws \App\Domain\Shared\Query\Exception\NotFoundException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function oneByUuid(Uuid $uuid): UserViewInterface
    {
        $qb = $this->repository
            ->createQueryBuilder('user')
            ->where('user.uuid = :uuid')
            ->setParameter('uuid', $uuid->getBytes())
        ;

        return $this->oneOrException($qb);
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function existsEmail(Email $email): ?Uuid
    {
        $userId = $this->repository
            ->createQueryBuilder('user')
            ->select('user.uuid')
            ->where('user.credentials.email = :email')
            ->setParameter('email', (string) $email)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        return $userId['uuid'] ?? null;
    }

    /**
     * @throws \App\Domain\Shared\Query\Exception\NotFoundException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function oneByEmail(Email $email): UserViewInterface
    {
        $qb = $this->repository
            ->createQueryBuilder('user')
            ->where('user.credentials.email = :email')
            ->setParameter('email', $email->toString())
        ;

        return $this->oneOrException($qb);
    }

    public function add(UserViewInterface $userRead): void
    {
        $this->register($userRead);
    }

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->class = UserView::class;
        parent::__construct($entityManager);
    }
}
