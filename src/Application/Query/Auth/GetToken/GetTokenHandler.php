<?php

declare(strict_types=1);

namespace App\Application\Query\Auth\GetToken;

use App\Application\Query\QueryHandlerInterface;
use App\Domain\User\Repository\GetUserCredentialsByEmailInterface;
use App\Infrastructure\User\Auth\AuthenticationProvider;

class GetTokenHandler implements QueryHandlerInterface
{
    /**
     * @throws \Assert\AssertionFailedException
     */
    public function __invoke(GetTokenQuery $query): string
    {
        [$uuid, $email, $hashedPassword] = $this->userCredentialsByEmail->getCredentialsByEmail($query->email);

        return $this->authenticationProvider->generateToken($uuid, $email, $hashedPassword);
    }

    public function __construct(
        GetUserCredentialsByEmailInterface $userCredentialsByEmail,
        AuthenticationProvider $authenticationProvider
    ) {
        $this->authenticationProvider = $authenticationProvider;
        $this->userCredentialsByEmail = $userCredentialsByEmail;
    }

    /** @var GetUserCredentialsByEmailInterface */
    private $userCredentialsByEmail;

    /** @var AuthenticationProvider */
    private $authenticationProvider;
}
