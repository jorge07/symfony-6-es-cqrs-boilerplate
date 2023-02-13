<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Event\Consumer;

use App\Shared\Infrastructure\Event\ReadModel\ElasticSearchEventRepository;
use Broadway\Domain\DomainMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'messenger.bus.event.async', fromTransport: 'events', priority: 10)]
class SendEventsToElasticConsumer
{
    public function __construct(private readonly ElasticSearchEventRepository $eventElasticRepository)
    {
    }

    public function __invoke(DomainMessage $event): void
    {
        $this->eventElasticRepository->store($event);
    }
}
