<?php

declare(strict_types=1);

namespace App\User\Domain\Repository;

use App\User\Domain\ValueObject\Email;
use Ramsey\Uuid\UuidInterface;

interface CheckUserByEmailInterface
{
    public function existsEmail(Email $email): ?UuidInterface;
}
