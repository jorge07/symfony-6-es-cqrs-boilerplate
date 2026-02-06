<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Bus;

use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Throwable;

trait MessageBusExceptionTrait
{
    /**
     * @throws Throwable
     */
    public function throwException(HandlerFailedException $exception): void
    {
        while ($exception instanceof HandlerFailedException) {
            /** @var Throwable $exception */
            $exception = $exception->getPrevious();
        }

        throw $exception;
    }
}
