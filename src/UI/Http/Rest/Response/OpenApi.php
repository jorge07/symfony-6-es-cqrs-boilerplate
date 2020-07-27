<?php

declare(strict_types=1);

namespace App\UI\Http\Rest\Response;

use App\Application\Query\Collection;
use App\Application\Query\Item;
use Symfony\Component\HttpFoundation\JsonResponse;
use function array_map;

class OpenApi extends JsonResponse
{
    private function __construct($data = null, int $status = self::HTTP_OK, array $headers = [], bool $json = false)
    {
        parent::__construct($data, $status, $headers, $json);
    }

    public static function fromPayload(array $payload, int $status): self
    {
        return new self($payload, $status);
    }

    public static function empty(int $status): self
    {
        return new self(null, $status);
    }

    public static function one(Item $resource, int $status = self::HTTP_OK): self
    {
        return new self (
            [
                'data' => self::model($resource),
                'relationships' => self::relations($resource->relationships),
            ],
            $status
        );
    }

    public static function created(string $location = null): self
    {
        return new self (
            null,
            self::HTTP_CREATED,
            ($location) ? [ 'location' => $location ] : []
        );
    }

    public static function collection(Collection $collection, int $status = self::HTTP_OK): OpenApi
    {
        /**
         * @param Item|array $data
         * @return array
         */
        $transformer = function ($data): array {
            return $data instanceof Item ? self::model($data) : $data;
        };

        $resources = array_map($transformer, $collection->data);

        return new OpenApi(
            [
                'meta' => [
                    'size' => $collection->limit,
                    'page' => $collection->page,
                    'total' => $collection->total,
                ],
                'data' => $resources
            ],
            $status
        );
    }

    private static function model(Item $resource): array
    {
        return [
            'id' => $resource->id,
            'type' => $resource->type,
            'attributes' => $resource->resource,
        ];
    }

    /**
     * @param Item[] $relations
     * @return array
     */
    private static function relations($relations): array
    {
        $result = [];

        foreach ($relations as $relation) {
            $result[$relation->type] = [
                'data' => self::model($relation),
            ];
        }

        return $result;
    }
}
