<?php

declare(strict_types=1);

namespace App\Tests\UI\Http\Rest\Controller\Events;

use App\Domain\User\Event\UserWasCreated;
use App\Infrastructure\Share\Event\Consumer\SendEventsToElasticConsumer;
use App\Infrastructure\Share\Event\Event;
use App\Infrastructure\Share\Event\Query\EventElasticRepository;
use App\Tests\UI\Http\Rest\Controller\JsonApiTestCase;
use Broadway\Domain\DateTime;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;

class GetEventsControllerTest extends JsonApiTestCase
{
    /**
     * @throws \Assert\AssertionFailedException
     */
    protected function setUp(): void
    {
        parent::setUp();

        /** @var EventElasticRepository $eventReadStore */
        $eventReadStore = $this->cli->getContainer()->get('events_repository');
        $eventReadStore->boot();

        /** @var SendEventsToElasticConsumer $consumer */
        $consumer = $this->cli->getContainer()->get('events_to_elastic');
        $data = [
            'uuid' => $uuid = Uuid::uuid4()->toString(),
            'credentials' => [
                'email' => self::DEFAULT_EMAIL,
                'password' => 'lkasjbdalsjdbalsdbaljsdhbalsjbhd987',
            ],
            'created_at' => '2020-02-20',
        ];
        $consumer(new Event(
            new DomainMessage(
                $uuid,
                1,
                new Metadata(),
                UserWasCreated::deserialize($data),
                DateTime::now()
            )
        ));

        $this->refreshIndex();

        $this->createUser();
        $this->auth();
    }

    /**
     * @test
     *
     * @group e2e
     */
    public function events_list_must_return_404_when_no_page_found(): void
    {
        $this->get('/api/events?page=100');

        self::assertSame(Response::HTTP_NOT_FOUND, $this->cli->getResponse()->getStatusCode());
    }

    /**
     * @test
     *
     * @group e2e
     *
     * @throws \Exception
     */
    public function events_should_be_present_in_elastic_search(): void
    {
        $this->refreshIndex();

        $this->get('/api/events', ['limit' => 1]);

        self::assertSame(Response::HTTP_OK, $this->cli->getResponse()->getStatusCode());

        /** @var string $content */
        $content = $this->cli->getResponse()->getContent();

        $responseDecoded = \json_decode($content, true);

        self::assertSame(1, $responseDecoded['meta']['total']);
        self::assertSame(1, $responseDecoded['meta']['page']);
        self::assertSame(1, $responseDecoded['meta']['size']);

        self::assertSame('App.Domain.User.Event.UserWasCreated', $responseDecoded['data'][0]['type']);
        self::assertSame(self::DEFAULT_EMAIL, $responseDecoded['data'][0]['payload']['credentials']['email']);
    }

    /**
     * @test
     *
     * @group e2e
     */
    public function given_invalid_page_returns_400_status(): void
    {
        $this->get('/api/events?page=two');

        self::assertSame(Response::HTTP_BAD_REQUEST, $this->cli->getResponse()->getStatusCode());
    }

    /**
     * @test
     *
     * @group e2e
     */
    public function given_invalid_limit_returns_400_status(): void
    {
        $this->get('/api/events?limit=three');

        self::assertSame(Response::HTTP_BAD_REQUEST, $this->cli->getResponse()->getStatusCode());
    }

    private function refreshIndex(): void
    {
        /** @var EventElasticRepository $eventReadStore */
        $eventReadStore = $this->cli->getContainer()->get('events_repository');
        $eventReadStore->refresh();
    }

    protected function tearDown(): void
    {
        /** @var EventElasticRepository $eventReadStore */
        $eventReadStore = $this->cli->getContainer()->get('events_repository');
        $eventReadStore->delete();

        parent::tearDown();
    }
}
