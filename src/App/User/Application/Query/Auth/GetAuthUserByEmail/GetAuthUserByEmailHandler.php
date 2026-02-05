<?php

declare(strict_types=1);

namespace App\User\Application\Query\Auth\GetAuthUserByEmail;

use App\Shared\Application\Query\QueryHandlerInterface;
use App\User\Domain\Repository\GetUserCredentialsByEmailInterface;
use App\User\Infrastructure\Auth\Auth;

final class GetAuthUserByEmailHandler implements QueryHandlerInterface
{
    public function __construct(private readonly GetUserCredentialsByEmailInterface $userCredentialsByEmail)
    {
    }

    public function __invoke(GetAuthUserByEmailQuery $query): Auth
    {
        $credentials = $this->userCredentialsByEmail->getCredentialsByEmail($query->email);

        return Auth::create($credentials->uuid, $credentials->email, $credentials->password);
    }
}
