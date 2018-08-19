<?php

namespace App\Infrastructure\Share\Event\Repository;

use App\Domain\Shared\Event\Repository\IterableAggregateEventStoreInterface;
use Broadway\Domain\DomainEventStream;
use Broadway\EventStore\Dbal\DBALEventStore;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Statement;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class IterableDbalEventStore implements IterableAggregateEventStoreInterface
{
    /**
     * @throws DBALException
     */
    public function current()
    {
        if (!$this->isStatementPrepared()) {
            $this->initializeIterator();
        }

        return $this->nextAggregate;
    }

    /**
     * @throws DBALException
     */
    public function next()
    {
        if (!$this->isStatementPrepared()) {
            $this->initializeIterator();
        }

        $uuid = $this->fetchNextAggregateId();

        $this->nextAggregate = $this->eventStore->load($uuid);
    }

    public function key()
    {
        return $this->index;
    }

    public function valid()
    {
        return !$this->isStatementPrepared() || $this->nextAggregate instanceof DomainEventStream;
    }

    public function rewind()
    {
        $this->statement = null;
    }

    /**
     * @throws DBALException
     */
    private function initializeIterator(): void
    {
        $this->prepareStatement();
        $this->resetIndex();
        $this->next();
    }

    private function isStatementPrepared(): bool
    {
        return $this->statement instanceof Statement;
    }

    /**
     * @throws DBALException
     */
    private function prepareStatement()
    {
        $this->statement = $this->connection->prepare(
            'SELECT DISTINCT `uuid` FROM ' . $this->eventStoreTable
        );
        $this->statement->execute();
    }

    private function resetIndex(): void
    {
        $this->index = -1;
    }

    private function fetchNextAggregateId(): ?UuidInterface
    {
        $nextAggregate = $this->statement->fetch();

        if (is_array($nextAggregate) && array_key_exists('uuid', $nextAggregate)) {
            return Uuid::fromBytes($nextAggregate['uuid']);
        }

        return null;
    }

    public function __construct(
        Connection $connection,
        DBALEventStore $eventStore,
        string $eventStoreTable
    ) {
        $this->connection = $connection;
        $this->eventStore = $eventStore;
        $this->eventStoreTable = $eventStoreTable;
        $this->resetIndex();
    }

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var DBALEventStore
     */
    private $eventStore;

    /**
     * @var string
     */
    private $eventStoreTable;

    /**
     * @var int
     */
    private $index;

    /**
     * @var DomainEventStream|null
     */
    private $nextAggregate;

    /**
     * @var Statement|null
     */
    private $statement;
}
