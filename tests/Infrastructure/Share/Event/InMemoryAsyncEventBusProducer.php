<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Share\Event;

use Broadway\Domain\DomainMessage;
use League\Tactician\CommandBus;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;

class InMemoryAsyncEventBusProducer implements ProducerInterface
{
    public function publish($msgBody, $routingKey = '', $additionalProperties = array())
    {
        $message = $this->messageAdapter($msgBody);

        $this->asyncEventBus->handle($message);
    }

    private function messageAdapter(string $body): DomainMessage
    {
        return unserialize($body);
    }

    public function __construct(CommandBus $asyncEventBus)
    {
        $this->asyncEventBus = $asyncEventBus;
    }

    /**
     * @var CommandBus
     */
    private $asyncEventBus;

}
