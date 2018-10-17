<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181017093525 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE users CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:App\\\\Domain\\\\User\\\\ValueObject\\\\Uuid)\', CHANGE credentials_email credentials_email VARCHAR(255) NOT NULL COMMENT \'(DC2Type:App\\\\Domain\\\\User\\\\ValueObject\\\\Email)\', CHANGE credentials_password credentials_password VARCHAR(255) NOT NULL COMMENT \'(DC2Type:App\\\\Domain\\\\User\\\\ValueObject\\\\Auth\\\\HashedPassword)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE users CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\', CHANGE credentials_email credentials_email VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE credentials_password credentials_password VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
    }
}
