<?php

declare(strict_types=1);

namespace App\Application\Query\User\FindByEmail;

use App\Infrastructure\Share\Bus\Query\Item;
use App\Infrastructure\Share\Bus\Query\QueryHandlerInterface;
use App\Infrastructure\User\Query\Mysql\MysqlUserReadModelRepository;
use App\Infrastructure\User\Query\Projections\UserView;

class FindByEmailHandler implements QueryHandlerInterface
{
    private MysqlUserReadModelRepository $repository;

    public function __construct(MysqlUserReadModelRepository $repository)
    {
        $this->repository = $repository;
    }

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
}
