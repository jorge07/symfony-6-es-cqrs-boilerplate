<?php

declare(strict_types=1);

namespace App\Tests\Application\Query\User\FindByEmail;

use App\Application\Command\User\Create\CreateUserCommand;
use App\Application\Query\User\FindByEmail\FindByEmailQuery;
use App\Domain\User\Query\UserView;
use App\Tests\Application\Command\ApplicationTestCase;
use Ramsey\Uuid\Uuid;

class FindByEmailHandlerTest extends ApplicationTestCase
{

    /**
     * @test
     *
     * @group integration
     */
    public function query_command_integration()
    {
        $email = $this->createUserRead();

        $this->fireTerminateEvent();

        $userRead = $this->ask(new FindByEmailQuery($email));

        self::assertInstanceOf(UserView::class, $userRead);
    }

    private function createUserRead(): string
    {
        $uuid = Uuid::uuid4()->toString();
        $email = 'lol@lol.com';

        $this->handle(new CreateUserCommand($uuid, $email, 'password'));

        return $email;
    }
}
