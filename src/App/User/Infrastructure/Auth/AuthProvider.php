<?php

declare(strict_types=1);

namespace App\User\Infrastructure\Auth;

use App\User\Domain\ValueObject\Email;
use App\Shared\Infrastructure\Persistence\ReadModel\Exception\NotFoundException;
use App\User\Infrastructure\ReadModel\Mysql\MysqlReadModelUserRepository;
use Assert\AssertionFailedException;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

final class AuthProvider implements UserProviderInterface
{
    public function __construct(private readonly MysqlReadModelUserRepository $userReadModelRepository)
    {
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        try {
            [$uuid, $email, $hashedPassword] = $this->userReadModelRepository->getCredentialsByEmail(
                Email::fromString($identifier)
            );

            return Auth::create($uuid, $email, $hashedPassword);
        } catch (NotFoundException) {
            throw new UserNotFoundException();
        }

    }
    /**
     * @throws NotFoundException
     * @throws AssertionFailedException
     * @throws NonUniqueResultException
     * @throws \Throwable
     *
     * @return Auth|UserInterface
     */
    public function loadUserByUsername(string $email): \App\User\Infrastructure\Auth\Auth|\Symfony\Component\Security\Core\User\UserInterface
    {
        [$uuid, $email, $hashedPassword] = $this->userReadModelRepository->getCredentialsByEmail(
            Email::fromString($email)
        );

        return Auth::create($uuid, $email, $hashedPassword);
    }

    /**
     * @throws NotFoundException
     * @throws AssertionFailedException
     * @throws NonUniqueResultException
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        return $this->loadUserByUsername($user->getUserIdentifier());
    }

    public function supportsClass(string $class): bool
    {
        return Auth::class === $class;
    }
}
