<?php

declare(strict_types=1);

namespace App\Infrastructure\Car\Query\Projections;

use App\Infrastructure\User\Query\Projections\UserView;
use Broadway\Serializer\Serializable;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class CarView
{
    /** @var \DateTime */
    public $date;

    /** @var UserView */
    public $owner;

    /** @var UuidInterface */
    public $uuid;

    public static function fromSerializable(Serializable $event): self
    {
        return self::deserialize($event->serialize());
    }

    public static function deserialize(array $data): self
    {
        $instance = new self();

        $instance->uuid = Uuid::fromString($data['uuid']);
        $instance->date = new \DateTime($data['date']);

        return $instance;
    }
}
