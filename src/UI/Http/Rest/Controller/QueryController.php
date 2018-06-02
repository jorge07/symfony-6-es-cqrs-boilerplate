<?php

declare(strict_types=1);

namespace App\UI\Http\Rest\Controller;

use App\Application\Query\Collection;
use App\Application\Query\Item;
use App\UI\Http\Rest\Response\JsonApiFormatter;
use League\Tactician\CommandBus;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class QueryController
{
    protected function jsonCollection(Collection $collection, bool $isImmutable = false): JsonResponse
    {
        $response = JsonResponse::create($this->formatter->collection($collection));

        $this->decoratwWithCache($response, $collection, $isImmutable);

        return $response;
    }

    private function decoratwWithCache(JsonResponse $response, Collection $collection, bool $isImmutable): void
    {
        if ($isImmutable && $collection->limit === count($collection->data)) {
            $aYear = 60 * 60 * 24 * 365;
            $response
                ->setMaxAge($aYear)
                ->setSharedMaxAge($aYear);
        }
    }

    protected function json(Item $resource): JsonResponse
    {
        return JsonResponse::create($this->formatter->one($resource));
    }

    protected function route(string $name, array $params = []): string
    {
        return $this->router->generate($name, $params);
    }

    protected function ask($query)
    {
        return $this->queryBus->handle($query);
    }

    public function __construct(CommandBus $queryBus, JsonApiFormatter $formatter, UrlGeneratorInterface $router)
    {
        $this->queryBus = $queryBus;
        $this->formatter = $formatter;
        $this->router = $router;
    }

    /**
     * @var JsonApiFormatter
     */
    private $formatter;

    /**
     * @var CommandBus
     */
    private $queryBus;

    /**
     * @var UrlGeneratorInterface
     */
    private $router;
}
