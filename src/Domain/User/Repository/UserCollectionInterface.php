<?php

namespace App\Domain\User\Repository;

use App\Domain\User\ValueObject\Email;

interface UserCollectionInterface
{
    public function existsEmail(Email $email): bool;
}
