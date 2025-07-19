<?php

namespace Inquisition\Core\Infrastructure\Migration;

use Inquisition\Core\Infrastructure\Persistence\DatabaseConnectionInterface;
use Inquisition\Core\Infrastructure\Persistence\DatabaseConnections;
use Inquisition\Core\Infrastructure\Persistence\Exception\PersistenceException;
use PDOStatement;

abstract readonly class AbstractMigration implements MigrationInterface
{
    protected const string DATABASE_NAME = 'default';

    protected DatabaseConnectionInterface $connection;

    /**
     * @throws PersistenceException
     */
    public function __construct()
    {
        MigrationRunner::getInstance()->registerMigration($this);
        $this->connection = DatabaseConnections::getInstance()->connect(static::DATABASE_NAME);
    }

    abstract public function up(): void;

    public function down(): void
    {
    }

    abstract public function getVersion(): string;

    abstract public function getDescription(): string;

    protected function execute(string $sql): void
    {
        $this->connection->connect()->exec($sql);
    }

    protected function query(string $sql): PDOStatement
    {
        return $this->connection->connect()->query($sql);
    }

}