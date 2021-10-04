<?php

declare(strict_types=1);

namespace App\Task\Application\Query\FindOneByUuid;

use App\Shared\Application\Query\QueryInterface;
use Ramsey\Uuid\Rfc4122\UuidV4;
use Ramsey\Uuid\UuidInterface;

final class FindOneByUuidQuery implements QueryInterface
{

    public UuidInterface $uuid;

    public function __construct(string $uuid)
    {
        $this->uuid = UuidV4::fromString($uuid);
    }
}
