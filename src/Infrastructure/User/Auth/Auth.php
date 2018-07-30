<?php

declare(strict_types=1);

namespace App\Infrastructure\User\Auth;

use App\Domain\User\Query\Projections\UserViewInterface;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Security\Core\Encoder\EncoderAwareInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class Auth implements UserInterface, EncoderAwareInterface
{
    public static function fromUser(UserViewInterface $user): self
    {
        return new self($user);
    }

    public function getUsername(): string
    {
        return $this->user->email();
    }

    public function getPassword(): string
    {
        return $this->user->hashedPassword();
    }

    public function getRoles(): array
    {
        return [
            'ROLE_USER',
        ];
    }

    public function getSalt(): void
    {
    }

    public function eraseCredentials(): void
    {
        // noop
    }

    public function getEncoderName(): string
    {
        return 'bcrypt';
    }

    public function uuid(): UuidInterface
    {
        return $this->user->uuid();
    }

    public function __toString(): string
    {
        return $this->user->email();
    }

    private function __construct(UserViewInterface $user)
    {
        $this->user = $user;
    }

    /** @var UserViewInterface */
    private $user;
}
