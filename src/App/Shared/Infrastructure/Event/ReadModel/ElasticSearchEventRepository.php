<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Event\ReadModel;

use App\Shared\Infrastructure\Persistence\ReadModel\Repository\ElasticSearchRepository;
use Broadway\Domain\DomainMessage;

final class ElasticSearchEventRepository extends ElasticSearchRepository
{
    private const INDEX = 'events';

    protected function index(): string
    {
        return self::INDEX;
    }

    public function store(DomainMessage $message): void
    {
        $document = [
            'type' => $message->getType(),
            'payload' => $message->getPayload()->serialize(),
            'occurred_on' => $message->getRecordedOn()->toString(),
        ];

        $this->add($document);
    }
}
