<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller;

use App\Shared\Application\Command\CommandBusInterface;
use App\Shared\Application\Command\CommandInterface;
use Throwable;

abstract class CommandController
{
    public function __construct(private readonly CommandBusInterface $commandBus)
    {
    }

    /**
     * @throws Throwable
     */
    protected function handle(CommandInterface $command): void
    {
        $this->commandBus->handle($command);
    }
}
