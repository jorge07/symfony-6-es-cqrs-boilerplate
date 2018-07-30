<?php

declare(strict_types=1);

namespace App\Domain\User\Auth;

use Ramsey\Uuid\UuidInterface;

interface SessionInterface
{
    /**
     * @return UuidInterface[]|string[]
     */
    public function get(): array;

    public function sameByUuid(string $uuid): bool;
}
