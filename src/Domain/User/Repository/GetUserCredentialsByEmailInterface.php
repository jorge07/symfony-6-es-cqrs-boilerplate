<?php

declare(strict_types=1);

namespace App\Domain\User\Repository;

use App\Domain\User\ValueObject\Email;

interface GetUserCredentialsByEmailInterface
{
    /**
     * @return array[Uuid, string $email, string $hashedPassword]
     */
    public function getCredentialsByEmail(Email $email): array;
}
