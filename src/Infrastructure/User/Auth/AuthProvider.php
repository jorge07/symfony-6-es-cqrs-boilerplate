<?php

declare(strict_types=1);

namespace App\Infrastructure\User\Auth;

use App\Domain\Shared\Query\Exception\NotFoundException;
use App\Domain\User\ValueObject\Email;
use App\Infrastructure\User\Query\Mysql\MysqlReadModelUserRepository;
use Assert\AssertionFailedException;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class AuthProvider implements UserProviderInterface
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
