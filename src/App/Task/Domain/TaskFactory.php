<?php

declare(strict_types=1);

namespace App\Task\Domain;

interface TaskFactory
{
    public function create(array $data): Task;

    public function update(Task $task, array $data): Task;

    public function delete(string $id): void;
}
