<?php

declare(strict_types=1);

namespace App\Application\Query\Event\GetEvents;

use App\Application\Query\Collection;
use App\Application\Query\QueryHandlerInterface;
use App\Domain\Shared\Event\EventRepositoryInterface;
use App\Domain\Shared\Query\Exception\NotFoundException;

class GetEventsHandler implements QueryHandlerInterface
{
    private EventRepositoryInterface $eventRepository;

    public function __construct(EventRepositoryInterface $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    /**
     * @throws NotFoundException
     */
    public function __invoke(GetEventsQuery $query): Collection
    {
        $result = $this->eventRepository->page($query->page, $query->limit);

        return new Collection($query->page, $query->limit, $result['total']['value'], $result['data']);
    }
}
