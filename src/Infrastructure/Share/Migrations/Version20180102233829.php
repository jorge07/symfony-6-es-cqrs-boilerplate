<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Broadway\EventStore\Dbal\DBALEventStore;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180102233829 extends AbstractMigration implements ContainerAwareInterface
{
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

    public function setContainer(ContainerInterface $container = null)
    {
        $this->eventStore = $container->get(DBALEventStore::class);
        $this->em = $container->get('doctrine.orm.entity_manager');
    }

    /** @var EntityManager */
    private $em;

    /** @var DBALEventStore */
    private $eventStore;
}
