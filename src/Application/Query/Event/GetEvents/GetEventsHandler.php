<?php

declare(strict_types=1);

namespace App\Application\Query\Event\GetEvents;

use App\Domain\Shared\Event\EventRepositoryInterface;
use App\Domain\Shared\Query\Exception\NotFoundException;
use App\Infrastructure\Share\Bus\Query\Collection;
use App\Infrastructure\Share\Bus\Query\QueryHandlerInterface;

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
        $result = $this->eventRepository->page($query->getPage(), $query->getLimit());

        return new Collection($query->getPage(), $query->getLimit(), $result['total']['value'], $result['data']);
    }
}
