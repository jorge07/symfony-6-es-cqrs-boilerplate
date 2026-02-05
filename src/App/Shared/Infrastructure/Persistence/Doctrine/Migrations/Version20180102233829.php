<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Persistence\Doctrine\Migrations;

use App\Shared\Infrastructure\Persistence\Doctrine\MigrationsFactory\ServiceAwareMigrationInterface;
use Broadway\EventStore\Dbal\DBALEventStore;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class Version20180102233829 extends AbstractMigration implements ServiceAwareMigrationInterface
{
    private EntityManagerInterface $em;

    private DBALEventStore $eventStore;

    public function setServices(EntityManagerInterface $em, DBALEventStore $eventStore): void
    {
        $this->em = $em;
        $this->eventStore = $eventStore;
    }

    public function up(Schema $schema): void
    {
        $this->eventStore->configureSchema($schema);

        $this->em->flush();
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('api.events');

        $this->em->flush();
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
