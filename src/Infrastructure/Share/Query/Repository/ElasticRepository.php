<?php

declare(strict_types=1);

namespace App\Infrastructure\Share\Query\Repository;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;

abstract class ElasticRepository
{
    public function search(array $query): array
    {
        $finalQuery = [];

        $finalQuery['index'] = $this->index;
        $finalQuery['type']  = $this->index; // To be deleted in elastic 7
        $finalQuery['body']  = $query;

        return $this->client->search($finalQuery);
    }

    public function refresh(): void
    {
        $this->client->indices()->refresh(['index' => $this->index]);
    }

    public function delete(): void
    {
        $this->client->indices()->delete(['index' => $this->index]);
    }

    protected function add(array $document): array
    {
        $query['index'] = $this->index;
        $query['type']  = $this->index;
        $query['id']    = $document['id'] ?? null;
        $query['body']  = $document;

        return $this->client->index($query);
    }


    public function __construct(array $config, string $index)
    {
        $this->client = ClientBuilder::fromConfig($config, true);
        $this->index = $index;
    }

    /**
     * @var string
     */
    private $index;

    /** @var Client  */
    private $client;
}
