<?php

declare(strict_types=1);

namespace App\User\Infrastructure\Auth;

use App\User\Domain\ValueObject\Email;
use App\Shared\Domain\Exception\NotFoundException;
use App\User\Infrastructure\ReadModel\Mysql\MysqlReadModelUserRepository;
use Assert\AssertionFailedException;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @implements UserProviderInterface<Auth>
 */
final class AuthProvider implements UserProviderInterface
{
    public function __construct(private readonly MysqlReadModelUserRepository $userReadModelRepository)
    {
    }

    public function loadUserByIdentifier(string $identifier): Auth
    {
        try {
            $credentials = $this->userReadModelRepository->getCredentialsByEmail(
                Email::fromString($identifier)
            );

            return Auth::create($credentials->uuid, $credentials->email, $credentials->password);
        } catch (NotFoundException) {
            throw new UserNotFoundException();
        }
    }

    /**
     * @throws NotFoundException
     * @throws AssertionFailedException
     * @throws NonUniqueResultException
     * @throws \Throwable
     */
    public function loadUserByUsername(string $email): Auth
    {
        $credentials = $this->userReadModelRepository->getCredentialsByEmail(
            Email::fromString($email)
        );

        return Auth::create($credentials->uuid, $credentials->email, $credentials->password);
    }

    /**
     * @throws NotFoundException
     * @throws AssertionFailedException
     * @throws NonUniqueResultException
     */
    public function refreshUser(UserInterface $user): Auth
    {
        return $this->loadUserByUsername($user->getUserIdentifier());
    }

    public function supportsClass(string $class): bool
    {
        return Auth::class === $class;
    }
}
