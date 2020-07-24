<?php

declare(strict_types=1);

namespace App\Infrastructure\User\Auth;

use App\Domain\User\ValueObject\Auth\HashedPassword;
use App\Domain\User\ValueObject\Email;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Ramsey\Uuid\UuidInterface;

final class AuthenticationProvider
{
    private JWTTokenManagerInterface $JWTManager;

    public function __construct(JWTTokenManagerInterface $JWTManager)
    {
        $this->JWTManager = $JWTManager;
    }

    public function generateToken(UuidInterface $uuid, Email $email, HashedPassword $hashedPassword): string
    {
        $auth = Auth::create($uuid, $email, $hashedPassword);

        return $this->JWTManager->create($auth);
    }
}
