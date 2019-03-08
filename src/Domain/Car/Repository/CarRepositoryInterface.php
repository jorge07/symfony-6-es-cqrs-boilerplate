<?php

declare(strict_types=1);

namespace App\Domain\Car\Repository;

use App\Domain\Car\Car;

interface CarRepositoryInterface
{
    public function register(Car $car): void;
}
