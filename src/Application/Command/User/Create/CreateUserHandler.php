<?php

namespace App\Application\Command\User\Create;

use App\Application\Command\CommandHandlerInterface;
use App\Domain\User\Factory\UserFactory;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\User;

class CreateUserHandler implements CommandHandlerInterface
{
    public function __invoke(CreateUserCommand $command)
    {
        $aggregateRoot = $this->userFactory->register($command->uuid, $command->email);

        $this->userRepository->store($aggregateRoot);
    }

    public function __construct(UserFactory $userFactory, UserRepositoryInterface $userRepository)
    {
        $this->userFactory = $userFactory;
        $this->userRepository = $userRepository;
    }

    /**
     * @var UserFactory
     */
    private $userFactory;

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;
}
