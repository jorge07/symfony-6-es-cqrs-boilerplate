<?php

declare(strict_types=1);

namespace App\Shared\Application\Query\Event\GetEvents;

use App\Shared\Application\Query\QueryInterface;

final class GetEventsQuery implements QueryInterface
{
    public function __construct(
        /** @psalm-readonly */
        public int $page = 1,
        /** @psalm-readonly */
        public int $limit = 50
    )
    {
    }
}
