<?php

declare(strict_types=1);

namespace App\Shared\Application\Query\Event\GetEvents;

use App\Shared\Application\Query\Collection;
use App\Shared\Application\Query\QueryHandlerInterface;
use App\Shared\Domain\Exception\NotFoundException;
use App\Shared\Domain\Repository\EventRepositoryInterface;
use Assert\AssertionFailedException;

final class GetEventsHandler implements QueryHandlerInterface
{
    public function __construct(private readonly EventRepositoryInterface $eventRepository)
    {
    }

    /**
     * @throws AssertionFailedException
     * @throws NotFoundException
     */
    public function __invoke(GetEventsQuery $query): Collection
    {
        $result = $this->eventRepository->page($query->page, $query->limit);

        return new Collection($query->page, $query->limit, $result['total']['value'], $result['data']);
    }
}
