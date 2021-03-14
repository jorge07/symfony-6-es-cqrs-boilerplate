<?php

declare(strict_types=1);

namespace Tests\UI\Http\Rest\Controller\Events;

use App\Shared\Infrastructure\Event\ReadModel\ElasticSearchEventRepository;
use Tests\UI\Http\Rest\Controller\JsonApiTestCase;
use Assert\AssertionFailedException;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\EventListener\StopWorkerOnMessageLimitListener;
use Symfony\Component\Messenger\Worker;
use Throwable;

class GetEventsControllerTest extends JsonApiTestCase
{
    private ?Worker $worker;

    /**
     * @throws AssertionFailedException
     * @throws Throwable
     */
    protected function setUp(): void
    {
        parent::setUp();

        /** @var ElasticSearchEventRepository $eventReadStore */
        $eventReadStore = self::$container->get(ElasticSearchEventRepository::class);

        $eventReadStore->reboot();

        $this->createUser();
        $this->auth();
        $this->fireTerminateEvent();
        $eventDispatcher = self::$container->get('event_dispatcher');
        $eventDispatcher->addSubscriber(new StopWorkerOnMessageLimitListener(2));
        $this->worker = new Worker(
            [
                'events' => self::$container->get('messenger.transport.events'),
                'users' => self::$container->get('messenger.transport.users'),
            ],
            self::$container->get('messenger.bus.event.async'),
            $eventDispatcher,
            self::$container->get('logger')
        );

        $this->worker->run();
        $this->refreshIndex();
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
     * @throws Exception
     */
    public function user_was_created_and_sign_in_events_should_be_present_in_elastic_search(): void
    {
        $this->get('/api/events', ['limit' => 2]);
        self::assertSame(Response::HTTP_OK, $this->cli->getResponse()->getStatusCode());

        /** @var string $content */
        $content = $this->cli->getResponse()->getContent();

        $responseDecoded = \json_decode($content, true);

        self::assertSame(2, $responseDecoded['meta']['total']);
        self::assertSame(1, $responseDecoded['meta']['page']);
        self::assertSame(2, $responseDecoded['meta']['size']);

        self::assertSame('App.User.Domain.Event.UserWasCreated', $responseDecoded['data'][0]['type']);
        self::assertSame(self::DEFAULT_EMAIL, $responseDecoded['data'][0]['payload']['credentials']['email']);
        self::assertSame('App.User.Domain.Event.UserSignedIn', $responseDecoded['data'][1]['type']);
        self::assertSame(self::DEFAULT_EMAIL, $responseDecoded['data'][1]['payload']['email']);
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
        /** @var ElasticSearchEventRepository $eventReadStore */
        $eventReadStore = self::$container->get(ElasticSearchEventRepository::class);
        $eventReadStore->refresh();
    }

    protected function tearDown(): void
    {
        /** @var ElasticSearchEventRepository $eventReadStore */
        $eventReadStore = self::$container->get(ElasticSearchEventRepository::class);
        $eventReadStore->delete();
        if ($this->worker) {
            $this->worker->stop();
        }
        parent::tearDown();
    }
}
