<?php

declare(strict_types=1);

namespace App\Domain\User\Event;

use App\Domain\Shared\ValueObject\DateTime;
use App\Domain\User\ValueObject\Auth\Credentials;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

final class UserWasCreated
{
    /** @Groups("user_was_created") */
    public UuidInterface $uuid;

    /** @Groups("user_was_created") */
    public Credentials $credentials;

    /** @Groups("user_was_created") */
    public DateTime $createdAt;

    public function __construct(UuidInterface $uuid, Credentials $credentials, DateTime $createdAt)
    {
        $this->uuid = $uuid;
        $this->credentials = $credentials;
        $this->createdAt = $createdAt;
    }
}
