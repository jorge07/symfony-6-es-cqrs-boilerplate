<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Persistence\Doctrine\MigrationsFactory;

use Broadway\EventStore\Dbal\DBALEventStore;
use Doctrine\DBAL\Connection;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\Migrations\Version\MigrationFactory;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Custom migration factory that injects services into migrations that need them.
 * This replaces the deprecated ContainerAwareInterface pattern.
 */
final class ContainerAwareFactory implements MigrationFactory
{
    public function __construct(
        private readonly Connection $connection,
        private readonly LoggerInterface $logger,
        private readonly EntityManagerInterface $entityManager,
        private readonly DBALEventStore $eventStore,
    ) {
    }

    public function createVersion(string $migrationClassName): AbstractMigration
    {
        $instance = new $migrationClassName(
            $this->connection,
            $this->logger
        );

        if ($instance instanceof ServiceAwareMigrationInterface) {
            $instance->setServices($this->entityManager, $this->eventStore);
        }

        return $instance;
    }
}
