<?php

declare(strict_types=1);

namespace App\UI\Http\Rest\Controller;

use App\UI\Http\Rest\Response\JsonApiFormatter;
use Broadway\ReadModel\SerializableReadModel;
use League\Tactician\CommandBus;
use Symfony\Component\HttpFoundation\JsonResponse;

abstract class QueryController
{
    protected function json(SerializableReadModel $serializableReadModel): JsonResponse
    {
        return JsonResponse::create($this->formatter::one($serializableReadModel));
    }

    protected function ask($query)
    {
        return $this->queryBus->handle($query);
    }

    public function __construct(CommandBus $queryBus, JsonApiFormatter $formatter)
    {
        $this->queryBus = $queryBus;
        $this->formatter = $formatter;
    }

    /**
     * @var JsonApiFormatter
     */
    private $formatter;

    /**
     * @var CommandBus
     */
    private $queryBus;
}
