<?php

namespace App\Domain\Shared\Event\Processor;

use Broadway\Domain\DomainEventStream;

interface EventStreamProcessorInterface
{
    public function process(DomainEventStream $stream);
}
