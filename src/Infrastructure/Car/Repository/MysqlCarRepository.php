<?php

declare(strict_types=1);

namespace App\Infrastructure\Car\Repository;

use App\Infrastructure\Car\Query\Projections\CarView;
use App\Infrastructure\Share\Query\Repository\MysqlRepository;
use Doctrine\ORM\EntityManagerInterface;

class MysqlCarRepository extends MysqlRepository
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->class = CarView::class;
        parent::__construct($entityManager);
    }
}
