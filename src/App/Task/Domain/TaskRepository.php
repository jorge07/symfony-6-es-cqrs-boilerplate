<?php

declare(strict_types=1);

namespace App\Task\Domain;

use Ramsey\Uuid\UuidInterface;

interface TaskRepository
{
    public function getByUUID(UuidInterface $uuid): Task;

    /**
     * @return Task[]
     */
    public function getByUserUUID(UuidInterface $userUuid): array;

    public function persist(Task $task): void;

    public function delete(string $taskID): void;
}
