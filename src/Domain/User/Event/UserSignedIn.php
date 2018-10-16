<?php

declare(strict_types=1);

namespace App\Domain\User\Event;

use App\Domain\User\ValueObject\Email;
use App\Domain\User\ValueObject\Uuid;
use Assert\Assertion;
use Broadway\Serializer\Serializable;

final class UserSignedIn implements Serializable
{
    public static function create(Uuid $uuid, Email $email): self
    {
        $instance = new self();

        $instance->uuid = $uuid;
        $instance->email = $email;

        return $instance;
    }

    /**
     * @throws \Assert\AssertionFailedException
     */
    public static function deserialize(array $data): self
    {
        Assertion::keyExists($data, 'uuid');
        Assertion::keyExists($data, 'email');

        return self::create(
            Uuid::fromString($data['uuid']),
            Email::fromString($data['email'])
        );
    }

    public function serialize(): array
    {
        return [
            'uuid'  => $this->uuid->toString(),
            'email' => $this->email->toString(),
        ];
    }

    /** @var Email */
    public $email;

    /** @var Uuid */
    public $uuid;
}
