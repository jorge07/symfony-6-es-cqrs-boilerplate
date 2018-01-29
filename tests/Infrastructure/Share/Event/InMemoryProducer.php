<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Share\Event;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class InMemoryProducer implements ProducerInterface
{
    public function publish($msgBody, $routingKey = '', $additionalProperties = array())
    {
        $amqMessage = $this->amqMessageAdapter($msgBody, $additionalProperties);

        $this->consume($routingKey, $amqMessage);
    }

    public function addConsumer(string $routing, ConsumerInterface $consumer): self
    {
        $this->consumers[$routing][] = $consumer;

        return $this;
    }

    private function consume(string $routingKey, AMQPMessage $message): void
    {
        /** @var ConsumerInterface[] $consummers */
        $consumers = $this->consumers[$routingKey] ?? [];

        if (empty($consumers)) {

            return;
        }

        foreach ($consumers as $consumer) {

            $consumer->execute($message);
        }
    }

    private function amqMessageAdapter(string $body, array $properties): AMQPMessage
    {
        return new AMQPMessage($body, $properties);
    }

    /** @var string[] */
    protected $consumers = [];
}
