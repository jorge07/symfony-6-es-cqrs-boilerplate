<?php

namespace App\Tests\Infrastructure\Share\Bus;

use League\Tactician\Middleware;

class EventCollectorMiddleware implements Middleware
{
    public function execute($command, callable $next)
    {
        $this->publishedEvents[] = $command;

        return $next($command);
    }

    public function popEvents(): array
    {
        $events = $this->publishedEvents;

        $this->publishedEvents = [];

        return $events;
    }

    private $publishedEvents = [];
}
