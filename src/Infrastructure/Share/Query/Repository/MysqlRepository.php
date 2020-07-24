<?php

declare(strict_types=1);

namespace App\Infrastructure\Share\Query\Repository;

use App\Domain\Shared\Query\Exception\NotFoundException;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;

abstract class MysqlRepository
{
    protected EntityRepository $repository;

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->setRepository($this->getClass());
    }

    abstract protected function getClass(): string;

    /**
     * @param object $model
     */
    public function register($model): void
    {
        $this->entityManager->persist($model);
        $this->apply();
    }

    public function apply(): void
    {
        $this->entityManager->flush();
    }

    /**
     * @return mixed
     *
     * @throws NotFoundException
     * @throws NonUniqueResultException
     */
    protected function oneOrException(QueryBuilder $queryBuilder, int $hydration = AbstractQuery::HYDRATE_OBJECT)
    {
        $model = $queryBuilder
            ->getQuery()
            ->getOneOrNullResult($hydration)
        ;

        if (null === $model) {
            throw new NotFoundException();
        }

        return $model;
    }

    private function setRepository(string $model): void
    {
        /** @var EntityRepository $objectRepository */
        $objectRepository = $this->entityManager->getRepository($model);
        $this->repository = $objectRepository;
    }
}
