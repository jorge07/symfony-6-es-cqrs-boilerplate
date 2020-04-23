<?php

declare(strict_types=1);

namespace App\Application\Command\User\SignUp;

use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\Specification\UniqueEmailSpecificationInterface;
use App\Domain\User\User;
use App\Infrastructure\Share\Bus\Command\CommandHandlerInterface;

class SignUpHandler implements CommandHandlerInterface
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

    /**
     * @throws \App\Domain\Shared\Exception\DateTimeException
     */
    public function __invoke(SignUpCommand $command): void
    {
        $user = User::create($command->getUuid(), $command->getCredentials(), $this->uniqueEmailSpecification);

        $this->userRepository->store($user);
    }
}
