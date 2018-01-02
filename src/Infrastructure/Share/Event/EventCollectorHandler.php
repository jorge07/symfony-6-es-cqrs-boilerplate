<?php

namespace App\Infrastructure\Share\Event;

use Broadway\Domain\DomainMessage;

class EventCollectorHandler
{
    private $publishedEvents = [];

    public function __invoke(DomainMessage $message)
    {
        $this->publishedEvents[] = $message;
    }

    public function popEvents(): array
    {
        $events = $this->publishedEvents;

        $this->publishedEvents = [];

        return $events;
    }
}
