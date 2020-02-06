<?php

declare(strict_types=1);

namespace App\UI\Http\Rest\Controller;

use App\Infrastructure\Share\MessageBusHelper;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;

abstract class CommandController
{
    /**
     * @throws Throwable
     */
    protected function exec($command): void
    {
        MessageBusHelper::dispatchCommand($this->commandBus, $command);
    }

    public function __construct(MessageBusInterface $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    /**
     * @var MessageBusInterface
     */
    private $commandBus;
}
