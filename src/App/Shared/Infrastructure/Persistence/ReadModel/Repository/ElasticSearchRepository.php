<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Persistence\ReadModel\Repository;

use Assert\Assertion;
use Assert\AssertionFailedException;
use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Psr\Log\LoggerInterface;

abstract class ElasticSearchRepository
{
    private Client $client;

    public function __construct(array $elasticConfig, LoggerInterface $elasticsearchLogger = null)
    {
        $defaultConfig = [];

        if ($elasticsearchLogger) {
            $defaultConfig['logger'] = $elasticsearchLogger;
            $defaultConfig['tracer'] = $elasticsearchLogger;
        }

        $this->client = ClientBuilder::fromConfig(\array_replace($defaultConfig, $elasticConfig), true);
    }

    abstract protected function index(): string;

    public function search(array $query): array
    {
        $finalQuery = [];

        $finalQuery['index'] = $this->index();
        $finalQuery['body'] = $query;

        return $this->client->search($finalQuery);
    }

    public function refresh(): void
    {
        if ($this->client->indices()->exists(['index' => $this->index()])) {
            $this->client->indices()->refresh(['index' => $this->index()]);
        }
    }

    public function delete(): void
    {
        if ($this->client->indices()->exists(['index' => $this->index()])) {
            $this->client->indices()->delete(['index' => $this->index()]);
        }
    }

    public function reboot(): void
    {
        $this->delete();
        $this->boot();
    }

    public function boot(): void
    {
        if (!$this->client->indices()->exists(['index' => $this->index()])) {
            $this->client->indices()->create(['index' => $this->index()]);
        }
    }

    protected function add(array $document): array
    {
        $query = [];

        $query['index'] = $this->index();
        $query['id'] = $document['id'] ?? null;
        $query['body'] = $document;

        return $this->client->index($query);
    }

    /**
     * @throws AssertionFailedException
     */
    public function page(int $page = 1, int $limit = 50): array
    {
        Assertion::greaterThan($page, 0, 'Pagination need to be > 0');

        $query = [];

        $query['index'] = $this->index();
        $query['from'] = ($page - 1) * $limit;
        $query['size'] = $limit;

        $response = $this->client->search($query);

        return [
            'data' => \array_column($response['hits']['hits'], '_source'),
            'total' => $response['hits']['total'],
        ];
    }

    public function isHealthly(): bool
    {
        try {
            $response = $this->client->cluster()->health();

            return $response['status'] !== 'red';
        } catch (\Throwable $err) {
            return false;
        }
    }
}
