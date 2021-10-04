<?php

namespace Tests\App\Task\Application\Command\Create;

use App\Task\Application\Command\Create\CreateTaskCommand;
use App\Task\Domain\Task;
use App\Task\Domain\TaskRepository;
use App\User\Application\Command\SignUp\SignUpCommand;
use Ramsey\Uuid\Uuid;
use Tests\App\Shared\Application\ApplicationTestCase;
use Throwable;

class CreateTaskHandlerTest  extends ApplicationTestCase
{
    /**
     * @test
     *
     * @group integration
     *
     * @throws Throwable
     */
    public function task_should_be_persisted_once_created(): void
    {
        $uuid = Uuid::uuid4();
        $email = 'asd@asd.asd';
        $taskUuid = Uuid::uuid4();

        $command = new SignUpCommand($uuid->toString(), $email, 'password');
        $this->handle($command);

        $createTaskCommand = new CreateTaskCommand(
            $taskUuid->toString(),
            $uuid->toString(),
            'Test',
            null
        );

        $this->handle($createTaskCommand);

        /** @var TaskRepository $repository */
        $repository = $this->service(TaskRepository::class);

        $task = $repository->getByUUID($taskUuid);
        self::assertInstanceOf(Task::class, $task);
    }
}
