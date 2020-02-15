<?php

declare(strict_types=1);

namespace App\Infrastructure\Share\Event\Consumer;

use App\Infrastructure\Share\Event\Query\EventElasticRepository;
use Messenger\Bus\Handler\EventHandlerInterface;
use Messenger\Event\EventInterface;

class SendEventsToElasticConsumer implements EventHandlerInterface
{
    /** @var EventElasticRepository */
    private $eventElasticRepository;

    public function __construct(EventElasticRepository $eventElasticRepository)
    {
        $this->eventElasticRepository = $eventElasticRepository;
    }

    public function __invoke(EventInterface $event): void
    {
        $this->eventElasticRepository->store($event);
    }
}
