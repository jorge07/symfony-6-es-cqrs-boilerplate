<?php

declare(strict_types=1);

namespace App\Tests\UI\Cli\Command;

use App\Application\Query\Item;
use App\Application\Query\User\FindByEmail\FindByEmailQuery;
use App\Infrastructure\User\Query\UserView;
use App\Tests\UI\Cli\AbstractConsoleTestCase;
use App\UI\Cli\Command\CreateUserCommand;
use League\Tactician\CommandBus;
use Ramsey\Uuid\Uuid;

class CreateUserCommandTest extends AbstractConsoleTestCase
{
    /**
     * @test
     *
     * @group unit
     */
    public function command_integration_with_bus_success()
    {
        $email = 'jorge.arcoma@gmail.com';

        /** @var CommandBus $commandBus */
        $commandBus = $this->service('tactician.commandbus.command');
        $commandTester = $this->app($command = new CreateUserCommand($commandBus), 'app:create-user');

        $commandTester->execute([
            'command'  => $command->getName(),
            'uuid'     => Uuid::uuid4()->toString(),
            'email'    => $email,
            'password' => 'jorgepass',
        ]);

        $output = $commandTester->getDisplay();

        $this->assertContains('User Created:', $output);
        $this->assertContains('Email: jorge.arcoma@gmail.com', $output);

        /** @var Item $item */
        $item = $this->ask(new FindByEmailQuery($email));
        /** @var UserView $userRead */
        $userRead = $item->readModel;

        self::assertInstanceOf(Item::class, $item);
        self::assertInstanceOf(UserView::class, $userRead);
        self::assertEquals($email, $userRead->credentials->email);
    }
}
