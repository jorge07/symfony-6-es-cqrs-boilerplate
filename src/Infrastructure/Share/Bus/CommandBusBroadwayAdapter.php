<?php

declare(strict_types=1);

namespace App\Infrastructure\Share\Bus;

use Broadway\Domain\DomainEventStream;
use Broadway\EventHandling\EventBus;
use Broadway\EventHandling\EventListener;
use League\Tactician\CommandBus as TacticianBus;

class CommandBusBroadwayAdapter implements EventBus
{
    public function publish(DomainEventStream $domainMessages): void
    {
        foreach ($domainMessages as $events) {

            $this->eventBus->handle($events);
        }
    }

    public function subscribe(EventListener $eventListener)
    {
        // Not apply
    }

    public function __construct(TacticianBus $eventBus)
    {
        $this->eventBus = $eventBus;
    }

    /**
     * @var TacticianBus
     */
    private $eventBus;
}
