<?php

declare(strict_types=1);

namespace App\Task\Application\Query\FindAll;

use App\Shared\Application\Query\QueryHandlerInterface;
use App\Task\Domain\TaskRepository;

final class FindAllHandler implements QueryHandlerInterface
{
    public function __construct(private TaskRepository $taskRepository)
    {}

    public function __invoke(FindAllQuery $query): array
    {
        return $this->taskRepository->getByUserUUID($query->userUuid);
    }
}
