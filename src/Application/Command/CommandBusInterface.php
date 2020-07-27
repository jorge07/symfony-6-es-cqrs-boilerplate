<?php

namespace App\Application\Command;

interface CommandBusInterface
{
    public function handle(CommandInterface $command): void;
}
