<?php

declare(strict_types=1);

namespace App\Infrastructure\Share\Event\Publisher;

use Broadway\Domain\DomainMessage;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

final class AsyncEventPublisher implements EventPublisher, EventSubscriberInterface
{

    public function publish(): void
    {
        foreach ($this->events as $event) {

            $this->eventProducer->publish(serialize($event->getPayload()), $event->getType());
        }
    }

    public function handle(DomainMessage $message): void
    {
        $this->events[] = $message;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::TERMINATE => 'publish'
        ];
    }

    public function __construct(ProducerInterface $eventProducer)
    {
        $this->eventProducer = $eventProducer;
    }

    /**
     * @var ProducerInterface
     */
    private $eventProducer;

    /** @var DomainMessage[] */
    private $events = [];
}
