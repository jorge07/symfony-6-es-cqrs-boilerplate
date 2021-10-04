<?php

declare(strict_types=1);

namespace App\Task\Application\Command\Create;

use App\Shared\Application\Command\CommandInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class CreateTaskCommand implements CommandInterface
{
    public UuidInterface $userId;
    public UuidInterface $uuid;

    public function __construct(
        string $uuid,
        string $userId,
        public string $title,
        public bool $completed,
    )
    {
        $this->uuid = Uuid::fromString($uuid);
        $this->userId = Uuid::fromString($userId);
    }
}
