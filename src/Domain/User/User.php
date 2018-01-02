<?php

declare(strict_types=1);

namespace App\Domain\User;

use App\Domain\User\Event\UserEmailChanged;
use App\Domain\User\Event\UserWasCreated;
use App\Domain\User\ValueObject\Email;
use Assert\Assertion;
use Broadway\EventSourcing\EventSourcedAggregateRoot;
use Ramsey\Uuid\UuidInterface;

class User extends EventSourcedAggregateRoot
{
    public static function create(UuidInterface $uuid, Email $email): self
    {
        $user = new self;

        $user->apply(new UserWasCreated($uuid, $email));

        return $user;
    }

    public function changeEmail(Email $email): void
    {
        $this->apply(new UserEmailChanged($email));
    }

    protected function applyUserWasCreated(UserWasCreated $event)
    {
        $this->uuid = $event->uuid;

        $this->setEmail($event->email);
    }

    protected function applyUserEmailChanged(UserEmailChanged $event)
    {
        Assertion::notEq($this->email->toString(), $event->email->toString(), 'New email should be different');

        $this->setEmail($event->email);
    }

    private function setEmail(Email $email): void
    {
        $this->email = $email;
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
}
