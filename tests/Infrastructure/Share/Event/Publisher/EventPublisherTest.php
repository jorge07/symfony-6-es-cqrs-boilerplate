<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Share\Event\Publisher;

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
     */
    public function messages_are_consumed_by_routing_key()
    {
        $data = ['uuid' => $uuid = Uuid::uuid4()->toString(), 'email' => 'lol@lol.com'];

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

        self::assertEquals($data, $event->serialize(), 'Check that its the same event');
    }

    private function createConsumer(): ConsumerInterface
    {
        return $this->consumer = new class implements ConsumerInterface {

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
        };
    }

    protected function setup()
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

    protected function tearDown()
    {
        $this->publisher = null;
        $this->consumer  = null;
    }

    /** @var ConsumerInterface|mixed */
    private $consumer;

    /** @var EventPublisher */
    private $publisher;
}
