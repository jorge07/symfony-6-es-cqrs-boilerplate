<?php

declare(strict_types=1);

namespace App\Infrastructure\Share\Event\Query;

use App\Domain\Shared\Event\EventRepositoryInterface;
use App\Infrastructure\Share\Query\Repository\ElasticRepository;
use Broadway\Domain\DomainMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class EventElasticRepository extends ElasticRepository implements EventRepositoryInterface
{
    private NormalizerInterface $normalizer;

    public function __construct(
        array $elasticConfig,
        NormalizerInterface $normalizer,
        LoggerInterface $elasticsearchLogger = null
    ) {
        parent::__construct($elasticConfig, $elasticsearchLogger);

        $this->normalizer = $normalizer;
    }

    protected function index(): string
    {
        return 'events';
    }

    public function store(DomainMessage $message): void
    {
        $document = [
            'type' => $message->getType(),
            'payload' => $this->normalizer->normalize($message->getPayload()),
            'occurred_on' => $message->getRecordedOn()->toString(),
        ];

        $this->add($document);
    }
}
