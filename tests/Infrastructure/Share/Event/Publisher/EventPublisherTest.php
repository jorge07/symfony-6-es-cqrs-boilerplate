<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Share\Event\Publisher;

use App\Domain\Shared\ValueObject\DateTime as DomainDateTime;
use App\Domain\User\Event\UserWasCreated;
use App\Infrastructure\Share\Event\Publisher\AsyncEventPublisher;
use App\Infrastructure\Share\Event\Publisher\EventPublisher;
use App\Tests\Application\ApplicationTestCase;
use Broadway\Domain\DateTime;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Messenger\Transport\TransportInterface;

class EventPublisherTest extends ApplicationTestCase
{
    private ?EventPublisher $publisher;

    private ?TransportInterface $transport;

    protected function setUp(): void
    {
        parent::setUp();

        $this->publisher = $this->service(AsyncEventPublisher::class);
        $this->transport = $this->service('messenger.transport.events');
    }

    /**
     * @test
     *
     * @group integration
     *
     * @throws \Exception
     * @throws \Assert\AssertionFailedException
     */
    public function events_are_consumed(): void
    {
        $current = DomainDateTime::now();

        $data = [
            'uuid' => $uuid = Uuid::uuid4()->toString(),
            'credentials' => [
                'email' => 'lol@lol.com',
                'password' => 'lkasjbdalsjdbalsdbaljsdhbalsjbhd987',
            ],
            'created_at' => $current->toString(),
        ];

        $this->publisher->handle(
            new DomainMessage(
                $uuid,
                1,
                new Metadata(),
                UserWasCreated::deserialize($data),
                DateTime::now()
            )
        );

        $this->publisher->publish();

        $transportMessages = $this->transport->get();
        self::assertCount(1, $transportMessages);

        $event = $transportMessages[0]->getMessage()->getDomainMessage()->getPayload();

        self::assertInstanceOf(UserWasCreated::class, $event);
        self::assertSame($data, $event->serialize(), 'Check that its the same event');
    }

    protected function tearDown(): void
    {
        $this->publisher = null;
        $this->transport = null;
    }
}
