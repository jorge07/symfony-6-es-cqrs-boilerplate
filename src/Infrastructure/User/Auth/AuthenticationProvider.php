<?php

declare(strict_types=1);

namespace App\Infrastructure\User\Auth;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Ramsey\Uuid\UuidInterface;

final class AuthenticationProvider
{
    /**
     * @throws \Assert\AssertionFailedException
     */
    public function generateToken(UuidInterface $uuid, string $email, string $hashedPassword): string
    {
        $auth = Auth::create($uuid, $email, $hashedPassword);

        return $this->JWTManager->create($auth);
    }

    public function __construct(JWTTokenManagerInterface $JWTManager)
    {
        $this->JWTManager = $JWTManager;
    }

    /** @var JWTTokenManagerInterface */
    private $JWTManager;
}
