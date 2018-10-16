<?php

declare(strict_types=1);

namespace App\Domain\User\Factory;

use App\Domain\User\Exception\EmailAlreadyExistException;
use App\Domain\User\Repository\CheckUserByEmailInterface;
use App\Domain\User\User;
use App\Domain\User\ValueObject\Auth\Credentials;
use App\Domain\User\ValueObject\Uuid;

class UserFactory
{
    public function register(Uuid $uuid, Credentials $credentials): User
    {
        if ($this->userCollection->existsEmail($credentials->email)) {
            throw new EmailAlreadyExistException('Email already registered.');
        }

        return User::create($uuid, $credentials);
    }

    public function __construct(CheckUserByEmailInterface $userCollection)
    {
        $this->userCollection = $userCollection;
    }

    /**
     * @var CheckUserByEmailInterface
     */
    private $userCollection;
}
