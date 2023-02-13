<?php

declare(strict_types=1);

namespace App\User\Application\Command\ChangeEmail;

use App\Shared\Application\Command\CommandHandlerInterface;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\User\Domain\Specification\UniqueEmailSpecificationInterface;

final class ChangeEmailHandler implements CommandHandlerInterface
{
    public function __construct(private readonly UserRepositoryInterface $userRepository, private readonly UniqueEmailSpecificationInterface $uniqueEmailSpecification)
    {
    }

    public function __invoke(ChangeEmailCommand $command): void
    {
        $user = $this->userRepository->get($command->userUuid);

        $user->changeEmail($command->email, $this->uniqueEmailSpecification);

        $this->userRepository->store($user);
    }
}
