<?php

declare(strict_types=1);

namespace App\Application\Query\Event\GetEvents;

use App\Application\Query\Collection;
use App\Application\Query\QueryHandlerInterface;
use App\Domain\Shared\Event\EventRepositoryInterface;

class GetEventsHandler implements QueryHandlerInterface
{
    /**
     * @throws \App\Domain\Shared\Query\Exception\NotFoundException
     */
    public function __invoke(GetEventsQuery $query): Collection
    {
        $result = $this->eventRepository->page($query->page, $query->limit);

        return new Collection($query->page, $query->limit, $result['total'], $result['data']);
    }

    public function __construct(EventRepositoryInterface $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    /** @var EventRepositoryInterface */
    private $eventRepository;
}
