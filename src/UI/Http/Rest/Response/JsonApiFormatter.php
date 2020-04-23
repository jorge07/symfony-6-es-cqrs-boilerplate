<?php

declare(strict_types=1);

namespace App\UI\Http\Rest\Response;

use App\Infrastructure\Share\Bus\Query\Collection;
use App\Infrastructure\Share\Bus\Query\Item;

final class JsonApiFormatter
{
    public static function one(Item $resource): array
    {
        return \array_filter([
            'data' => self::model($resource),
            'relationships' => self::relations($resource->getRelationships()),
        ]);
    }

    public static function collection(Collection $collection): array
    {
        $transformer = function ($data) {
            return $data instanceof Item ? self::model($data) : $data;
        };

        $resources = \array_map($transformer, $collection->getData());

        return \array_filter([
            'meta' => [
                'size' => $collection->getLimit(),
                'page' => $collection->getPage(),
                'total' => $collection->getTotal(),
            ],
            'data' => $resources,
        ]);
    }

    private static function model(Item $resource): array
    {
        return [
            'id' => $resource->getId(),
            'type' => $resource->getType(),
            'attributes' => $resource->getResource(),
        ];
    }

    private static function relations(array $relations): array
    {
        $result = [];

        /** @var Item $relation */
        foreach ($relations as $relation) {
            $result[$relation->getType()] = [
                'data' => self::model($relation),
            ];
        }

        return $result;
    }
}
