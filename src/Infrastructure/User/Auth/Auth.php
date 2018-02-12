<?php

declare(strict_types=1);

namespace App\Infrastructure\User\Auth;

use App\Domain\User\Query\UserView;
use Symfony\Component\Security\Core\Encoder\EncoderAwareInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class Auth implements UserInterface, EncoderAwareInterface
{
    public static function fromUser(UserView $user): self
    {
        return new self($user);
    }

    public function getUsername(): string
    {
        return (string) $this->user->credentials->email;
    }

    public function getPassword(): string
    {
        return (string) $this->user->credentials->password;
    }

    public function getRoles(): array
    {
        return [
            'ROLE_USER'
        ];
    }

    public function getSalt(): void
    {
    }

    public function eraseCredentials(): void
    {
        // noop
    }

    public function __toString(): string 
    {
        return (string) $this->user->credentials->email;
    }

    private function __construct(UserView $user)
    {
        $this->user = $user;
    }

    /** @var UserView */
    private $user;

    public function getEncoderName(): string
    {
        return 'bcrypt';
    }
}
