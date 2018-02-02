<?php

declare(strict_types=1);

namespace App\Infrastructure\Share\Event\Query;

use App\Infrastructure\Share\Query\Repository\ElasticRepository;
use Broadway\Domain\DomainMessage;

final class EventElasticRepository extends ElasticRepository
{
    public function store(DomainMessage $message)
    {
        $document = [
            'type' => $message->getType(),
            'payload' => $message->getPayload()->serialize()
        ];

        $this->add($document);
    }

    public function __construct(array $elasticConfig)
    {
        parent::__construct($elasticConfig, 'events');
    }
}
