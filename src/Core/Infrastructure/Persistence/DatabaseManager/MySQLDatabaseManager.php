<?php

namespace Inquisition\Core\Infrastructure\Persistence\DatabaseManager;

use Inquisition\Core\Application\Persistence\DatabaseManagerInterface;
use Inquisition\Core\Infrastructure\Persistence\DatabaseConnection;
use PDO;

final readonly class MySQLDatabaseManager implements DatabaseManagerInterface
{
    public function __construct(private DatabaseConnection $connection) {}

    /**
     * @return void
     */
    public function create(): void
    {
        $name = $this->connection->getDatabaseName();
        $this->connection->connect()
            ->exec("CREATE DATABASE `$name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    }

    /**
     * @return bool
     */
    public function exists(): bool
    {
        $stmt = $this->connection->connect()->prepare("SHOW DATABASES LIKE :name");
        $stmt->execute(['name' => $this->connection->getDatabaseName()]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * @return void
     */
    public function reset(): void
    {
        if ($this->exists()) {
            $this->connection->connect()->exec("DROP DATABASE `{$this->connection->getDatabaseName()}`");
        }
        $this->create();
    }
}