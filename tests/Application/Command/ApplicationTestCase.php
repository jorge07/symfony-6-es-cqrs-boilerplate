<?php

declare(strict_types=1);

namespace App\Tests\Application\Command;

use League\Tactician\CommandBus;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
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

        $dispatcher->dispatch(KernelEvents::TERMINATE);
    }

    protected function setUp()
    {
        $kernel = static::bootKernel();

        $this->container = $kernel->getContainer();
        $this->commandBus = $this->container->get('tactician.commandbus.command');
        $this->queryBus = $this->container->get('tactician.commandbus.query');
    }

    protected function tearDown()
    {
        $this->container = null;
        $this->commandBus = null;
        $this->queryBus = null;

    }

    /** @var ContainerInterface */
    private $container;
    /** @var CommandBus */
    private $commandBus;
    /** @var CommandBus */
    private $queryBus;
}
