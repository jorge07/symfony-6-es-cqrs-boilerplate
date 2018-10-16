<?php

declare(strict_types=1);

namespace App\Infrastructure\User\Query\Projections;

use App\Domain\User\Query\Projections\UserViewInterface;
use App\Domain\User\ValueObject\Auth\Credentials;
use App\Domain\User\ValueObject\Auth\HashedPassword;
use App\Domain\User\ValueObject\Email;
use App\Domain\User\ValueObject\Uuid;
use Broadway\Serializer\Serializable;

class UserView implements UserViewInterface
{
    public static function fromSerializable(Serializable $event): self
    {
        return self::deserialize($event->serialize());
    }

    /**
     * @throws \Assert\AssertionFailedException
     */
    public static function deserialize(array $data): self
    {
        $instance = new self();

        $instance->uuid = Uuid::fromString($data['uuid']);
        $instance->credentials = new Credentials(
            Email::fromString($data['credentials']['email']),
            HashedPassword::fromHash($data['credentials']['password'] ?? '')
        );

        return $instance;
    }

    public function serialize(): array
    {
        return [
            'uuid'        => $this->getId(),
            'credentials' => [
                'email'    => $this->credentials->email->toString(),
                'password' => $this->credentials->password->toString(),
            ],
        ];
    }

    public function uuid(): Uuid
    {
        return $this->uuid;
    }

    public function email(): Email
    {
        return $this->credentials->email;
    }

    public function changeEmail(Email $email): void
    {
        $this->credentials->email = $email;
    }

    public function hashedPassword(): HashedPassword
    {
        return $this->credentials->password;
    }

    public function getId(): string
    {
        return $this->uuid->toString();
    }

    /** @var Uuid */
    private $uuid;

    /** @var Credentials */
    private $credentials;
}
