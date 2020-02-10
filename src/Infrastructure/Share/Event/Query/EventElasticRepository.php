<?php

declare(strict_types=1);

namespace App\Infrastructure\Share\Event\Query;

use App\Domain\Shared\Event\EventRepositoryInterface;
use App\Infrastructure\Share\Query\Repository\ElasticRepository;
use Broadway\Domain\DomainMessage;

final class EventElasticRepository extends ElasticRepository implements EventRepositoryInterface
{
    private const INDEX = 'events';

    public function store(DomainMessage $message): void
    {
        $document = [
            'type' => $message->getType(),
            'payload' => $message->getPayload()->serialize(),
            'occurred_on' => $message->getRecordedOn()->toString(),
        ];

        $this->add($document);
    }

    public function __construct(array $elasticConfig)
    {
        parent::__construct($elasticConfig, self::INDEX);
    }
}
