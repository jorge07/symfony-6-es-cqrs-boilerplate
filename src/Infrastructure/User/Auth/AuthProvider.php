<?php

declare(strict_types=1);

namespace App\Infrastructure\User\Auth;

use App\Domain\User\Query\Repository\UserReadModelRepositoryInterface;
use App\Domain\User\ValueObject\Email;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class AuthProvider implements UserProviderInterface
{
    public function loadUserByUsername($email)
    {
        $user = $this->userReadModelRepository->oneByEmail(Email::fromString($email));

        return Auth::fromUser($user);
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class)
    {
        return Auth::class;
    }

    public function __construct(UserReadModelRepositoryInterface $userReadModelRepository)
    {
        $this->userReadModelRepository = $userReadModelRepository;
    }

    /**
     * @var UserReadModelRepositoryInterface
     */
    private $userReadModelRepository;
}
