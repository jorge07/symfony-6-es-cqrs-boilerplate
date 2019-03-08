<?php

declare(strict_types=1);

namespace App\Domain\Car;

use App\Domain\Car\Event\CarWasAcquired;
use Broadway\EventSourcing\EventSourcedAggregateRoot;
use Ramsey\Uuid\UuidInterface;

class Car extends EventSourcedAggregateRoot
{
    public static function create(UuidInterface $uuid, UuidInterface $owner, \DateTime $date): self
    {
        $instance = new self();

        $instance->apply(new CarWasAcquired($uuid, $owner, $date));

        return $instance;
    }

    public function getAggregateRootId(): string
    {
        return $this->uuid->toString();
    }

    protected function applyCarWasAcquired(CarWasAcquired $event)
    {
        $this->uuid = $event->uuid;
        $this->owner = $event->owner;
        $this->date = $event->date;
    }

    /** @var UuidInterface */
    private $uuid;

    /** @var UuidInterface */
    private $owner;

    /** @var \DateTime */
    private $date;
}
