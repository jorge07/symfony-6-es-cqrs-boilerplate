<?php

declare(strict_types=1);

namespace App\Tests\Application\Query\User\FindByEmail;

use App\Application\Command\User\SignUp\SignUpCommand;
use App\Application\Query\Item;
use App\Application\Query\User\FindByEmail\FindByEmailQuery;
use App\Infrastructure\User\Query\Projections\UserView;
use App\Tests\Application\ApplicationTestCase;
use Ramsey\Uuid\Uuid;

class FindByEmailHandlerTest extends ApplicationTestCase
{
    /**
     * @test
     *
     * @group integration
     *
     * @throws \Exception
     * @throws \Assert\AssertionFailedException
     */
    public function query_command_integration(): void
    {
        $email = $this->createUserRead();

        $this->fireTerminateEvent();

        /** @var Item $result */
        $result = $this->ask(new FindByEmailQuery($email));

        /** @var UserView $userRead */
        $userRead = $result->readModel;

        self::assertInstanceOf(Item::class, $result);
        self::assertInstanceOf(UserView::class, $userRead);
        self::assertSame($email, $userRead->email());
    }

    /**
     * @throws \Exception
     * @throws \Assert\AssertionFailedException
     */
    private function createUserRead(): string
    {
        $uuid = Uuid::uuid4()->toString();
        $email = 'lol@lol.com';

        $this->handle(new SignUpCommand($uuid, $email, 'password'));

        return $email;
    }
}
