<?php

declare(strict_types=1);

namespace App\Tests\Application\Query\User\FindByEmail;

use App\Application\Command\User\SignUp\SignUpCommand;
use App\Application\Query\Item;
use App\Application\Query\User\FindByEmail\FindByEmailQuery;
use App\Infrastructure\User\Query\Projections\UserView;
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

        /** @var Item $item */
        $item = $this->ask(new FindByEmailQuery($email));
        /** @var UserView $userRead */
        $userRead = $item->readModel;

        self::assertInstanceOf(Item::class, $item);
        self::assertInstanceOf(UserView::class, $userRead);
        self::assertSame($email, $userRead->email());
    }

    private function createUserRead(): string
    {
        $uuid = Uuid::uuid4()->toString();
        $email = 'lol@lol.com';

        $this->handle(new SignUpCommand($uuid, $email, 'password'));

        return $email;
    }
}
