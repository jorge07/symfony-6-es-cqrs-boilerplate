<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Persistence\Doctrine\MigrationsFactory;

use Doctrine\DBAL\Connection;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\Migrations\Version\MigrationFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ContainerAwareFactory
 *
 * @desciption If you know a better way to do that let me know because I've lost an important amount of time...
 */
final class ContainerAwareFactory implements MigrationFactory
{
    private ?ContainerInterface $container;

    private Connection $connection;

    private LoggerInterface $logger;

    public function __construct(Connection $connection, LoggerInterface $logger, ContainerInterface $container)
    {
        $this->connection = $connection;
        $this->logger = $logger;
        $this->container = $container;
    }

    public function createVersion(string $migrationClassName): AbstractMigration
    {
        $instance = new $migrationClassName(
            $this->connection,
            $this->logger
        );

        if ($instance instanceof ContainerAwareInterface) {
            $instance->setContainer($this->container);
        }

        return $instance;
    }
}
