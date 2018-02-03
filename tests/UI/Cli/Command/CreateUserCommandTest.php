<?php

declare(strict_types=1);

namespace App\Tests\UI\Cli\Command;

use App\UI\Cli\Command\CreateUserCommand;
use League\Tactician\CommandBus;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class CreateUserCommandTest extends KernelTestCase
{
    /**
     * @test
     *
     * @group unit
     */
    public function command_integration_with_bus_success()
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        /** @var CommandBus $commandBus */
        $commandBus = $kernel->getContainer()->get('tactician.commandbus.command');
        $application->add(new CreateUserCommand($commandBus));

        $command = $application->find('app:create-user');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command'  => $command->getName(),

            'uuid' => Uuid::uuid4()->toString(),
            'email' => 'jorge.arcoma@gmail.com'
        ));

        $output = $commandTester->getDisplay();

        $this->assertContains('User Created:', $output);
        $this->assertContains('Email: jorge.arcoma@gmail.com', $output);
    }
}
