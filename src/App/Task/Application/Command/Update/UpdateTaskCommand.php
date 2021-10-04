<?php

declare(strict_types=1);

namespace App\Task\Application\Command\Update;

use App\Shared\Application\Command\CommandInterface;
use Ramsey\Uuid\Rfc4122\UuidV4;
use Ramsey\Uuid\UuidInterface;

final class UpdateTaskCommand implements CommandInterface
{
    public UuidInterface $uuid;
    public array $payload;

    public function __construct(array $payload, string $uuid)
    {
        $this->uuid = UuidV4::fromString($uuid);
        $this->payload = $payload;
        $this->payload['completedAt'] = isset($payload['completedAt']);
    }
}
