<?php

declare(strict_types=1);

namespace App\Application\Query\Auth\GetAuthUserByEmail;

use App\Application\Query\QueryHandlerInterface;
use App\Domain\User\Repository\GetUserCredentialsByEmailInterface;
use App\Infrastructure\User\Auth\Auth;

final class GetAuthUserByEmailHandler implements QueryHandlerInterface
{
    private GetUserCredentialsByEmailInterface $userCredentialsByEmail;

    public function __construct(
        GetUserCredentialsByEmailInterface $userCredentialsByEmail
    ) {
        $this->userCredentialsByEmail = $userCredentialsByEmail;
    }

    public function __invoke(GetAuthUserByEmailQuery $query): Auth
    {
        [$uuid, $email, $hashedPassword] = $this->userCredentialsByEmail->getCredentialsByEmail($query->email);

        return Auth::create($uuid, $email, $hashedPassword);
    }
}
