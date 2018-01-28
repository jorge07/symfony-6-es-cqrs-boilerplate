<?php

namespace App\Application\Query\User\FindByEmail;

use App\Application\Query\QueryHandlerInterface;
use App\Domain\User\Query\UserRead;
use App\Domain\User\Query\UserReadModelRepositoryInterface;

class FindByEmailHandler implements QueryHandlerInterface
{

    public function __invoke(FindByEmailQuery $query): UserRead
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
