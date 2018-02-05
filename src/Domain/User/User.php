<?php

declare(strict_types=1);

namespace App\Domain\User;

use App\Domain\User\Event\UserEmailChanged;
use App\Domain\User\Event\UserSignedIn;
use App\Domain\User\Event\UserWasCreated;
use App\Domain\User\Exception\InvalidCredentialsException;
use App\Domain\User\ValueObject\Auth\Credentials;
use App\Domain\User\ValueObject\Auth\HashedPassword;
use App\Domain\User\ValueObject\Email;
use Assert\Assertion;
use Broadway\EventSourcing\EventSourcedAggregateRoot;
use Ramsey\Uuid\UuidInterface;

class User extends EventSourcedAggregateRoot
{
    public static function create(UuidInterface $uuid, Credentials $credentials): self
    {
        $user = new self;

        $user->apply(new UserWasCreated($uuid, $credentials));

        return $user;
    }

    public function changeEmail(Email $email): void
    {
        $this->apply(new UserEmailChanged($this->uuid, $email));
    }

    /**
     * @param string $plainPassword
     * @throws InvalidCredentialsException
     */
    public function signIn(string $plainPassword): void
    {
        $match = $this->hashedPassword->match($plainPassword);

        if (!$match) {
            throw new InvalidCredentialsException();
        }

        $this->apply(UserSignedIn::fromEmail($this->email));
    }

    protected function applyUserWasCreated(UserWasCreated $event): void
    {
        $this->uuid = $event->uuid;

        $this->setEmail($event->credentials->email);
        $this->setHashedPassword($event->credentials->password);
    }

    protected function applyUserEmailChanged(UserEmailChanged $event): void
    {
        Assertion::notEq($this->email->toString(), $event->email->toString(), 'New email should be different');

        $this->setEmail($event->email);
    }

    private function setEmail(Email $email): void
    {
        $this->email = $email;
    }
    
    private function setHashedPassword(HashedPassword $hashedPassword): void
    {
        $this->hashedPassword = $hashedPassword;
    }

    public function email(): string
    {
        return $this->email->toString();
    }

    public function uuid(): string
    {
        return $this->uuid->toString();
    }

    public function getAggregateRootId(): string
    {
        return $this->uuid->toString();
    }

    /** @var UuidInterface */
    private $uuid;

    /** @var Email */
    private $email;

    /** @var HashedPassword */
    private $hashedPassword;
}
