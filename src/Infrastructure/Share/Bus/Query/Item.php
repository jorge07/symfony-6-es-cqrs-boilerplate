<?php

declare(strict_types=1);

namespace App\Infrastructure\Share\Bus\Query;

use Broadway\ReadModel\SerializableReadModel;

final class Item
{
    private string $id;

    private string $type;

    private array $resource;

    private array $relationships = [];

    private SerializableReadModel $readModel;

    public function __construct(SerializableReadModel $serializableReadModel, array $relations = [])
    {
        $this->id = $serializableReadModel->getId();
        $this->type = $this->type($serializableReadModel);
        $this->resource = $serializableReadModel->serialize();
        $this->relationships = $relations;
        $this->readModel = $serializableReadModel;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getResource(): array
    {
        return $this->resource;
    }

    public function getRelationships(): array
    {
        return $this->relationships;
    }

    public function getReadModel(): SerializableReadModel
    {
        return $this->readModel;
    }

    private function type(SerializableReadModel $model): string
    {
        $path = \explode('\\', \get_class($model));

        return \array_pop($path);
    }
}
