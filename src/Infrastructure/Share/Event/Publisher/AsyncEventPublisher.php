<?php

declare(strict_types=1);

namespace App\Infrastructure\Share\Event\Publisher;

use App\Infrastructure\Share\Bus\EventBus;
use App\Infrastructure\Share\Event\Event;
use Broadway\Domain\DomainMessage;
use Broadway\EventHandling\EventListener;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Throwable;

final class AsyncEventPublisher implements EventPublisher, EventSubscriberInterface, EventListener
{
    /** @var DomainMessage[] */
    private array $events = [];

    private EventBus $bus;

    public function __construct(EventBus $bus)
    {
        $this->bus = $bus;
    }

    public function handle(DomainMessage $message): void
    {
        $this->events[] = $message;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::TERMINATE => 'publish',
            ConsoleEvents::TERMINATE => 'publish',
        ];
    }

    /**
     * @throws Throwable
     */
    public function publish(): void
    {
        if (empty($this->events)) {
            return;
        }

        foreach ($this->events as $event) {
            $this->bus->handle(new Event($event));
        }
    }
}
