<?php

declare(strict_types=1);

namespace App\Shared\Application\Query\Event\GetEvents;

use App\Shared\Application\Query\Collection;
use App\Shared\Application\Query\QueryHandlerInterface;
use App\Shared\Infrastructure\Event\ReadModel\ElasticSearchEventRepository;
use App\Shared\Infrastructure\Persistence\ReadModel\Exception\NotFoundException;
use Assert\AssertionFailedException;

final class GetEventsHandler implements QueryHandlerInterface
{
    private ElasticSearchEventRepository $eventRepository;

    public function __construct(ElasticSearchEventRepository $eventRepository)
    {
        $this->eventRepository = $eventRepository;
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
