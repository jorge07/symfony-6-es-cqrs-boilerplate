<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Share\Event\Publisher;

use App\Domain\Shared\ValueObject\DateTime as DomainDateTime;
use App\Domain\User\Event\UserWasCreated;
use App\Domain\User\ValueObject\Auth\Credentials;
use App\Domain\User\ValueObject\Auth\HashedPassword;
use App\Domain\User\ValueObject\Email;
use App\Infrastructure\Share\Event\Publisher\AsyncEventPublisher;
use App\Infrastructure\Share\Event\Publisher\EventPublisher;
use App\Tests\Application\ApplicationTestCase;
use Broadway\Domain\DateTime;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class EventPublisherTest extends ApplicationTestCase
{
    private ?EventPublisher $publisher;

    private ?TransportInterface $transport;

    private ?NormalizerInterface $normalizer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->publisher = $this->service(AsyncEventPublisher::class);
        $this->transport = $this->service('messenger.transport.events');
        $this->normalizer = $this->service(NormalizerInterface::class);
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
        $uuid = Uuid::uuid4();

        $data = [
            'uuid' => $uuid->toString(),
            'credentials' => [
                'email' => 'lol@lol.com',
                'password' => 'hashed_password',
            ],
            'created_at' => $current->toString(),
        ];

        $this->publisher->handle(
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
                    $current
                ),
                DateTime::now()
            )
        );

        $this->publisher->publish();

        $transportMessages = $this->transport->get();
        self::assertCount(1, $transportMessages);

        $event = $transportMessages[0]->getMessage()->getDomainMessage()->getPayload();

        self::assertInstanceOf(UserWasCreated::class, $event);
        self::assertSame($data, $this->normalizer->normalize($event), 'Check that its the same event');
    }

    protected function tearDown(): void
    {
        $this->publisher = null;
        $this->transport = null;
        $this->normalizer = null;
    }
}
