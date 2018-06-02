<?php

declare(strict_types=1);

namespace App\Domain\User\Event;

use App\Domain\User\ValueObject\Auth\Credentials;
use App\Domain\User\ValueObject\Auth\HashedPassword;
use App\Domain\User\ValueObject\Email;
use Assert\Assertion;
use Broadway\Serializer\Serializable;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class UserWasCreated implements Serializable
{
    public static function deserialize(array $data): self
    {
        Assertion::keyExists($data, 'uuid');
        Assertion::keyExists($data, 'credentials');

        return new self(
            Uuid::fromString($data['uuid']),
            new Credentials(
                Email::fromString($data['credentials']['email']),
                HashedPassword::fromHash($data['credentials']['password'])
            )
        );
    }

    public function serialize(): array
    {
        return [
            'uuid'        => $this->uuid->toString(),
            'credentials' => [
                'email'    => $this->credentials->email->toString(),
                'password' => $this->credentials->password->toString(),
            ],
        ];
    }

    public function __construct(UuidInterface $uuid, Credentials $credentials)
    {
        $this->uuid = $uuid;
        $this->credentials = $credentials;
    }

    /**
     * @var UuidInterface
     */
    public $uuid;

    /**
     * @var Credentials
     */
    public $credentials;
}
