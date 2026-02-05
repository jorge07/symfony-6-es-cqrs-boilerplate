<?php

declare(strict_types=1);

namespace App\User\Application\Query\User\FindByEmail;

use App\Shared\Application\Query\Item;
use App\Shared\Application\Query\QueryHandlerInterface;
use App\Shared\Domain\Exception\NotFoundException;
use App\User\Domain\Repository\UserReadModelRepositoryInterface;
use App\User\Infrastructure\ReadModel\UserView;
use Doctrine\ORM\NonUniqueResultException;

final class FindByEmailHandler implements QueryHandlerInterface
{
    public function __construct(private readonly UserReadModelRepositoryInterface $repository)
    {
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
