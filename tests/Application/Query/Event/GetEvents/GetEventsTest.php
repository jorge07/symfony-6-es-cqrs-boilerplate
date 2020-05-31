<?php

declare(strict_types=1);

namespace App\Tests\Application\Query\Event\GetEvents;

use App\Application\Command\User\SignUp\SignUpCommand;
use App\Application\Query\Event\GetEvents\GetEventsQuery;
use App\Domain\Shared\ValueObject\DateTime as DomainDateTime;
use App\Domain\User\Event\UserWasCreated;
use App\Domain\User\ValueObject\Auth\Credentials;
use App\Domain\User\ValueObject\Auth\HashedPassword;
use App\Domain\User\ValueObject\Email;
use App\Infrastructure\Share\Bus\Event\Event;
use App\Infrastructure\Share\Bus\Query\Collection;
use App\Infrastructure\Share\Event\Consumer\SendEventsToElasticConsumer;
use App\Infrastructure\Share\Event\Query\EventElasticRepository;
use App\Tests\Application\ApplicationTestCase;
use Broadway\Domain\DateTime;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Ramsey\Uuid\Uuid;

final class GetEventsTest extends ApplicationTestCase
{
    /**
     * @throws \App\Domain\Shared\Exception\DateTimeException
     * @throws \Assert\AssertionFailedException
     * @throws \Throwable
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

        $uuid = Uuid::uuid4();

        /** @var SendEventsToElasticConsumer $consumer */
        $consumer = $this->service(SendEventsToElasticConsumer::class);
        $consumer(new Event(
            new DomainMessage(
                $uuid->toString(),
                1,
                new Metadata(),
                new UserWasCreated(
                    $uuid,
                    new Credentials(
                        Email::fromString('lol@lol.com'),
                        HashedPassword::fromHash('hashed_password')
                    ),
                    DomainDateTime::now()
                ),
                DateTime::now()
            )
        ));

        $this->fireTerminateEvent();

        /** @var EventElasticRepository $eventReadStore */
        $eventReadStore = $this->service(EventElasticRepository::class);
        $eventReadStore->refresh();
    }

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

    protected function tearDown(): void
    {
        /** @var EventElasticRepository $eventReadStore */
        $eventReadStore = $this->service(EventElasticRepository::class);
        $eventReadStore->delete();

        parent::tearDown();
    }
}
