<?php

declare(strict_types=1);

namespace App\User\Domain\Repository;

use App\User\Domain\ValueObject\Email;
use Ramsey\Uuid\UuidInterface;

interface UserReadModelRepositoryInterface
{
    public function oneByUuid(UuidInterface $uuid): mixed;

    public function oneByEmail(Email $email): mixed;

    public function oneByEmailAsArray(Email $email): array;
}
