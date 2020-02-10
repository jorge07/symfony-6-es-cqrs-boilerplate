<?php

declare(strict_types=1);

namespace App\Application\Query\User\FindByEmail;

use App\Application\Query\Item;
use App\Application\Query\QueryHandlerInterface;
use App\Infrastructure\User\Query\Mysql\MysqlUserReadModelRepository;
use App\Infrastructure\User\Query\Projections\UserView;

class FindByEmailHandler implements QueryHandlerInterface
{
    /**
     * @throws \App\Domain\Shared\Query\Exception\NotFoundException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function __invoke(FindByEmailQuery $query): Item
    {
        /** @var UserView $userView */
        $userView = $this->repository->oneByEmail($query->email);

        return new Item($userView);
    }

    public function __construct(MysqlUserReadModelRepository $repository)
    {
        $this->repository = $repository;
    }

    /** @var MysqlUserReadModelRepository */
    private $repository;
}
