<?php

declare(strict_types=1);

namespace App\User\Application\Query\Auth;

use App\User\Domain\ValueObject\Auth\HashedPassword;
use App\User\Domain\ValueObject\Email;
use Ramsey\Uuid\UuidInterface;

interface AuthenticationProviderInterface
{
    public function generateToken(UuidInterface $uuid, Email $email, HashedPassword $hashedPassword): string;
}
