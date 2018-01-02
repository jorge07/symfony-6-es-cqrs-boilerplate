<?php

namespace App\Application\Command\User\Create;

use App\Application\Command\CommandHandlerInterface;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\User;

class CreateUserHandler implements CommandHandlerInterface
{
    public function __invoke(CreateUserCommand $command)
    {
        $aggregateRoot = User::create($command->uuid, $command->email);

        $this->userRepository->store($aggregateRoot);
    }

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;
}
