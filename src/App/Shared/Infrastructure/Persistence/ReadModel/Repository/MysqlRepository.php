<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Persistence\ReadModel\Repository;

use App\Shared\Infrastructure\Persistence\ReadModel\Exception\NotFoundException;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use Throwable;

abstract class MysqlRepository
{
    protected EntityRepository $repository;

    protected EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->setEntityManager();
    }

    /**
     * @important Hold on
     * I don't like this neither but I'm facing this and I don't know how to fix it:
     * docker-compose -f docker-compose.yml -f etc/dev/docker-compose.yml run --rm code sh -lc './vendor/bin/phpstan analyse -l 6 -c phpstan.neon src tests'
        116/116 [▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓] 100%

        ------ -------------------------------------------------------------------------------------------------------------
        Line   src/Infrastructure/Shared/Persistence/ReadModel/Repository/MysqlRepository.php
        ------ -------------------------------------------------------------------------------------------------------------
        25     Unable to resolve the template type T in call to method Doctrine\Persistence\ObjectManager::getRepository()
        ------ -------------------------------------------------------------------------------------------------------------


        [ERROR] Found 1 error
     *
     * If you know how to solve this let me know please and I'll owe you a beer
     */
    abstract protected function setEntityManager(): void;

    /**
     * @param mixed $model
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

    public function isHealthy(): bool
    {
        $connection = $this->entityManager->getConnection();

        try {
            $dummySelectSQL = $connection->getDatabasePlatform()->getDummySelectSQL();
            $connection->executeQuery($dummySelectSQL);

            return true;
        } catch (Throwable $exception) {
            $connection->close();

            return false;
        }
    }
}
