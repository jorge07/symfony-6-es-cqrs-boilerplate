<?php

declare(strict_types=1);

namespace App\User\Infrastructure\Auth;

use App\User\Domain\ValueObject\Auth\HashedPassword;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;

final class PasswordHasher implements PasswordHasherInterface {

    private string $hasher = HashedPassword::class;

    public function hash(string $plainPassword): string 
    {
        return $this->hasher::encode($plainPassword)->toString();
    }

    public function verify(string $hashedPassword, string $plainPassword): bool
    {
        return $this->hasher::fromHash($hashedPassword)->match($plainPassword);
    }

    public function needsRehash(string $hashedPassword): bool
    {
        return false;
    }
}
