<?php

declare(strict_types=1);

namespace App\Tests\UI\Cli\Command;

use App\Application\Query\Item;
use App\Application\Query\User\FindByEmail\FindByEmailQuery;
use App\Infrastructure\Share\Bus\CommandBus;
use App\Infrastructure\User\Query\Projections\UserView;
use App\Tests\UI\Cli\AbstractConsoleTestCase;
use App\UI\Cli\Command\CreateUserCommand;
use Ramsey\Uuid\Uuid;

class CreateUserCommandTest extends AbstractConsoleTestCase
{
    /**
     * @test
     *
     * @group unit
     *
     * @throws \Exception
     * @throws \Assert\AssertionFailedException
     */
    public function command_integration_with_bus_success(): void
    {
        $email = 'jorge.arcoma@gmail.com';

        /** @var CommandBus $commandBus */
        $commandBus = $this->service(CommandBus::class);
        $commandTester = $this->app($command = new CreateUserCommand($commandBus), 'app:create-user');

        $commandTester->execute([
            'command' => $command->getName(),
            'uuid' => Uuid::uuid4()->toString(),
            'email' => $email,
            'password' => 'jorgepass',
        ]);

        $output = $commandTester->getDisplay();

        $this->assertStringContainsString('User Created:', $output);
        $this->assertStringContainsString('Email: jorge.arcoma@gmail.com', $output);

        /** @var Item $result */
        $result = $this->ask(new FindByEmailQuery($email));

        /** @var UserView $userRead */
        $userRead = $result->readModel;

        self::assertInstanceOf(Item::class, $result);
        self::assertInstanceOf(UserView::class, $userRead);
        self::assertSame($email, $userRead->email());
    }
}
