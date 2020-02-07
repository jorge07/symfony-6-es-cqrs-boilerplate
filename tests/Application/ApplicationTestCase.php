<?php

declare(strict_types=1);

namespace App\Tests\Application;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Throwable;

abstract class ApplicationTestCase extends KernelTestCase
{
    protected function ask($query)
    {
        try {
            $command = $this->commandBus->dispatch($query);

            /** @var HandledStamp $stamp */
            $stamp = $command->last(HandledStamp::class);

            return $stamp->getResult();
        } catch (HandlerFailedException $e) {
            while ($e instanceof HandlerFailedException) {
                /** @var Throwable $e */
                $e = $e->getPrevious();
            }

            throw $e;
        }
    }

    protected function handle($command): void
    {
        try {
            $this->commandBus->dispatch($command);
        } catch (HandlerFailedException $e) {
            while ($e instanceof HandlerFailedException) {
                /** @var Throwable $e */
                $e = $e->getPrevious();
            }

            throw $e;
        }
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
        self::bootKernel();

        $this->commandBus = $this->service('messenger.bus.command');

        $this->queryBus = $this->service('messenger.bus.query');
    }

    protected function tearDown(): void
    {
        $this->commandBus = null;
        $this->queryBus = null;
    }

    /** @var MessageBusInterface|null */
    private $commandBus;

    /** @var MessageBusInterface|null */
    private $queryBus;
}
