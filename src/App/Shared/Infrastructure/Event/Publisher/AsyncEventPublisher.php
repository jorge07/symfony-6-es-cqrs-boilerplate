<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Event\Publisher;

use App\Shared\Infrastructure\Bus\AsyncEvent\MessengerAsyncEventBus;
use Broadway\Domain\DomainMessage;
use Broadway\EventHandling\EventListener;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Throwable;

final class AsyncEventPublisher implements EventSubscriberInterface, EventListener
{
    /** @var DomainMessage[] */
    private array $messages = [];

    private MessengerAsyncEventBus $bus;

    public function __construct(MessengerAsyncEventBus $bus)
    {
        $this->bus = $bus;
    }

    public function handle(DomainMessage $message): void
    {
        $this->messages[] = $message;
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
        if (empty($this->messages)) {
            return;
        }

        foreach ($this->messages as $message) {
            $this->bus->handle($message);
        }
    }
}
