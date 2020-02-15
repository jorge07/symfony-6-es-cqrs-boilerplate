<?php

declare(strict_types=1);

namespace App\Infrastructure\Share\Event\Query;

use App\Domain\Shared\Event\EventRepositoryInterface;
use App\Infrastructure\Share\Query\Repository\ElasticRepository;
use Messenger\Event\EventInterface;

final class EventElasticRepository extends ElasticRepository implements EventRepositoryInterface
{
    private const INDEX = 'events';

    public function store(EventInterface $event): void
    {
        $document = [
            'type' => \get_class($event),
            'payload' => $event->serialize(),
        ];

        $this->add($document);
    }

    public function __construct(array $elasticConfig)
    {
        parent::__construct($elasticConfig, self::INDEX);
    }
}
