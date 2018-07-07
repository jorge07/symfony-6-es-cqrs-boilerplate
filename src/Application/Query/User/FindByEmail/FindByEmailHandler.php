<?php

namespace App\Application\Query\User\FindByEmail;

use App\Application\Query\Item;
use App\Application\Query\QueryHandlerInterface;
use App\Domain\User\Query\Repository\UserReadModelRepositoryInterface;
use App\Infrastructure\User\Query\Projections\UserView;

class FindByEmailHandler implements QueryHandlerInterface
{
    public function __invoke(FindByEmailQuery $query): Item
    {
        /** @var UserView $userView */
        $userView = $this->repository->oneByEmail($query->email);

        return new Item($userView);
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
