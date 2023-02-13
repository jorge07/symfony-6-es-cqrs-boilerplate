<?php

declare(strict_types=1);

namespace App\User\Domain\Repository;

use App\User\Domain\ValueObject\Email;

interface GetUserCredentialsByEmailInterface
{
    /**
     * @return array{0: \Ramsey\Uuid\UuidInterface, 1: Email, 2: \App\User\Domain\ValueObject\Auth\HashedPassword}
     */
    public function getCredentialsByEmail(Email $email): array;
}
