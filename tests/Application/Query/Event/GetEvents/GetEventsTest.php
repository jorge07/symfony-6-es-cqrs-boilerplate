<?php

declare(strict_types=1);

namespace App\Tests\Application\Query\Event\GetEvents;

use App\Application\Command\User\SignUp\SignUpCommand;
use App\Application\Query\Collection;
use App\Application\Query\Event\GetEvents\GetEventsQuery;
use App\Infrastructure\Share\Event\Consumer\SendEventsToElasticConsumer;
use App\Infrastructure\Share\Event\Query\EventElasticRepository;
use App\Tests\Application\Command\ApplicationTestCase;
use App\Tests\Infrastructure\Share\Event\Publisher\InMemoryProducer;
use Ramsey\Uuid\Uuid;

final class GetEventsTest extends ApplicationTestCase
{
    /**
     * @test
     *
     * @group integration
     */
    public function processed_events_must_be_in_elastic_search(): void
    {
        $response = $this->ask(new GetEventsQuery());

        self::assertInstanceOf(Collection::class, $response);
        self::assertSame(1, $response->total);
        self::assertSame('App.Domain.User.Event.UserWasCreated', $response->data[0]['type']);
    }

    /**
     * @throws \Exception
     * @throws \Assert\AssertionFailedException
     */
    protected function setUp()
    {
        parent::setUp();

        /** @var EventElasticRepository $eventReadStore */
        $eventReadStore = $this->service('events_repository');
        $eventReadStore->delete();

        /** @var InMemoryProducer $consumersRegistry */
        $consumersRegistry = $this->service(InMemoryProducer::class);
        /** @var SendEventsToElasticConsumer $consumer */
        $consumer = $this->service('events_to_elastic');
        $consumersRegistry->addConsumer('App.Domain.User.Event.UserWasCreated', $consumer);

        $command = new SignUpCommand(
            Uuid::uuid4()->toString(),
            'asd@asd.asd',
            'qwerqwer'
        );

        $this->handle($command);

        $this->fireTerminateEvent();

        /** @var EventElasticRepository $eventReadStore */
        $eventReadStore = $this->service('events_repository');
        $eventReadStore->refresh();
    }

    protected function tearDown()
    {
        /** @var EventElasticRepository $eventReadStore */
        $eventReadStore = $this->service('events_repository');
        $eventReadStore->delete();

        parent::tearDown();
    }
}
