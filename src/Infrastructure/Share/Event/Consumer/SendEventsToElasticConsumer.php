<?php

declare(strict_types=1);

namespace App\Infrastructure\Share\Event\Consumer;

use App\Infrastructure\Share\Event\Event;
use App\Infrastructure\Share\Event\EventHandlerInterface;
use App\Infrastructure\Share\Event\Query\EventElasticRepository;

class SendEventsToElasticConsumer implements EventHandlerInterface
{
    private EventElasticRepository $eventElasticRepository;

    public function __construct(EventElasticRepository $eventElasticRepository)
    {
        $this->eventElasticRepository = $eventElasticRepository;
    }

    public function __invoke(Event $event): void
    {
        $this->eventElasticRepository->store(
            $event->getDomainMessage()
        );
    }
}
