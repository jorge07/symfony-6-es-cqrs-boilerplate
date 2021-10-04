<?php

declare(strict_types=1);

namespace App\Task\Infrastructure;

use App\Shared\Infrastructure\Form\AbstractForm;
use App\Task\Domain\Task;
use App\Task\Domain\TaskFactory;
use Symfony\Component\Form\FormFactoryInterface;

final class TaskFormFactory extends AbstractForm implements TaskFactory
{
    public function __construct(FormFactoryInterface $formFactory)
    {
        parent::__construct($formFactory, TaskSymfonyForm::class);
    }

    public function create(array $data): Task
    {
        /** @var Task $task */
        $task = $this->execute(self::CREATE, $data);

        return $task;
    }

    public function update(Task $task, array $data): Task
    {
        $this->execute(self::UPDATE, $data, $task);

        return $task;
    }

    public function delete(string $id): void
    {
        // TODO: Implement delete() method.
    }
}
