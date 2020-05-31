<?php

declare(strict_types=1);

namespace App\Application\Query\User\FindByEmail;

use App\Infrastructure\Share\Bus\Query\Item;
use App\Infrastructure\Share\Bus\Query\ItemFactory;
use App\Infrastructure\Share\Bus\Query\QueryHandlerInterface;
use App\Infrastructure\User\Query\Mysql\MysqlUserReadModelRepository;

class FindByEmailHandler implements QueryHandlerInterface
{
    private MysqlUserReadModelRepository $repository;

    private ItemFactory $factory;

    public function __construct(MysqlUserReadModelRepository $repository, ItemFactory $factory)
    {
        $this->repository = $repository;
        $this->factory = $factory;
    }

    /**
     * @throws \App\Domain\Shared\Query\Exception\NotFoundException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function __invoke(FindByEmailQuery $query): Item
    {
        $userView = $this->repository->oneByEmail($query->email);

        return $this->factory->create(
            $userView,
            [],
            ['groups' => ['user_view', 'credentials']]
        );
    }
}
