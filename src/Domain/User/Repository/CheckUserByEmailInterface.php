<?php

namespace App\Domain\User\Repository;

use App\Domain\User\ValueObject\Email;
use App\Domain\User\ValueObject\Uuid;

interface CheckUserByEmailInterface
{
    public function existsEmail(Email $email): ?Uuid;
}
