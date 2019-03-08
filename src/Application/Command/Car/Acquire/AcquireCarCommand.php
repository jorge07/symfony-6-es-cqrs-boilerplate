<?php

declare(strict_types=1);

namespace App\Application\Command\Car\Acquire;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class AcquireCarCommand
{
    /**
     * @var UuidInterface
     */
    public $uuid;

    /**
     * @var UuidInterface
     */
    public $owner;

    /**
     * @var \DateTime
     */
    public $date;

    public function __construct(string $uuid, string $owner, \DateTime $date)
    {
        $this->uuid = Uuid::fromString($uuid);
        $this->owner = Uuid::fromString($owner);
        $this->date = $date;
    }
}
