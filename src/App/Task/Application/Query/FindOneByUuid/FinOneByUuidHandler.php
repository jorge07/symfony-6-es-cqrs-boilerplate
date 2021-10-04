<?php

declare(strict_types=1);

namespace App\Task\Application\Query\FindOneByUuid;

use App\Shared\Application\Query\QueryHandlerInterface;
use App\Task\Domain\Task;
use App\Task\Domain\TaskRepository;

final class FinOneByUuidHandler implements QueryHandlerInterface
{
    public function __construct(private TaskRepository $taskRepository)
    {
    }

    public function __invoke(FindOneByUuidQuery $query): Task
    {
        return $this->taskRepository->getByUUID($query->uuid);
    }
}
