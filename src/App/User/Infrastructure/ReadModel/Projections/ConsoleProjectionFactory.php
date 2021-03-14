<?php

declare(strict_types=1);

namespace App\User\Infrastructure\ReadModel\Projections;

use App\User\Domain\Event\UserSignedIn;
use App\Shared\Infrastructure\Bus\AsyncEvent\MessengerAsyncEventBus;
use Broadway\Domain\DomainMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

/**
 * Class ConsoleProjectionFactory
 *
 * @description This is a dummy example about how to handle custom Events
 *  In this case all events sent to users transport will be consumed here.
 *  In this example we only act on UserSignedIn event.
 *
 *  In order to be able to process all events to be sent to Elasticseach I didn't find any other solution than send the
 *  DomainMessage instead the Event itself so it's send to the Async event bus inside the envelop. That a problem
 *  when trying to identify the object itself for routing or others. Because of this I added a routing key to the
 *  messenger envelope in the async event bus so you can configure symfony messenger to route your events to a different
 *  transport base on routing keys. There's room for improvement here but I'm facing messenger limitations so will
 *  probably need to contribute to the project to get certain features like multiple messages per handler
 * (a la EventListener) instead the current one to one binding or Subscription models.
 *
 *  An example of how to use this:
 *      MessengerConfig:
 *
 * @see MessengerAsyncEventBus::handle
 */
final class ConsoleProjectionFactory implements MessageSubscriberInterface
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function __invoke(DomainMessage $message): void
    {
        if ($message->getPayload() instanceof UserSignedIn) {
            $this->onUserSignedIn($message->getPayload());
        }
    }

    public static function getHandledMessages(): iterable
    {
        yield DomainMessage::class => [
            'from_transport' => 'users',
            'bus' => 'messenger.bus.event.async',
        ];
    }

    private function onUserSignedIn(UserSignedIn $event): void
    {
        $this->logger->info('Welcome to the jungle ' . $event->email->toString());
    }
}
