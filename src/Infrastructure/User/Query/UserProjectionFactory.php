<?php

declare(strict_types=1);

namespace App\Infrastructure\User\Query;

use App\Domain\User\Event\UserEmailChanged;
use App\Domain\User\Event\UserWasCreated;
use App\Infrastructure\User\Query\Mysql\MysqlUserReadModelRepository;
use App\Infrastructure\User\Query\Projections\UserView;
use Broadway\ReadModel\Projector;

class UserProjectionFactory extends Projector
{
    /**
     * @throws \Assert\AssertionFailedException
     */
    protected function applyUserWasCreated(UserWasCreated $userWasCreated): void
    {
        $userReadModel = UserView::fromSerializable($userWasCreated);

        $this->repository->add($userReadModel);
    }

    /**
     * @throws \App\Domain\Shared\Query\Exception\NotFoundException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function applyUserEmailChanged(UserEmailChanged $emailChanged): void
    {
        /** @var UserView $userReadModel */
        $userReadModel = $this->repository->oneByUuid($emailChanged->uuid);

        $userReadModel->changeEmail($emailChanged->email);
        $userReadModel->changeUpdatedAt($emailChanged->updatedAt);

        $this->repository->apply();
    }

    public function __construct(MysqlUserReadModelRepository $repository)
    {
        $this->repository = $repository;
    }

    /** @var MysqlUserReadModelRepository */
    private $repository;
}
