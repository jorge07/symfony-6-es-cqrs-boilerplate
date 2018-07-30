<?php

declare(strict_types=1);

namespace App\Application\Query\Auth\GetToken;

use App\Application\Query\QueryHandlerInterface;
use App\Domain\User\Auth\AuthenticationProviderInterface;
use App\Domain\User\Query\Repository\UserReadModelRepositoryInterface;

class GetTokenHandler implements QueryHandlerInterface
{
    public function __invoke(GetTokenQuery $query)
    {
        $userView = $this->readModelRepository->oneByEmail($query->email);

        return $this->authenticationProvider->generateToken($userView);
    }

    public function __construct(
        UserReadModelRepositoryInterface $readModelRepository,
        AuthenticationProviderInterface $authenticationProvider
    ) {
        $this->readModelRepository = $readModelRepository;
        $this->authenticationProvider = $authenticationProvider;
    }

    /**
     * @var UserReadModelRepositoryInterface
     */
    private $readModelRepository;

    /**
     * @var AuthenticationProviderInterface
     */
    private $authenticationProvider;
}
