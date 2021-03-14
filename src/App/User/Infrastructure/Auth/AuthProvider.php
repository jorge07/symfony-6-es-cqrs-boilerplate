<?php

declare(strict_types=1);

namespace App\User\Infrastructure\Auth;

use App\User\Domain\ValueObject\Email;
use App\Shared\Infrastructure\Persistence\ReadModel\Exception\NotFoundException;
use App\User\Infrastructure\ReadModel\Mysql\MysqlReadModelUserRepository;
use Assert\AssertionFailedException;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

final class AuthProvider implements UserProviderInterface
{
    private MysqlReadModelUserRepository $userReadModelRepository;

    public function __construct(MysqlReadModelUserRepository $userReadModelRepository)
    {
        $this->userReadModelRepository = $userReadModelRepository;
    }

    /**
     * @throws NotFoundException
     * @throws AssertionFailedException
     * @throws NonUniqueResultException
     * @throws \Throwable
     *
     * @return Auth|UserInterface
     */
    public function loadUserByUsername(string $email)
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
        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass(string $class): bool
    {
        return Auth::class === $class;
    }
}
