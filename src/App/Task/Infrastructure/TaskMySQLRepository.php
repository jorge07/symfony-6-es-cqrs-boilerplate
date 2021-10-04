<?php

declare(strict_types=1);

namespace App\Task\Infrastructure;

use App\Shared\Infrastructure\Persistence\ReadModel\Exception\NotFoundException;
use App\Shared\Infrastructure\Persistence\ReadModel\Repository\MysqlRepository;
use App\Task\Domain\Task;
use App\Task\Domain\TaskRepository;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\ORMException;
use Ramsey\Uuid\UuidInterface;

final class TaskMySQLRepository extends MysqlRepository implements TaskRepository
{
    protected function setEntityManager(): void
    {
        /** @var EntityRepository $objectRepository */
        $objectRepository = $this->entityManager->getRepository(Task::class);
        $this->repository = $objectRepository;
    }

    /**
     * @throws NotFoundException
     * @throws NonUniqueResultException
     */
    public function getByUUID(UuidInterface $uuid): Task
    {
        return $this->oneOrException($this->repository
            ->createQueryBuilder('t')
            ->select('t')
            ->where('t.uuid = :uuid')
            ->setParameter('uuid', $uuid->getBytes())
        );
    }

    public function persist(Task $task): void
    {
        $this->register($task);
    }

    /**
     * @throws ORMException
     */
    public function delete(string $taskID): void
    {
        $this->entityManager
            ->getReference(Task::class, $taskID)
            ->remove()
            ->flush()
        ;
    }

    public function getByUserUUID(UuidInterface $userUuid): array
    {
        return $this->repository
            ->createQueryBuilder('t')
            ->select('t')
            ->where('t.userId = :userId')
            ->setParameter('userId', $userUuid->getBytes())
            ->getQuery()
            ->getResult()
        ;
    }
}
