<?php

declare(strict_types=1);

namespace App\Domain\User\Repository;

use App\Domain\User\User;
use App\Domain\User\ValueObject\Uuid;

interface UserRepositoryInterface
{
    public function get(Uuid $uuid): User;

    public function store(User $user): void;
}
