<?php

namespace App\Domain\User\Query\Repository;

use App\Domain\User\Query\Projections\UserViewInterface;
use App\Domain\User\ValueObject\Email;
use App\Domain\User\ValueObject\Uuid;

interface UserReadModelRepositoryInterface
{
    public function oneByUuid(Uuid $uuid): UserViewInterface;

    public function oneByEmail(Email $email): UserViewInterface;

    public function add(UserViewInterface $userRead): void;

    public function apply(): void;
}
