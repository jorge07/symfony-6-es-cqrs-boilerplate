<?php

declare(strict_types=1);

namespace App\Domain\User\Auth;

use App\Domain\User\Query\Projections\UserViewInterface;

interface AuthenticationProviderInterface
{
    public function generateToken(UserViewInterface $userView): string;
}
