<?php

declare(strict_types=1);

namespace App\Domain\User\Auth;

use App\Domain\User\ValueObject\Uuid;

interface SessionInterface
{
    /**
     * @return Uuid[]|string[]
     */
    public function get(): array;

    public function sameByUuid(string $uuid): bool;
}
