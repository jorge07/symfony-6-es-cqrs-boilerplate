<?php

namespace App\Application\Command\User\Create;

use App\Application\Command\CommandHandlerInterface;
use App\Domain\User\Exception\EmailAlreadyExistException;
use App\Domain\User\Factory\UserFactory;
use App\Domain\User\Repository\UserCollectionInterface;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\ValueObject\Email;

class CreateUserHandler implements CommandHandlerInterface
{
    public function __invoke(CreateUserCommand $command): void
    {
        $this->checkEmail($command->credentials->email);

        $aggregateRoot = $this->userFactory->register($command->uuid, $command->credentials);

        $this->userRepository->store($aggregateRoot);
    }

    private function checkEmail(Email $email): void
    {
        if ($this->userCollection->existsEmail($email)) {

            throw new EmailAlreadyExistException();
        }
    }

    public function __construct(UserFactory $userFactory, UserRepositoryInterface $userRepository, UserCollectionInterface $userCollection)
    {
        $this->userFactory = $userFactory;
        $this->userRepository = $userRepository;
        $this->userCollection = $userCollection;
    }

    /**
     * @var UserFactory
     */
    private $userFactory;

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var UserCollectionInterface
     */
    private $userCollection;
}
