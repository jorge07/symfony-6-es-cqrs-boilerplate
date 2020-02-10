<?php

declare(strict_types=1);

namespace App\UI\Http\Rest\Controller;

use App\Infrastructure\Share\Bus\CommandBus;
use App\Infrastructure\Share\Bus\CommandInterface;

abstract class CommandController
{
    /**
     * @throws \Throwable
     */
    protected function exec(CommandInterface $command): void
    {
        $this->commandBus->handle($command);
    }

    public function __construct(CommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    /** @var CommandBus */
    private $commandBus;
}
