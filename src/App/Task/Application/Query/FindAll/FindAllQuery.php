<?php

declare(strict_types=1);

namespace App\Task\Application\Query\FindAll;

use App\Shared\Application\Query\QueryInterface;
use Ramsey\Uuid\Rfc4122\UuidV4;
use Ramsey\Uuid\UuidInterface;

/**
 * TODO Implement pagination criteria pattern
 */
final class FindAllQuery implements QueryInterface
{
    public UuidInterface $userUuid;

    public function __construct(string $userUuid)
    {
        $this->userUuid = UuidV4::fromString($userUuid);
    }
}
