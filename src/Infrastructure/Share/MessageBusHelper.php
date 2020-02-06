<?php

declare(strict_types=1);

namespace App\Infrastructure\Share;

use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Throwable;

final class MessageBusHelper
{
    private function __construct()
    {
    }

    /**
     * @throws Throwable
     */
    public static function dispatchCommand(MessageBusInterface $messageBus, $command): void
    {
        try {
            $messageBus->dispatch($command);
        } catch (HandlerFailedException $e) {
            self::throwException($e);
        }
    }

    /**
     * @throws Throwable
     */
    public static function dispatchQuery(MessageBusInterface $messageBus, $query)
    {
        try {
            $command = $messageBus->dispatch($query);

            /** @var HandledStamp $stamp */
            $stamp = $command->last(HandledStamp::class);

            return $stamp->getResult();
        } catch (HandlerFailedException $e) {
            self::throwException($e);
        }
    }

    /**
     * @throws Throwable
     */
    private static function throwException(HandlerFailedException $exception)
    {
        while ($exception instanceof HandlerFailedException) {
            /** @var Throwable $exception */
            $exception = $exception->getPrevious();
        }

        throw $exception;
    }
}
