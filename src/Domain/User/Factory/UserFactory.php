<?php

declare(strict_types=1);

namespace App\Domain\User\Factory;

use App\Domain\User\Exception\EmailAlreadyExistException;
use App\Domain\User\Repository\UserCollectionInterface;
use App\Domain\User\User;
use App\Domain\User\ValueObject\Auth\Credentials;
use Ramsey\Uuid\UuidInterface;

class UserFactory
{

    public function register(UuidInterface $uuid, Credentials $credentials): User
    {
        if ($this->userCollection->existsEmail($credentials->email)) {

            throw new EmailAlreadyExistException();
        }

        return User::create($uuid, $credentials);
    }

    public function __construct(UserCollectionInterface $userCollection)
    {
        $this->userCollection = $userCollection;
    }

    /**
     * @var UserCollectionInterface
     */
    private $userCollection;
}
