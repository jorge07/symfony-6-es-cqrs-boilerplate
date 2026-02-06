<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller;

use App\Shared\Application\Query\Collection;
use App\Shared\Application\Query\Item;
use App\Shared\Application\Query\QueryBusInterface;
use App\Shared\Application\Query\QueryInterface;
use UI\Http\Rest\Response\OpenApi;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Throwable;

abstract class QueryController
{
    private const CACHE_MAX_AGE = 31_536_000;

    public function __construct(
        // Year.
        private readonly QueryBusInterface $queryBus,
        private readonly UrlGeneratorInterface $router
    )
    {
    }

    /**
     * @throws Throwable
     */
    protected function ask(QueryInterface $query): mixed
    {
        return $this->queryBus->ask($query);
    }

    protected function jsonCollection(Collection $collection, int $status = OpenApi::HTTP_OK, bool $isImmutable = false): OpenApi
    {
        $response = OpenApi::collection($collection, $status);

        $this->decorateWithCache($response, $collection, $isImmutable);

        return $response;
    }

    protected function json(Item $resource, int $status = OpenApi::HTTP_OK): OpenApi
    {
        return OpenApi::one($resource, $status);
    }

    protected function route(string $name, array $params = []): string
    {
        return $this->router->generate($name, $params);
    }

    private function decorateWithCache(OpenApi $response, Collection $collection, bool $isImmutable): void
    {
        if ($isImmutable && $collection->limit === \count($collection->data)) {
            $response
                ->setMaxAge(self::CACHE_MAX_AGE)
                ->setSharedMaxAge(self::CACHE_MAX_AGE);
        }
    }
}
