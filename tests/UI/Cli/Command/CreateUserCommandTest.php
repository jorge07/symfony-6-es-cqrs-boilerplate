<?php

declare(strict_types=1);

namespace Tests\UI\Cli\Command;

use App\Shared\Application\Command\CommandBusInterface;
use App\Shared\Application\Query\Item;
use App\User\Application\Query\User\FindByEmail\FindByEmailQuery;
use Assert\AssertionFailedException;
use Ramsey\Uuid\Uuid;
use Tests\UI\Cli\AbstractConsoleTestCase;
use Throwable;
use UI\Cli\Command\CreateUserCommand;

class CreateUserCommandTest extends AbstractConsoleTestCase
{
    /**
     * @test
     *
     * @group e2e
     *
     * @throws Throwable
     * @throws AssertionFailedException
     */
    public function command_integration_with_bus_success(): void
    {
        $email = 'jorge.arcoma@gmail.com';

        /** @var CommandBusInterface $commandBus */
        $commandBus = $this->service(CommandBusInterface::class);
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

        self::assertInstanceOf(Item::class, $result);
        self::assertSame('UserView', $result->type);
        self::assertSame($email, $result->resource['credentials.email']->toString());
    }
}
