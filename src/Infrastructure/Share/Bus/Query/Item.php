<?php

declare(strict_types=1);

namespace App\Infrastructure\Share\Bus\Query;

use Broadway\ReadModel\SerializableReadModel;

final class Item
{
    /** @psalm-readonly */
    public string $id;

    /** @psalm-readonly */
    public string $type;

    /** @psalm-readonly */
    public array $resource;

    /** @psalm-readonly */
    public array $relationships = [];

    /** @psalm-readonly */
    public SerializableReadModel $readModel;

    public function __construct(SerializableReadModel $serializableReadModel, array $relations = [])
    {
        $this->id = $serializableReadModel->getId();
        $this->type = $this->type($serializableReadModel);
        $this->resource = $serializableReadModel->serialize();
        $this->relationships = $relations;
        $this->readModel = $serializableReadModel;
    }

    private function type(SerializableReadModel $model): string
    {
        $path = \explode('\\', \get_class($model));

        return (string) \array_pop($path);
    }
}
