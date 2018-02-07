<?php

declare(strict_types=1);

namespace App\Domain\User\Event;

use App\Domain\User\ValueObject\Email;
use Broadway\Serializer\Serializable;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class UserSignedIn implements Serializable
{
    /** @var Email */
    public $email;

    /** @var UuidInterface */
    public $uuid;

    public static function create(UuidInterface $uuid, Email $email)
    {
        $instance = new self();

        $instance->uuid = $uuid;
        $instance->email = $email;

        return $instance;
    }

    public static function deserialize(array $data)
    {
        return self::create(
            Uuid::fromString($data['uuid']),
            Email::fromString($data['email'])
        );
    }

    public function serialize(): array
    {
        return [
            'uuid' => $this->uuid->toString(),
            'email' => $this->email->toString()
        ];
    }


}
