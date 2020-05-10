<?php

declare(strict_types=1);

namespace App\Domain\User\Repository;

use App\Domain\User\ValueObject\Email;

interface GetUserCredentialsByEmailInterface
{
    /**
     * @return array{0: \Ramsey\Uuid\UuidInterface, 1: string, 2: string}
     */
    public function getCredentialsByEmail(Email $email): array;
}
