<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Share\Event\Publisher;

use App\Domain\Shared\ValueObject\DateTime as DomainDateTime;
use App\Domain\User\Event\UserWasCreated;
use App\Infrastructure\Share\Event\Publisher\AsyncEventPublisher;
use App\Infrastructure\Share\Event\Publisher\EventPublisher;
use Broadway\Domain\DateTime;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class EventPublisherTest extends TestCase
{
    /**
     * @test
     *
     * @group unit
     *
     * @throws \Exception
     * @throws \Assert\AssertionFailedException
     */
    public function messages_are_consumed_by_routing_key(): void
    {
        $current = DomainDateTime::now();

        $data = [
            'uuid'        => $uuid = Uuid::uuid4()->toString(),
            'credentials' => [
                'email'    => 'lol@lol.com',
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

        /** @var UserWasCreated $event */
        $event = $this->consumer->getMessage()->getPayload();

        self::assertInstanceOf(UserWasCreated::class, $event);

        self::assertSame($data, $event->serialize(), 'Check that its the same event');
    }

    private function createConsumer(): Consumer
    {
        return $this->consumer = new Consumer();
    }

    protected function setup(): void
    {
        $producer = new InMemoryProducer();

        $this->publisher = new AsyncEventPublisher(
            $producer
                ->addConsumer(
                    'App.Domain.User.Event.UserWasCreated',
                    $this->createConsumer()
                )
        );
    }

    protected function tearDown(): void
    {
        $this->publisher = null;
        $this->consumer = null;
    }

    /** @var Consumer|null */
    private $consumer;

    /** @var EventPublisher|null */
    private $publisher;
}

class Consumer implements ConsumerInterface
{
    /** @var DomainMessage|null */
    private $message;

    public function getMessage(): ?DomainMessage
    {
        return $this->message;
    }

    public function execute(AMQPMessage $msg)
    {
        $this->message = unserialize($msg->body);
    }
}
