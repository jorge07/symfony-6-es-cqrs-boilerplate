<?php

declare(strict_types=1);

namespace App\Application\Query\User\FindByEmail;

use App\Application\Query\Item;
use App\Application\Query\QueryHandlerInterface;
use App\Domain\Shared\Query\Exception\NotFoundException;
use App\Infrastructure\User\Query\Mysql\MysqlReadModelUserRepository;
use App\Infrastructure\User\Query\Projections\UserView;
use Doctrine\ORM\NonUniqueResultException;

class FindByEmailHandler implements QueryHandlerInterface
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
