<?php

declare(strict_types=1);

namespace App\User\Application\Query\Auth\GetToken;

use App\Shared\Application\Query\QueryHandlerInterface;
use App\User\Application\Query\Auth\AuthenticationProviderInterface;
use App\User\Domain\Repository\GetUserCredentialsByEmailInterface;

final class GetTokenHandler implements QueryHandlerInterface
{
    public function __construct(private readonly GetUserCredentialsByEmailInterface $userCredentialsByEmail, private readonly AuthenticationProviderInterface $authenticationProvider)
    {
    }

    public function __invoke(GetTokenQuery $query): string
    {
        $credentials = $this->userCredentialsByEmail->getCredentialsByEmail($query->email);

        return $this->authenticationProvider->generateToken($credentials->uuid, $credentials->email, $credentials->password);
    }
}
