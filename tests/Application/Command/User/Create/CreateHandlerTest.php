<?php

namespace App\Tests\Application\Command\User\Create;

use App\Application\Command\User\Create\CreateUserCommand;
use App\Application\Query\User\FindByEmail\FindByEmailQuery;
use App\Domain\User\Query\UserRead;
use App\Tests\Application\Command\ApplicationTestCase;
use App\Tests\Infrastructure\Share\Bus\EventCollectorMiddleware;
use Ramsey\Uuid\Uuid;

class CreateHandlerTest extends ApplicationTestCase
{
    /**
     * @test
     *
     * @group integration
     */
    public function command_handler_must_fire_domain_event()
    {
        $uuid = Uuid::uuid4();
        $email = 'asd@asd.asd';

        $command = new CreateUserCommand($uuid, $email);
        $this
            ->handle($command);

        /** @var EventCollectorMiddleware $collector */
        $collector = $this->service(EventCollectorMiddleware::class);

        self::assertCount(1, $collector->popEvents());
    }

    /**
     * @test
     *
     * @group integration
     */
    public function create_a_user_should_generate_a_mysql_projection()
    {
        $uuid = Uuid::uuid4();
        $email = 'asd@asd.asd';

        $command = new CreateUserCommand($uuid, $email);
        $this
            ->handle($command);

        /** @var UserRead $readUser */
        $readUser = $this->ask(new FindByEmailQuery($email));

        self::assertEquals($uuid->toString(), $readUser->getId());
    }
}
