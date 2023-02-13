<?php

declare(strict_types=1);

namespace App\User\Infrastructure\Auth;

use App\User\Domain\ValueObject\Auth\HashedPassword;
use App\User\Domain\ValueObject\Email;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherAwareInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class Auth implements UserInterface, PasswordHasherAwareInterface, PasswordAuthenticatedUserInterface, \Stringable
{
    private function __construct(private readonly UuidInterface $uuid, private readonly Email $email, private readonly HashedPassword $hashedPassword)
    {
    }

    public static function create(UuidInterface $uuid, Email $email, HashedPassword $hashedPassword): self
    {
        return new self($uuid, $email, $hashedPassword);
    }

    public function getUserIdentifier(): string
    {
        return $this->email->toString();
    }

    public function getUsername(): string
    {
        return $this->email->toString();
    }

    public function getPassword(): string
    {
        return $this->hashedPassword->toString();
    }

    public function getRoles(): array
    {
        return [
            'ROLE_USER',
        ];
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials(): void
    {
        // noop
    }

    public function getPasswordHasherName(): string
    {
        return 'hasher';
    }

    public function uuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function __toString(): string
    {
        return $this->email->toString();
    }
}
