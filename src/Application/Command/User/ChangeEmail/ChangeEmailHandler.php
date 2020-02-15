<?php

declare(strict_types=1);

namespace App\Application\Command\User\ChangeEmail;

use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\Specification\UniqueEmailSpecificationInterface;
use Messenger\Bus\Handler\CommandHandlerInterface;

class ChangeEmailHandler implements CommandHandlerInterface
{
    public function __invoke(ChangeEmailCommand $command): void
    {
        $user = $this->userRepository->get($command->userUuid);

        $user->changeEmail($command->email, $this->uniqueEmailSpecification);

        $this->userRepository->store($user);
    }

    public function __construct(
        UserRepositoryInterface $userRepository,
        UniqueEmailSpecificationInterface $uniqueEmailSpecification
    ) {
        $this->userRepository = $userRepository;
        $this->uniqueEmailSpecification = $uniqueEmailSpecification;
    }

    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var UniqueEmailSpecificationInterface */
    private $uniqueEmailSpecification;
}
