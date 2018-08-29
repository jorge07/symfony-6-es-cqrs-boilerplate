<?php

declare(strict_types=1);

namespace App\UI\Http\Rest\Controller;

use Symfony\Component\Messenger\MessageBusInterface;

abstract class CommandController
{
    protected function exec($command): void
    {
        $this->commandBus->dispatch($command);
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
