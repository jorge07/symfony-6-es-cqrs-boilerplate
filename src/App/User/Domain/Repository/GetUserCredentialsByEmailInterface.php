<?php

declare(strict_types=1);

namespace App\User\Domain\Repository;

use App\User\Domain\ValueObject\Auth\UserCredentials;
use App\User\Domain\ValueObject\Email;

interface GetUserCredentialsByEmailInterface
{
    public function getCredentialsByEmail(Email $email): UserCredentials;
}
