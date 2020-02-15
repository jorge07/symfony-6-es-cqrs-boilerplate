<?php

declare(strict_types=1);

namespace App\Tests\Application;

use App\Tests\AMQPTrait;
use Messenger\Bus\CommandBus;
use Messenger\Bus\CommandInterface;
use Messenger\Bus\QueryBus;
use Messenger\Bus\QueryInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;

abstract class ApplicationTestCase extends KernelTestCase
{
    use AMQPTrait;

    /**
     * @throws \Throwable
     */
    protected function ask(QueryInterface $query)
    {
        return $this->queryBus->handle($query);
    }

    /**
     * @throws \Throwable
     */
    protected function handle(CommandInterface $command): void
    {
        $this->commandBus->handle($command);
    }

    /**
     * @return object|null
     */
    protected function service(string $serviceId)
    {
        return self::$container->get($serviceId);
    }

    protected function fireTerminateEvent(): void
    {
        /** @var EventDispatcherInterface $dispatcher */
        $dispatcher = $this->service(EventDispatcherInterface::class);

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
        $kernel = self::bootKernel();

        $this->setApplication(new Application($kernel));
        $this->commandBus = $this->service(CommandBus::class);
        $this->queryBus = $this->service(QueryBus::class);

        $this->purgeQueue();
    }

    protected function tearDown(): void
    {
        $this->commandBus = null;
        $this->queryBus = null;
    }

    /** @var CommandBus|null */
    private $commandBus;

    /** @var QueryBus|null */
    private $queryBus;
}
