<?php

declare(strict_types=1);

namespace App\Domain\User\Event;

use App\Domain\Shared\ValueObject\DateTime;
use App\Domain\User\ValueObject\Email;
use Ramsey\Uuid\UuidInterface;

final class UserEmailChanged
{
    public UuidInterface $uuid;

    public Email $email;

    public DateTime $updatedAt;

    public function __construct(UuidInterface $uuid, Email $email, DateTime $updatedAt)
    {
        $this->email = $email;
        $this->uuid = $uuid;
        $this->updatedAt = $updatedAt;
    }
}
