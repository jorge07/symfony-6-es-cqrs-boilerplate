<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Bus\AsyncEvent;

use App\Shared\Infrastructure\Bus\MessageBusExceptionTrait;
use Broadway\Domain\DomainMessage;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;

final class MessengerAsyncEventBus
{
    use MessageBusExceptionTrait;

    public function __construct(private readonly MessageBusInterface $messageBus)
    {
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
