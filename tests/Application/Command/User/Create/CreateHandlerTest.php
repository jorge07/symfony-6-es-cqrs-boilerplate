<?php

namespace App\Tests\Application\Command\User\Create;

use App\Application\Command\User\Create\CreateUserCommand;
use App\Infrastructure\Share\Event\EventCollectorHandler;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CreateHandlerTest extends KernelTestCase
{
    /**
     * @test
     *
     * @group integration
     */
    public function command_bus_integration_test()
{
    $kernel = self::bootKernel();

    $command = new CreateUserCommand(Uuid::uuid4(), 'asd@asd.asd');

    $bus = $kernel
        ->getContainer()
        ->get('tactician.commandbus.command');

    $bus
        ->handle($command)
    ;

    /** @var EventCollectorHandler $eventCollector */
    $eventCollector = $kernel->getContainer()->get(EventCollectorHandler::class);

    self::assertCount(1, $eventCollector->popEvents());
}
}
