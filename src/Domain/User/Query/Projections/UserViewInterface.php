<?php

namespace App\Domain\User\Query\Projections;

use App\Domain\User\ValueObject\Auth\HashedPassword;
use App\Domain\User\ValueObject\Email;
use App\Domain\User\ValueObject\Uuid;
use Broadway\ReadModel\SerializableReadModel;

interface UserViewInterface extends SerializableReadModel
{
    public function uuid(): Uuid;

    public function email(): Email;

    public function hashedPassword(): HashedPassword;

    public function changeEmail(Email $email): void;
}
