<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200215222048 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Event stream';
    }

    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE event_stream (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\', event VARCHAR(255) NOT NULL, aggregate_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\', payload JSON NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', version INT NOT NULL, UNIQUE INDEX aggregate_id_version_unique (aggregate_id, version), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE event_stream');
    }
}
