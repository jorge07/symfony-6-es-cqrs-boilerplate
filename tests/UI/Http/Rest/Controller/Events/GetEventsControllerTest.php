<?php

declare(strict_types=1);

namespace App\Tests\UI\Http\Rest\Controller\Events;

use App\Infrastructure\Share\Event\Consumer\SendEventsToElasticConsumer;
use App\Infrastructure\Share\Event\Query\EventElasticRepository;
use App\Tests\Infrastructure\Share\Event\Publisher\InMemoryProducer;
use App\Tests\UI\Http\Rest\Controller\JsonApiTestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;

class GetEventsControllerTest extends JsonApiTestCase
{
    /**
     * @test
     *
     * @group e2e
     */
    public function events_list_must_return_404_when_no_page_found()
    {
        $this->get('/api/events?page=100');

        self::assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @test
     *
     * @group e2e
     */
    public function events_should_be_present_in_elastic_search()
    {
        $uuid = Uuid::uuid4()->toString();

        $this->post('/api/users', [
            'uuid'     => $uuid,
            'email'    => 'jo@jo.com',
            'password' => 'password',
        ]);

        self::assertEquals(Response::HTTP_CREATED, $this->client->getResponse()->getStatusCode());

        $this->refreshIndex();

        $this->get('/api/events', ['limit' => 1]);

        self::assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $responseDecoded = json_decode($this->client->getResponse()->getContent(), true);

        self::assertEquals(1, $responseDecoded['meta']['total']);
        self::assertEquals(1, $responseDecoded['meta']['page']);
        self::assertEquals(1, $responseDecoded['meta']['size']);

        self::assertEquals('App.Domain.User.Event.UserWasCreated', $responseDecoded['data'][0]['type']);
        self::assertEquals($uuid, $responseDecoded['data'][0]['payload']['uuid']);
    }

    /**
     * @test
     *
     * @group e2e
     */
    public function events_not_present_in_elastic_search_in_other_page()
    {
        $uuid = Uuid::uuid4()->toString();

        $this->post('/api/users', [
            'uuid'     => $uuid,
            'email'    => 'jo@jo.com',
            'password' => 'password',
        ]);

        self::assertEquals(Response::HTTP_CREATED, $this->client->getResponse()->getStatusCode());

        $this->refreshIndex();

        $this->get('/api/events', ['page' => 2]);

        self::assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $responseDecoded = json_decode($this->client->getResponse()->getContent(), true);

        self::assertEquals(1, $responseDecoded['meta']['total']);
        self::assertEquals(2, $responseDecoded['meta']['page']);
        self::assertEquals(50, $responseDecoded['meta']['size']);

        self::assertFalse(isset($responseDecoded['data']));
    }

    /**
     * @test
     *
     * @group e2e
     */
    public function given_invalid_page_returns_400_status()
    {
        $this->get('/api/events', ['page' => 'two']);

        self::assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());

        $this->get('/api/events?page=two');

        self::assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @test
     *
     * @group e2e
     */
    public function given_invalid_limit_returns_400_status()
    {
        $this->get('/api/events', ['limit' => 'three']);

        self::assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());

        $this->get('/api/events?limit=three');

        self::assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
    }

    private function refreshIndex()
    {
        /** @var EventElasticRepository $eventReadStore */
        $eventReadStore = $this->client->getContainer()->get('events_repository');
        $eventReadStore->refresh();
    }

    protected function setUp()
    {
        parent::setUp();

        /** @var EventElasticRepository $eventReadStore */
        $eventReadStore = $this->client->getContainer()->get('events_repository');
        $eventReadStore->boot();

        /** @var InMemoryProducer $consumersRegistry */
        $consumersRegistry = $this->client->getContainer()->get(InMemoryProducer::class);
        /** @var SendEventsToElasticConsumer $consumer */
        $consumer = $this->client->getContainer()->get('events_to_elastic');
        $consumersRegistry->addConsumer('App.Domain.User.Event.UserWasCreated', $consumer);

        $this->refreshIndex();
    }

    protected function tearDown()
    {
        /** @var EventElasticRepository $eventReadStore */
        $eventReadStore = $this->client->getContainer()->get('events_repository');
        $eventReadStore->delete();

        parent::tearDown();
    }
}
