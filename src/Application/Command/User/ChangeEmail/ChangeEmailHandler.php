<?php

declare(strict_types=1);

namespace App\Application\Command\User\ChangeEmail;

use App\Application\Command\CommandHandlerInterface;
use App\Domain\User\Repository\UserRepositoryInterface;

class ChangeEmailHandler implements CommandHandlerInterface
{
    public function __invoke(ChangeEmailCommand $command)
    {
        $user = $this->userRepository->get($command->userUuid);

        $user->changeEmail($command->email);

        $this->userRepository->store($user);
    }

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /** @var UserRepositoryInterface */
    private $userRepository;
}
