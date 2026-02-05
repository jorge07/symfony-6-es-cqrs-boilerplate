<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Persistence\Doctrine\MigrationsFactory;

use Broadway\EventStore\Dbal\DBALEventStore;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Interface for migrations that need access to services.
 * This replaces the deprecated ContainerAwareInterface pattern from Symfony 6.x.
 */
interface ServiceAwareMigrationInterface
{
    public function setServices(EntityManagerInterface $em, DBALEventStore $eventStore): void;
}
