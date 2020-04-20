<?php

declare(strict_types=1);

namespace App\Infrastructure\Share\Query\Repository;

use Assert\Assertion;
use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;

abstract class ElasticRepository
{
    private string $index;

    private Client $client;

    public function __construct(array $config, string $index)
    {
        $this->client = ClientBuilder::fromConfig($config, true);
        $this->index = $index;
    }

    public function search(array $query): array
    {
        $finalQuery = [];

        $finalQuery['index'] = $this->index;
        $finalQuery['body'] = $query;

        return $this->client->search($finalQuery);
    }

    public function refresh(): void
    {
        if ($this->client->indices()->exists(['index' => $this->index])) {
            $this->client->indices()->refresh(['index' => $this->index]);
        }
    }

    public function delete(): void
    {
        if ($this->client->indices()->exists(['index' => $this->index])) {
            $this->client->indices()->delete(['index' => $this->index]);
        }
    }

    public function reboot(): void
    {
        $this->delete();
        $this->boot();
    }

    public function boot(): void
    {
        if (!$this->client->indices()->exists(['index' => $this->index])) {
            $this->client->indices()->create(['index' => $this->index]);
        }
    }

    protected function add(array $document): array
    {
        $query['index'] = $this->index;
        $query['id'] = $document['id'] ?? null;
        $query['body'] = $document;

        return $this->client->index($query);
    }

    public function page(int $page = 1, int $limit = 50): array
    {
        Assertion::greaterThan($page, 0, 'Pagination need to be > 0');

        $query = [];

        $query['index'] = $this->index;
        $query['from'] = ($page - 1) * $limit;
        $query['size'] = $limit;

        $response = $this->client->search($query);

        return [
            'data' => \array_column($response['hits']['hits'], '_source'),
            'total' => $response['hits']['total'],
        ];
    }
}
