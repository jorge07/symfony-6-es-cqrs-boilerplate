<?php

declare(strict_types=1);

namespace App\Infrastructure\User\Auth;

use App\Domain\User\ValueObject\Auth\HashedPassword;
use App\Domain\User\ValueObject\Email;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Security\Core\Encoder\EncoderAwareInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class Auth implements UserInterface, EncoderAwareInterface
{
    /**
     * @throws \Assert\AssertionFailedException
     */
    public static function create(UuidInterface $uuid, string $email, string $hashedPassword): self
    {
        return new self($uuid, Email::fromString($email), HashedPassword::fromHash($hashedPassword));
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

    public function getEncoderName(): string
    {
        return 'bcrypt';
    }

    public function uuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function __toString(): string
    {
        return $this->email->toString();
    }

    private function __construct(UuidInterface $uuid, Email $email, HashedPassword $hashedPassword)
    {
        $this->uuid = $uuid;
        $this->email = $email;
        $this->hashedPassword = $hashedPassword;
    }

    /** @var UuidInterface */
    private $uuid;

    /** @var Email */
    private $email;

    /** @var HashedPassword */
    private $hashedPassword;
}
