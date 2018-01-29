<?php

declare(strict_types=1);

namespace App\Infrastructure\Share\Query\Repository;

use App\Domain\Shared\Query\Exception\NotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

abstract class MysqlRepository
{
    public function register($model): void
    {
        $this->entityManager->persist($model);
        $this->apply();
    }

    public function apply(): void
    {
        $this->entityManager->flush();
    }

    protected function oneOrException(QueryBuilder $queryBuilder)
    {
        $model = $queryBuilder
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if (null === $model) {

            throw new NotFoundException();
        }

        return $model;
    }

    protected function setRepository(string $model): void
    {
        $this->repository = $this->entityManager->getRepository($model);
    }

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /** @var EntityRepository */
    protected $repository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
}
