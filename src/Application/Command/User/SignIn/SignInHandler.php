<?php

declare(strict_types=1);

namespace App\Application\Command\User\SignIn;

use App\Application\Command\CommandHandlerInterface;
use App\Domain\User\Exception\InvalidCredentialsException;
use App\Domain\User\Repository\UserCollectionInterface;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\ValueObject\Email;
use Ramsey\Uuid\UuidInterface;

class SignInHandler implements CommandHandlerInterface
{
    public function __invoke(SignInCommand $query): void
    {
        $uuid = $this->uuidFromEmail($query->email);

        $aggregateRoot = $this->userStore->get($uuid);

        $aggregateRoot->signIn($query->plainPassword);
    }

    private function uuidFromEmail(Email $email): UuidInterface
    {
        $uuid = $this->userCollection->existsEmail($email);

        if (null === $uuid) {

            throw new InvalidCredentialsException();
        }

        return $uuid;
    }

    public function __construct(UserRepositoryInterface $userStore, UserCollectionInterface $userCollection)
    {
        $this->userStore = $userStore;
        $this->userCollection = $userCollection;
    }

    /**
     * @var UserRepositoryInterface
     */
    private $userStore;

    /**
     * @var UserCollectionInterface
     */
    private $userCollection;
}
