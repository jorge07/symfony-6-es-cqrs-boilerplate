<?php

namespace App\Domain\User\Query\Projections;

use App\Domain\User\ValueObject\Email;
use Broadway\ReadModel\SerializableReadModel;
use Ramsey\Uuid\UuidInterface;

interface UserViewInterface extends SerializableReadModel
{
    public function uuid(): UuidInterface;

    public function email(): string;

    public function hashedPassword(): string;

    public function changeEmail(Email $email): void;
}
