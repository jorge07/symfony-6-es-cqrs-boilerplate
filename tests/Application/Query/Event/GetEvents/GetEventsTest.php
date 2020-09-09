<?php

declare(strict_types=1);

namespace App\Tests\Application\Query\Event\GetEvents;

use App\Application\Command\User\SignUp\SignUpCommand;
use App\Application\Query\Collection;
use App\Application\Query\Event\GetEvents\GetEventsQuery;
use App\Domain\Shared\Exception\DateTimeException;
use App\Domain\User\Event\UserWasCreated;
use App\Infrastructure\Shared\Event\Consumer\SendEventsToElasticConsumer;
use App\Infrastructure\Shared\Event\ReadModel\ElasticSearchEventRepository;
use App\Tests\Application\ApplicationTestCase;
use Assert\AssertionFailedException;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Ramsey\Uuid\Uuid;
use Throwable;

final class GetEventsTest extends ApplicationTestCase
{
    /**
     * @throws DateTimeException
     * @throws AssertionFailedException
     * @throws Throwable
     */
    protected function setUp(): void
    {
        parent::setUp();

        /** @var ElasticSearchEventRepository $eventReadStore */
        $eventReadStore = $this->service(ElasticSearchEventRepository::class);
        $eventReadStore->reboot();

        $command = new SignUpCommand(
            Uuid::uuid4()->toString(),
            'asd@asd.asd',
            'qwerqwer'
        );

        $this->handle($command);

        /** @var SendEventsToElasticConsumer $consumer */
        $consumer = $this->service(SendEventsToElasticConsumer::class);
        $data = [
            'uuid' => $uuid = Uuid::uuid4()->toString(),
            'credentials' => [
                'email' => 'asd@asd.asd',
                'password' => '$2y$12$90mmbScglod8M3adPNvXsOIyiFC.AqOpgTktQTnnu1.Pvn5inVcUm',
            ],
            'created_at' => '2020-02-20',
        ];

        $consumer(DomainMessage::recordNow($uuid, 1, new Metadata(), UserWasCreated::deserialize($data)));

        $this->fireTerminateEvent();

        /** @var ElasticSearchEventRepository $eventReadStore */
        $eventReadStore = $this->service(ElasticSearchEventRepository::class);
        $eventReadStore->refresh();
    }

    /**
     * @test
     *
     * @group integration
     *
     * @throws Throwable
     */
    public function processed_events_must_be_in_elastic_search(): void
    {
        $response = $this->ask(new GetEventsQuery());

        self::assertInstanceOf(Collection::class, $response);
        self::assertSame(1, $response->total);
        self::assertSame('App.Domain.User.Event.UserWasCreated', $response->data[0]['type']);
        self::assertSame('asd@asd.asd', $response->data[0]['payload']['credentials']['email']);
    }

    protected function tearDown(): void
    {
        /** @var ElasticSearchEventRepository $eventReadStore */
        $eventReadStore = $this->service(ElasticSearchEventRepository::class);
        $eventReadStore->delete();

        parent::tearDown();
    }
}
