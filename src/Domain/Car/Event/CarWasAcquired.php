<?php

declare(strict_types=1);

namespace App\Domain\Car\Event;

use Broadway\Serializer\Serializable;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class CarWasAcquired implements Serializable
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

    public function __construct(UuidInterface $uuid, UuidInterface $owner, \DateTime $date)
    {
        $this->uuid = $uuid;
        $this->owner = $owner;
        $this->date = $date;
    }

    public static function deserialize(array $data)
    {
        return new self(
            Uuid::fromString($data['uuid']),
            Uuid::fromString($data['owner']),
            new \DateTime($data['date'])
        );
    }

    public function serialize(): array
    {
        return [
            'uuid'  => $this->uuid->toString(),
            'owner' => $this->owner->toString(),
            'date'  => $this->date->format('Y-m-d H:i:s'),
        ];
    }
}
