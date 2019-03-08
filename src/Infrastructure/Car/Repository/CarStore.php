<?php

declare(strict_types=1);

namespace App\Infrastructure\Car\Repository;

use App\Domain\Car\Car;
use App\Domain\Car\Repository\CarRepositoryInterface;
use App\Infrastructure\Share\Event\Store\EventStore;

class CarStore extends EventStore implements CarRepositoryInterface
{
    public function register(Car $car): void
    {
        $this->save($car);
    }

    protected static function aggregate(): string
    {
        return Car::class;
    }
}
