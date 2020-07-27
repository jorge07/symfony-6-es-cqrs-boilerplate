<?php

declare(strict_types=1);

namespace App\Application\Command;

interface CommandBusInterface
{
    public function handle(CommandInterface $command): void;
}
