<?php

declare(strict_types=1);

namespace App\User\Application\Command\SignUp;

use App\Shared\Application\Command\CommandHandlerInterface;
use App\Shared\Domain\Exception\DateTimeException;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\User\Domain\Specification\UniqueEmailSpecificationInterface;
use App\User\Domain\User;

final class SignUpHandler implements CommandHandlerInterface
{
    public function __construct(private readonly UserRepositoryInterface $userRepository, private readonly UniqueEmailSpecificationInterface $uniqueEmailSpecification)
    {
    }

    /**
     * @throws DateTimeException
     */
    public function __invoke(SignUpCommand $command): void
    {
        $user = User::create($command->uuid, $command->credentials, $this->uniqueEmailSpecification);

        $this->userRepository->store($user);
    }
}
