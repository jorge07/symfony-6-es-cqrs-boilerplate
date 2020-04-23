<?php

declare(strict_types=1);

namespace App\Infrastructure\User\Auth;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Ramsey\Uuid\UuidInterface;

final class AuthenticationProvider
{
    private JWTTokenManagerInterface $JWTManager;

    public function __construct(JWTTokenManagerInterface $JWTManager)
    {
        $this->JWTManager = $JWTManager;
    }

    /**
     * @throws \Assert\AssertionFailedException
     */
    public function generateToken(UuidInterface $uuid, string $email, string $hashedPassword): string
    {
        $auth = Auth::create($uuid, $email, $hashedPassword);

        return $this->JWTManager->create($auth);
    }
}
