<?php

declare(strict_types=1);

namespace App\Task\Application\Command\Create;

use App\Shared\Application\Command\CommandHandlerInterface;
use App\Task\Domain\TaskFactory;
use App\Task\Domain\TaskRepository;

final class CreateTaskHandler implements CommandHandlerInterface
{
    public function __construct(
        private TaskFactory $taskFactory,
        private TaskRepository $taskRepository
    )
    {}

    public function __invoke(CreateTaskCommand $command): void
    {
        $task = $this->taskFactory->create([
            'uuid' => $command->uuid,
            'userId' => $command->userId,
            'title' => $command->title,
            'completedAt' => $command->completed,
        ]);

        $this->taskRepository->persist($task);
    }
}
