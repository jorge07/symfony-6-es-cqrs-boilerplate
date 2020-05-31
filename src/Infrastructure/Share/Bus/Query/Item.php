<?php

declare(strict_types=1);

namespace App\Infrastructure\Share\Bus\Query;

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
    public object $readModel;

    public function __construct(
        string $id,
        string $type,
        array $resource,
        array $relationships,
        object $readModel
    ) {
        $this->id = $id;
        $this->type = $type;
        $this->resource = $resource;
        $this->relationships = $relationships;
        $this->readModel = $readModel;
    }
}
