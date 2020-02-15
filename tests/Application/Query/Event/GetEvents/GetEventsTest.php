<?php

declare(strict_types=1);

namespace App\Tests\Application\Query\Event\GetEvents;

use App\Application\Command\User\SignUp\SignUpCommand;
use App\Application\Query\Collection;
use App\Application\Query\Event\GetEvents\GetEventsQuery;
use App\Domain\User\Event\UserWasCreated;
use App\Infrastructure\Share\Event\Query\EventElasticRepository;
use App\Tests\Application\ApplicationTestCase;
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
        self::assertSame(UserWasCreated::class, $response->data[0]['type']);
    }

    /**
     * @throws \Exception
     * @throws \Assert\AssertionFailedException
     */
    protected function setUp(): void
    {
        parent::setUp();

        /** @var EventElasticRepository $eventReadStore */
        $eventReadStore = $this->service(EventElasticRepository::class);
        $eventReadStore->reboot();

        $command = new SignUpCommand(
            Uuid::uuid4()->toString(),
            'asd@asd.asd',
            'qwerqwer'
        );

        $this->handle($command);

        $this->consumeMessages();

        $this->fireTerminateEvent();

        /** @var EventElasticRepository $eventReadStore */
        $eventReadStore = $this->service(EventElasticRepository::class);
        $eventReadStore->refresh();
    }

    protected function tearDown(): void
    {
        /** @var EventElasticRepository $eventReadStore */
        $eventReadStore = $this->service(EventElasticRepository::class);
        $eventReadStore->delete();

        parent::tearDown();
    }
}
