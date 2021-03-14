<?php

declare(strict_types=1);

namespace App\User\Infrastructure\ReadModel\Projections;

use App\Shared\Domain\Exception\DateTimeException;
use App\User\Domain\Event\UserEmailChanged;
use App\User\Domain\Event\UserWasCreated;
use App\Shared\Infrastructure\Persistence\ReadModel\Exception\NotFoundException;
use App\User\Infrastructure\ReadModel\Mysql\MysqlReadModelUserRepository;
use App\User\Infrastructure\ReadModel\UserView;
use Assert\AssertionFailedException;
use Broadway\ReadModel\Projector;
use Doctrine\ORM\NonUniqueResultException;

final class UserProjectionFactory extends Projector
{
    private MysqlReadModelUserRepository $repository;

    public function __construct(MysqlReadModelUserRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @throws AssertionFailedException
     * @throws DateTimeException
     */
    protected function applyUserWasCreated(UserWasCreated $userWasCreated): void
    {
        $userReadModel = UserView::fromSerializable($userWasCreated);

        $this->repository->add($userReadModel);
    }

    /**
     * @throws NotFoundException
     * @throws NonUniqueResultException
     */
    protected function applyUserEmailChanged(UserEmailChanged $emailChanged): void
    {
        $userReadModel = $this->repository->oneByUuid($emailChanged->uuid);

        $userReadModel->changeEmail($emailChanged->email);
        $userReadModel->changeUpdatedAt($emailChanged->updatedAt);

        $this->repository->apply();
    }
}
