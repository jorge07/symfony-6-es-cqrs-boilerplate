<?php

declare(strict_types=1);

namespace App\Infrastructure\User\Query\Mysql;

use App\Domain\User\Repository\CheckUserByEmailInterface;
use App\Domain\User\Repository\GetUserCredentialsByEmailInterface;
use App\Domain\User\ValueObject\Email;
use App\Infrastructure\Share\Query\Repository\MysqlRepository;
use App\Infrastructure\User\Query\Projections\UserView;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\UuidInterface;

final class MysqlUserReadModelRepository extends MysqlRepository implements CheckUserByEmailInterface, GetUserCredentialsByEmailInterface
{
    /**
     * @throws \App\Domain\Shared\Query\Exception\NotFoundException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function oneByUuid(UuidInterface $uuid): UserView
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
    public function existsEmail(Email $email): ?UuidInterface
    {
        $userId = $this->repository
            ->createQueryBuilder('user')
            ->select('user.uuid')
            ->where('user.credentials.email = :email')
            ->setParameter('email', (string) $email)
            ->getQuery()
            ->setHydrationMode(AbstractQuery::HYDRATE_ARRAY)
            ->getOneOrNullResult()
        ;

        return $userId['uuid'] ?? null;
    }

    /**
     * @throws \App\Domain\Shared\Query\Exception\NotFoundException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function oneByEmail(Email $email): UserView
    {
        $qb = $this->repository
            ->createQueryBuilder('user')
            ->where('user.credentials.email = :email')
            ->setParameter('email', $email->toString())
        ;

        return $this->oneOrException($qb);
    }

    public function add(UserView $userRead): void
    {
        $this->register($userRead);
    }

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->class = UserView::class;
        parent::__construct($entityManager);
    }

    /**
     * @throws \App\Domain\Shared\Query\Exception\NotFoundException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getCredentialsByEmail(Email $email): array
    {
        $user = $this->oneByEmail($email);

        return [
            $user->uuid(),
            $user->email(),
            $user->hashedPassword(),
        ];
    }
}
