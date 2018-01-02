<?php

declare(strict_types=1);

namespace App\Tests\Application\Command\User\ChangeEmail;

use App\Application\Command\User\ChangeEmail\ChangeEmailCommand;
use App\Application\Command\User\Create\CreateUserCommand;
use App\Infrastructure\Share\Event\EventCollectorHandler;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ChangeEmailHandlerTest extends KernelTestCase
{
    /**
     * @test
     *
     * @group integration
     */
    public function command_bus_integration_test()
    {
        $kernel = self::bootKernel();

        $command = new CreateUserCommand($uuid = Uuid::uuid4()->toString(), 'asd@asd.asd');

        $bus = $kernel
            ->getContainer()
            ->get('tactician.commandbus.command');

        $bus
            ->handle($command)
        ;

        $command = new ChangeEmailCommand($uuid, 'lol@asd.asd');

        $bus
            ->handle($command)
        ;

        /** @var EventCollectorHandler $eventCollector */
        $eventCollector = $kernel->getContainer()->get(EventCollectorHandler::class);

        self::assertCount(2, $eventCollector->popEvents());
    }
}
