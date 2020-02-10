<?php

declare(strict_types=1);

namespace App\Application\Command\User\SignIn;

use App\Application\Command\CommandHandlerInterface;
use App\Domain\User\Exception\InvalidCredentialsException;
use App\Domain\User\Repository\CheckUserByEmailInterface;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\ValueObject\Email;
use Ramsey\Uuid\UuidInterface;

class SignInHandler implements CommandHandlerInterface
{
    public function __invoke(SignInCommand $command): void
    {
        $uuid = $this->uuidFromEmail($command->email);

        $user = $this->userStore->get($uuid);

        $user->signIn($command->plainPassword);

        $this->userStore->store($user);
    }

    private function uuidFromEmail(Email $email): UuidInterface
    {
        $uuid = $this->userCollection->existsEmail($email);

        if (null === $uuid) {
            throw new InvalidCredentialsException();
        }

        return $uuid;
    }

    public function __construct(UserRepositoryInterface $userStore, CheckUserByEmailInterface $userCollection)
    {
        $this->userStore = $userStore;
        $this->userCollection = $userCollection;
    }

    /** @var UserRepositoryInterface */
    private $userStore;

    /** @var CheckUserByEmailInterface */
    private $userCollection;
}
