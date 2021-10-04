<?php

declare(strict_types=1);

namespace App\Task\Application\Command\Update;

use App\Shared\Application\Command\CommandHandlerInterface;
use App\Task\Domain\TaskFactory;
use App\Task\Domain\TaskRepository;

final class UpdateTaskHandler implements CommandHandlerInterface
{
    public function __construct(
        private TaskFactory $taskFactory,
        private TaskRepository $taskRepository
    )
    {}

    public function __invoke(UpdateTaskCommand $command): void
    {
        $task = $this->taskRepository->getByUUID($command->uuid);

        $this->taskFactory->update($task, $command->payload);

        $this->taskRepository->persist($task);
    }
}
