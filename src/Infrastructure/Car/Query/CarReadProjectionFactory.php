<?php

declare(strict_types=1);

namespace App\Infrastructure\Car\Query;

use App\Domain\Car\Event\CarWasAcquired;
use App\Infrastructure\Car\Query\Projections\CarView;
use App\Infrastructure\Car\Repository\MysqlCarRepository;
use App\Infrastructure\User\Query\Projections\UserView;
use Broadway\ReadModel\Projector;

class CarReadProjectionFactory extends Projector
{
    protected function applyCarWasAcquired(CarWasAcquired $event): void
    {
        $car = CarView::fromSerializable($event);

        $car->owner = $this->repository->createReference(UserView::class, $event->owner);

        $this->repository->register($car);
    }

    public function __construct(MysqlCarRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @var MysqlCarRepository
     */
    private $repository;
}
