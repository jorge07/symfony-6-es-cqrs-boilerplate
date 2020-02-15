<?php

declare(strict_types=1);

namespace App\Application\Query;

use Messenger\Projection\ReadModelInterface;

final class Item
{
    /** @var string */
    public $id;

    /** @var string */
    public $type;

    /** @var array */
    public $resource;

    /** @var array */
    public $relationships = [];

    /** @var ReadModelInterface */
    public $readModel;

    public function __construct(ReadModelInterface $serializableReadModel, array $relations = [])
    {
        $this->id = $serializableReadModel->getId();
        $this->type = $this->type($serializableReadModel);
        $this->resource = $serializableReadModel->serialize();
        $this->relationships = $relations;
        $this->readModel = $serializableReadModel;
    }

    private function type(ReadModelInterface $model): string
    {
        $path = \explode('\\', \get_class($model));

        return \array_pop($path);
    }
}
