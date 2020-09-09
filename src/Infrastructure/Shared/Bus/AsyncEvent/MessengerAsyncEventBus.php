<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared\Bus\AsyncEvent;

use App\Infrastructure\Shared\Bus\MessageBusExceptionTrait;
use Broadway\Domain\DomainMessage;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;

final class MessengerAsyncEventBus
{
    use MessageBusExceptionTrait;

    private MessageBusInterface $messageBus;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    /**
     * @throws Throwable
     */
    public function handle(DomainMessage $command): void
    {
        try {
            $this->messageBus->dispatch($command, [
                new AmqpStamp($command->getType()),
            ]);
        } catch (HandlerFailedException $error) {
            $this->throwException($error);
        }
    }
}
