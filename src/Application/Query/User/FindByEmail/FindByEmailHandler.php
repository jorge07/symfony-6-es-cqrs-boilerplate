<?php

namespace App\Application\Query\User\FindByEmail;

use App\Application\Query\QueryHandlerInterface;
use App\Domain\User\Query\Repository\UserReadModelRepositoryInterface;
use App\Domain\User\Query\UserView;

class FindByEmailHandler implements QueryHandlerInterface
{
    public function __invoke(FindByEmailQuery $query): UserView
    {
        return $this->repository->oneByEmail($query->email);
    }

    public function __construct(UserReadModelRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @var UserReadModelRepositoryInterface
     */
    private $repository;
}
