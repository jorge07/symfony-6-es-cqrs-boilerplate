<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Share\Event;

use Messenger\Event\EventInterface;
use Messenger\EventSubscriber\ProjectionEventSubscriber;
use Messenger\Projection\Event\ProjectorEvent;

class EventCollectorListener extends ProjectionEventSubscriber
{
    public function handleProjection(ProjectorEvent $projectorEvent): void
    {
        $this->publishedEvents[] = $projectorEvent->getEvent();
    }

    /**
     * @return EventInterface[]
     */
    public function popEvents(): array
    {
        $events = $this->publishedEvents;

        $this->publishedEvents = [];

        return $events;
    }

    /** @var EventInterface[] */
    private $publishedEvents = [];
}
