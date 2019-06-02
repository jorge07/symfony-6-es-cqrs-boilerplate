<?php

declare(strict_types=1);

namespace App\Tests\Application;

use League\Tactician\CommandBus;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;

abstract class ApplicationTestCase extends KernelTestCase
{
    protected function ask($query)
    {
        return $this->queryBus->handle($query);
    }

    protected function handle($command): void
    {
        $this->commandBus->handle($command);
    }

    protected function service(string $serviceId)
    {
        return self::$container->get($serviceId);
    }

    protected function fireTerminateEvent(): void
    {
        /** @var EventDispatcher $dispatcher */
        $dispatcher = $this->service('event_dispatcher');

        $dispatcher->dispatch(
            new TerminateEvent(
                static::$kernel,
                Request::create('/'),
                Response::create()
            ),
            KernelEvents::TERMINATE
        );
    }

    protected function setUp(): void
    {
        static::bootKernel();

        $this->commandBus = $this->service('tactician.commandbus.command');

        $this->queryBus = $this->service('tactician.commandbus.query');
    }

    protected function tearDown(): void
    {
        $this->commandBus = null;
        $this->queryBus = null;
    }

    /** @var CommandBus|null */
    private $commandBus;

    /** @var CommandBus|null */
    private $queryBus;
}
