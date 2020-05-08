<?php

declare(strict_types=1);

namespace App\Infrastructure\Share\Bus\Event;

use Broadway\Domain\DomainMessage;

final class Event implements EventInterface
{
    private DomainMessage $domainMessage;

    public function __construct(DomainMessage $domainMessage)
    {
        $this->domainMessage = $domainMessage;
    }

    public function getDomainMessage(): DomainMessage
    {
        return $this->domainMessage;
    }
}
