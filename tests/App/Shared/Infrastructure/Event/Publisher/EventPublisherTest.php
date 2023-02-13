<?php

declare(strict_types=1);

namespace Tests\App\Shared\Infrastructure\Event\Publisher;

use App\Shared\Domain\Exception\DateTimeException;
use App\Shared\Domain\ValueObject\DateTime as DomainDateTime;
use App\User\Domain\Event\UserWasCreated;
use App\Shared\Infrastructure\Event\Publisher\AsyncEventPublisher;
use Assert\AssertionFailedException;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Tests\App\Shared\Application\ApplicationTestCase;
use Throwable;

class EventPublisherTest extends ApplicationTestCase
{
    private ?AsyncEventPublisher $publisher;

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
     * @throws AssertionFailedException
     * @throws DateTimeException
     * @throws Throwable
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

        $this->publisher->handle(DomainMessage::recordNow($uuid, 1, new Metadata(), UserWasCreated::deserialize($data)));

        $this->publisher->publish();

        $transportMessages = $this->transport->get();
        self::assertCount(1, $transportMessages);

        $event = $transportMessages[0]->getMessage()->getPayload();

        self::assertInstanceOf(UserWasCreated::class, $event);
        self::assertSame($data, $event->serialize(), 'Check that its the same event');
    }

    protected function tearDown(): void
    {
        $this->publisher = null;
        $this->transport = null;
    }
}
