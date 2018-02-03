<?php

declare(strict_types=1);

namespace App\Tests\Application\Command;

use League\Tactician\CommandBus;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
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
        return $this->container->get($serviceId);
    }

    protected function fireTerminateEvent()
    {
        /** @var EventDispatcherInterface $dispatcher */
        $dispatcher = $this->service('event_dispatcher');

        $dispatcher->dispatch(
            KernelEvents::TERMINATE,
            new PostResponseEvent(
                static::$kernel,
                Request::create('/'),
                Response::create()
            )
        );
    }

    protected function setUp()
    {
        static::bootKernel();

        $this->container = static::$kernel->getContainer();

        /** @var CommandBus $commandBus */
        $commandBus = $this->container->get('tactician.commandbus.command');
        $this->commandBus = $commandBus;

        /** @var CommandBus $queryBus */
        $queryBus = $this->container->get('tactician.commandbus.query');
        $this->queryBus = $queryBus;
    }

    protected function tearDown()
    {
        $this->container = null;
        $this->commandBus = null;
        $this->queryBus = null;
    }

    /** @var ContainerInterface|null */
    private $container;
    /** @var CommandBus|null */
    private $commandBus;
    /** @var CommandBus|null */
    private $queryBus;
}
