<?php

declare(strict_types=1);

namespace App\Infrastructure\User\Query;

use App\Domain\User\Event\UserEmailChanged;
use App\Domain\User\Event\UserWasCreated;
use App\Domain\User\Query\UserView;
use App\Domain\User\Query\Repository\UserReadModelRepositoryInterface;
use Broadway\ReadModel\Projector;

class UserReadProjectionFactory extends Projector
{
    protected function applyUserWasCreated(UserWasCreated $userWasCreated): void
    {
        $userReadModel = UserView::fromSerializable($userWasCreated);

        $this->repository->add($userReadModel);
    }

    protected function applyUserEmailChanged(UserEmailChanged $emailChanged): void
    {
        $userReadModel = $this->repository->oneByUuid($emailChanged->uuid);

        $userReadModel->email = $emailChanged->email->toString();

        $this->repository->apply();
    }

    public function __construct(UserReadModelRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /** @var UserReadModelRepositoryInterface */
    private $repository;
}
