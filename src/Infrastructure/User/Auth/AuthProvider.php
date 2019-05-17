<?php

declare(strict_types=1);

namespace App\Infrastructure\User\Auth;

use App\Domain\User\ValueObject\Email;
use App\Infrastructure\User\Query\Mysql\MysqlUserReadModelRepository;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class AuthProvider implements UserProviderInterface
{
    /**
     * @param string $email
     *
     * @throws \App\Domain\Shared\Query\Exception\NotFoundException
     * @throws \Assert\AssertionFailedException
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @return Auth|UserInterface
     */
    public function loadUserByUsername($email)
    {
        // @var array $user
        list($uuid, $email, $hashedPassword) = $this->userReadModelRepository->getCredentialsByEmail(
            Email::fromString($email)
        );

        return Auth::create($uuid, $email, $hashedPassword);
    }

    /**
     * @throws \App\Domain\Shared\Query\Exception\NotFoundException
     * @throws \Assert\AssertionFailedException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class): bool
    {
        return Auth::class === $class;
    }

    public function __construct(MysqlUserReadModelRepository $userReadModelRepository)
    {
        $this->userReadModelRepository = $userReadModelRepository;
    }

    /**
     * @var MysqlUserReadModelRepository
     */
    private $userReadModelRepository;
}
