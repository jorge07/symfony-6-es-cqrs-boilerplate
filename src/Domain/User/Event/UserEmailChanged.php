<?php

declare(strict_types=1);

namespace App\Domain\User\Event;

use App\Domain\Shared\ValueObject\DateTime;
use App\Domain\User\ValueObject\Email;
use Assert\Assertion;
use Broadway\Serializer\Serializable;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

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
            Email::fromString($data['email']),
            DateTime::fromString($data['updated_at'])
        );
    }

    public function serialize(): array
    {
        return [
            'uuid' => $this->uuid->toString(),
            'email' => $this->email->toString(),
            'updated_at' => $this->updatedAt->toString(),
        ];
    }

    public function __construct(UuidInterface $uuid, Email $email, DateTime $updatedAt)
    {
        $this->email = $email;
        $this->uuid = $uuid;
        $this->updatedAt = $updatedAt;
    }

    /** @var UuidInterface */
    public $uuid;

    /** @var Email */
    public $email;

    /** @var DateTime */
    public $updatedAt;
}
