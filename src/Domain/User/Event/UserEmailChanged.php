<?php

declare(strict_types=1);

namespace App\Domain\User\Event;

use App\Domain\User\ValueObject\Email;
use App\Domain\User\ValueObject\Uuid;
use Assert\Assertion;
use Broadway\Serializer\Serializable;

final class UserEmailChanged implements Serializable
{
    /**
     * @throws \Assert\AssertionFailedException
     */
    public static function deserialize(array $data): self
    {
        Assertion::keyExists($data, 'uuid');
        Assertion::keyExists($data, 'email');

        return new self(
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

    public function __construct(Uuid $uuid, Email $email)
    {
        $this->email = $email;
        $this->uuid = $uuid;
    }

    /**
     * @var Uuid
     */
    public $uuid;

    /**
     * @var Email
     */
    public $email;
}
