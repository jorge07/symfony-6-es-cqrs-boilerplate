<?php

declare(strict_types=1);

namespace App\Application\Query\Event\GetEvents;

use App\Application\Query\Collection;
use App\Application\Query\QueryHandlerInterface;
use App\Infrastructure\Shared\Event\ReadModel\ElasticSearchEventRepository;
use App\Infrastructure\Shared\Persistence\ReadModel\Exception\NotFoundException;
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
