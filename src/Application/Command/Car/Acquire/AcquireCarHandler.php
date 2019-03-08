<?php

declare(strict_types=1);

namespace App\Application\Command\Car\Acquire;

use App\Application\Command\CommandHandlerInterface;
use App\Domain\Car\Car;
use App\Domain\Car\Repository\CarRepositoryInterface;

class AcquireCarHandler implements CommandHandlerInterface
{
    public function __invoke(AcquireCarCommand $command)
    {
        $car = Car::create($command->uuid, $command->owner, $command->date);

        $this->repository->register($car);
    }

    public function __construct(CarRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @var CarRepositoryInterface
     */
    private $repository;
}
