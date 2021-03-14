<?php

declare(strict_types=1);

namespace App\User\Application\Command\ChangeEmail;

use App\Shared\Application\Command\CommandHandlerInterface;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\User\Domain\Specification\UniqueEmailSpecificationInterface;

final class ChangeEmailHandler implements CommandHandlerInterface
{
    private UserRepositoryInterface $userRepository;

    private UniqueEmailSpecificationInterface $uniqueEmailSpecification;

    public function __construct(
        UserRepositoryInterface $userRepository,
        UniqueEmailSpecificationInterface $uniqueEmailSpecification
    ) {
        $this->userRepository = $userRepository;
        $this->uniqueEmailSpecification = $uniqueEmailSpecification;
    }

    public function __invoke(ChangeEmailCommand $command): void
    {
        $user = $this->userRepository->get($command->userUuid);

        $user->changeEmail($command->email, $this->uniqueEmailSpecification);

        $this->userRepository->store($user);
    }
}
