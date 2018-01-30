<?php

declare(strict_types=1);

namespace App\Infrastructure\Share\Event\Consumer;

use Broadway\Domain\DomainMessage;
use League\Tactician\CommandBus;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

final class EventQueueConsumer implements ConsumerInterface
{

    public function execute(AMQPMessage $msg)
    {
        $domainMessage = $this->wakeUpDomainMessage($msg);

        $this->eventBus->handle($domainMessage);
    }

    private function wakeUpDomainMessage(AMQPMessage $message): DomainMessage
    {
        return unserialize($message->body);
    }

    public function __construct(CommandBus $asyncEventBus)
    {
        $this->eventBus = $asyncEventBus;
    }

    /**
     * @var CommandBus
     */
    private $eventBus;
}
