<?php

declare(strict_types=1);

namespace App\Tests\Application\Query\User\FindByEmail;

use App\Application\Command\User\SignUp\SignUpCommand;
use App\Application\Query\Item;
use App\Application\Query\User\FindByEmail\FindByEmailQuery;
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

        /** @var Item $userRead */
        $userRead = $this->ask(new FindByEmailQuery($email));

        self::assertInstanceOf(Item::class, $userRead);
        self::assertArrayHasKey('uuid', $userRead->resource);
        self::assertArrayHasKey('credentials', $userRead->resource);
    }

    private function createUserRead(): string
    {
        $uuid = Uuid::uuid4()->toString();
        $email = 'lol@lol.com';

        $this->handle(new SignUpCommand($uuid, $email, 'password'));

        return $email;
    }
}
