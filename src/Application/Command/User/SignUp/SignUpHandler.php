<?php

namespace App\Application\Command\User\SignUp;

use App\Application\Command\CommandHandlerInterface;
use App\Domain\User\Factory\UserFactory;
use App\Domain\User\Repository\UserRepositoryInterface;

class SignUpHandler implements CommandHandlerInterface
{
    public function __invoke(SignUpCommand $command): void
    {
        $aggregateRoot = $this->userFactory->register($command->uuid, $command->credentials);

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
