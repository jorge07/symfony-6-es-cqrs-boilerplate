<?php

namespace App\Application\Command\Event;

use App\Application\Command\CommandHandlerInterface;
use App\Domain\Shared\Event\Processor\EventStreamProcessorInterface;
use App\Domain\Shared\Event\Repository\IterableAggregateEventStoreInterface;

class ReplayEventsHandler implements CommandHandlerInterface
{
    public function __invoke(ReplayEventsCommand $command): void
    {
        foreach ($this->iterableDbalEventStore as $stream) {
            $this->eventStreamProcessor->process($stream);
        }
    }

    public function __construct(
        EventStreamProcessorInterface $eventStreamProcessor,
        IterableAggregateEventStoreInterface $iterableDbalEventStore
    ) {
        $this->eventStreamProcessor = $eventStreamProcessor;
        $this->iterableDbalEventStore = $iterableDbalEventStore;
    }

    /**
     * @var EventStreamProcessorInterface
     */
    private $eventStreamProcessor;

    /**
     * @var IterableAggregateEventStoreInterface
     */
    private $iterableDbalEventStore;
}
