<?php

declare(strict_types=1);

namespace App\Infrastructure\Share\Event\Consumer;

use App\Infrastructure\Share\Bus\Event\Event;
use App\Infrastructure\Share\Bus\Event\EventHandlerInterface;
use App\Infrastructure\Share\Event\Query\ElasticSearchEventRepository;

class SendEventsToElasticConsumer implements EventHandlerInterface
{
    private ElasticSearchEventRepository $eventElasticRepository;

    public function __construct(ElasticSearchEventRepository $eventElasticRepository)
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
