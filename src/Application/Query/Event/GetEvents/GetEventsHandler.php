<?php

declare(strict_types=1);

namespace App\Application\Query\Event\GetEvents;

use App\Application\Query\QueryHandlerInterface;
use App\Domain\Shared\Event\EventRepositoryInterface;

class GetEventsHandler implements QueryHandlerInterface
{

    public function __invoke(GetEventsQuery $query): array
    {
        return $this->eventRepository->page($query->page, $query->limit);
    }

    public function __construct(EventRepositoryInterface $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;
}
