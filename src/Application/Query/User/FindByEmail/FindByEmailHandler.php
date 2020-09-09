<?php

declare(strict_types=1);

namespace App\Application\Query\User\FindByEmail;

use App\Application\Query\Item;
use App\Application\Query\QueryHandlerInterface;
use App\Infrastructure\Shared\Persistence\ReadModel\Exception\NotFoundException;
use App\Infrastructure\User\ReadModel\Mysql\MysqlReadModelUserRepository;
use App\Infrastructure\User\ReadModel\UserView;
use Doctrine\ORM\NonUniqueResultException;

final class FindByEmailHandler implements QueryHandlerInterface
{
    private MysqlReadModelUserRepository $repository;

    public function __construct(MysqlReadModelUserRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @throws NotFoundException
     * @throws NonUniqueResultException
     */
    public function __invoke(FindByEmailQuery $query): Item
    {
        $userView = $this->repository->oneByEmailAsArray($query->email);

        return Item::fromPayload($userView['uuid']->toString(), UserView::TYPE, $userView);
    }
}
