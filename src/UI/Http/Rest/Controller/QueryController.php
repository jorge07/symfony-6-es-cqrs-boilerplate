<?php

declare(strict_types=1);

namespace App\UI\Http\Rest\Controller;

use App\Application\Query\Collection;
use App\Application\Query\Item;
use App\Infrastructure\Share\Bus\QueryBus;
use App\Infrastructure\Share\Bus\QueryInterface;
use App\UI\Http\Rest\Response\JsonApiFormatter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class QueryController
{
    private const CACHE_MAX_AGE = 31536000; // Year.

    /**
     * @return mixed
     *
     * @throws \Throwable
     */
    protected function ask(QueryInterface $query)
    {
        return $this->queryBus->handle($query);
    }

    protected function jsonCollection(Collection $collection, bool $isImmutable = false): JsonResponse
    {
        $response = JsonResponse::create($this->formatter::collection($collection));

        $this->decorateWithCache($response, $collection, $isImmutable);

        return $response;
    }

    protected function json(Item $resource): JsonResponse
    {
        return JsonResponse::create($this->formatter->one($resource));
    }

    protected function route(string $name, array $params = []): string
    {
        return $this->router->generate($name, $params);
    }

    private function decorateWithCache(JsonResponse $response, Collection $collection, bool $isImmutable): void
    {
        if ($isImmutable && $collection->limit === \count($collection->data)) {
            $response
                ->setMaxAge(self::CACHE_MAX_AGE)
                ->setSharedMaxAge(self::CACHE_MAX_AGE);
        }
    }

    public function __construct(QueryBus $queryBus, JsonApiFormatter $formatter, UrlGeneratorInterface $router)
    {
        $this->queryBus = $queryBus;
        $this->formatter = $formatter;
        $this->router = $router;
    }

    /** @var JsonApiFormatter */
    private $formatter;

    /** @var QueryBus */
    private $queryBus;

    /** @var UrlGeneratorInterface */
    private $router;
}
