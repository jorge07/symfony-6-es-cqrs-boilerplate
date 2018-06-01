<?php

declare(strict_types=1);

namespace App\Tests\UI\Http\Rest\Controller;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class JsonApiTestCase extends WebTestCase
{
    protected function post(string $uri, array $params)
    {
        $this->client->request(
            'POST',
            $uri,
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            json_encode($params)
        );
    }

    protected function get(string $uri)
    {
        $this->client->request(
            'GET',
            $uri,
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ]
        );
    }

    protected function setUp()
    {
        $this->client = static::createClient();
    }

    protected function tearDown()
    {
        $this->client = null;
    }

    /** @var Client|null */
    protected $client;
}
