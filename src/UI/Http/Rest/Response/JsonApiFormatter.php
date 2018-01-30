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

    /**
     * @param SerializableReadModel[] $serializables
     *
     * @return array
     */
    public static function collection(array $serializables): array
    {
        $resources = [];

        foreach ($serializables as $serializable) {
            $resources[] = self::model($serializable);
        }

        return [
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

    private static function type(SerializableReadModel $model):string
    {
        $path = explode('\\', get_class($model));
        
        return array_pop($path);
    }
}
