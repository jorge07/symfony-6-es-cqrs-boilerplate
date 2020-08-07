<?php

declare(strict_types=1);

namespace App\Infrastructure\User\Query;

use App\Domain\Shared\Exception\DateTimeException;
use App\Domain\Shared\Query\Exception\NotFoundException;
use App\Domain\User\Event\UserEmailChanged;
use App\Domain\User\Event\UserWasCreated;
use App\Infrastructure\User\Query\Mysql\MysqlReadModelUserRepository;
use App\Infrastructure\User\Query\Projections\UserView;
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
