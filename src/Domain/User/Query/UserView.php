<?php

declare(strict_types=1);

namespace App\Domain\User\Query;

use Broadway\ReadModel\SerializableReadModel;
use Broadway\Serializer\Serializable;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class UserView implements SerializableReadModel
{
    /** @var UuidInterface */
    public $uuid;

    /** @var string */
    public $email;

    public static function fromSerializable(Serializable $event): self
    {
        return self::deserialize($event->serialize());
    }

    public static function deserialize(array $data): self
    {
        $instance = new self;

        $instance->uuid = Uuid::fromString($data['uuid']);
        $instance->email = $data['email'];

        return $instance;
    }

    public function serialize(): array
    {
        return [
            'uuid' => $this->getId(),
            'email' => $this->email
        ];
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->uuid->toString();
    }
}
