<?php

declare(strict_types=1);

namespace App\User\Application\Command\SignUp;

use App\Shared\Application\Command\CommandHandlerInterface;
use App\Shared\Domain\Exception\DateTimeException;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\User\Domain\Specification\UniqueEmailSpecificationInterface;
use App\User\Domain\User;
use App\User\Domain\ValueObject\Auth\Credentials;
use App\User\Domain\ValueObject\Auth\HashedPassword;
use App\User\Domain\ValueObject\Email;

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
        $credentials = new Credentials(
            Email::fromString($command->email),
            HashedPassword::encode($command->plainPassword),
        );

        $user = User::create($command->uuid, $credentials, $this->uniqueEmailSpecification);

        $this->userRepository->store($user);
    }
}
