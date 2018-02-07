<?php

declare(strict_types=1);

namespace App\UI\Http\Rest\Response;

use Broadway\ReadModel\SerializableReadModel;

final class JsonApiFormatter
{
    public static function one(SerializableReadModel $serializable): array
    {
        return [
            'data' => self::model($serializable)
        ];
    }

    public static function collection(Collection $collection): array
    {
        $transformer = function ($data) {
            return $data instanceof SerializableReadModel ? self::model($data) : $data;
        };

        $resources = array_map($transformer, $collection->data());

        return [
            'meta' => [
                'size' => $collection->limit(),
                'page' => $collection->page(),
                'total' => $collection->total(),
            ],
            'data' => $resources
        ];
    }

    private static function model(SerializableReadModel $serializable): array
    {
        return [
            'id' => $serializable->getId(),
            'type' => self::type($serializable),
            'attributes' => $serializable->serialize()
        ];
    }

    private static function type(SerializableReadModel $model): string
    {
        $path = explode('\\', get_class($model));
        
        return array_pop($path);
    }
}
