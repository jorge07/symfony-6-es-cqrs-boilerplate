<?php

declare(strict_types=1);

namespace App\Infrastructure\Share\Event\Query;

use App\Domain\Shared\Event\EventRepositoryInterface;
use App\Infrastructure\Share\Persistence\Query\Repository\ElasticSearchRepository;
use Broadway\Domain\DomainMessage;

final class ElasticSearchEventRepository extends ElasticSearchRepository implements EventRepositoryInterface
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
