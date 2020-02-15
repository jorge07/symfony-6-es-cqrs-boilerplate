<?php

declare(strict_types=1);

namespace App\Infrastructure\Share\Query\Repository;

use Assert\Assertion;
use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;

abstract class ElasticRepository
{
    public function search(array $query): array
    {
        $finalQuery = [];

        $finalQuery['index'] = $finalQuery['type'] = $this->index; // To be deleted in elastic 7
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
        $query['index'] = $query['type'] = $this->index;
        $query['id'] = $document['id'] ?? null;
        $query['body'] = $document;

        return $this->client->index($query);
    }

    public function page(int $page = 1, int $limit = 50): array
    {
        Assertion::greaterThan($page, 0, 'Pagination need to be > 0');

        $query = [];

        $query['index'] = $query['type'] = $this->index;
        $query['from'] = ($page - 1) * $limit;
        $query['size'] = $limit;

        $response = $this->client->search($query);

        return [
            'data' => \array_column($response['hits']['hits'], '_source'),
            'total' => $response['hits']['total'],
        ];
    }

    public function __construct(array $config, string $index)
    {
        $this->client = ClientBuilder::fromConfig($config, true);
        $this->index = $index;
    }

    /** @var string */
    private $index;

    /** @var Client */
    private $client;
}
