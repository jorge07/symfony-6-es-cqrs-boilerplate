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
        $this->post('/api/users', [
            'uuid'     => $uuid = Uuid::uuid4()->toString(),
            'email'    => 'jo@jo.com',
            'password' => 'password',
        ]);

        self::assertEquals(201, $this->client->getResponse()->getStatusCode());

        $this->refreshIndex();

        $this->get('/api/events');

        self::assertEquals(200, $this->client->getResponse()->getStatusCode());

        self::assertContains('UserWasCreated', $this->client->getResponse()->getContent());
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
