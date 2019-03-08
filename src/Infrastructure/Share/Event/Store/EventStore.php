<?php

declare(strict_types=1);

namespace App\Infrastructure\Share\Event\Store;

use Broadway\EventHandling\EventBus;
use Broadway\EventSourcing\AggregateFactory\PublicConstructorAggregateFactory;
use Broadway\EventSourcing\EventSourcingRepository;
use Broadway\EventStore\EventStore as BroadwayEventStore;

abstract class EventStore extends EventSourcingRepository
{
    abstract protected static function aggregate(): string;

    public function __construct(
        BroadwayEventStore $eventStore,
        EventBus $eventBus,
        array $eventStreamDecorators = []
    ) {
        parent::__construct(
            $eventStore,
            $eventBus,
            $this->aggregate(),
            new PublicConstructorAggregateFactory(),
            $eventStreamDecorators
        );
    }
}
