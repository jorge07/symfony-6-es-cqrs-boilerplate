<?php

declare(strict_types=1);

namespace App\Shared\Application\Query;

use Broadway\ReadModel\SerializableReadModel;

final class Item
{
    private function __construct(
        public readonly string $id,
        public readonly string $type,
        public readonly array $resource,
        public readonly array $relationships = []
    )
    {
    }

    private static function type(SerializableReadModel $model): string
    {
        $path = \explode('\\', $model::class);

        return \array_pop($path);
    }

    public static function fromSerializable(SerializableReadModel $serializableReadModel, array $relations = []): self
    {
        return new self(
            $serializableReadModel->getId(),
            self::type($serializableReadModel),
            $serializableReadModel->serialize(),
            $relations
        );
    }

    public static function fromPayload(string $id, string $type, array $payload, array $relations = []): self
    {
        return new self(
            $id,
            $type,
            $payload,
            $relations
        );
    }
}
