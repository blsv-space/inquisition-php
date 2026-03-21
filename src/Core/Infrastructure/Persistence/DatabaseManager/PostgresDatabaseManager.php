<?php

declare(strict_types=1);

namespace Inquisition\Core\Infrastructure\Persistence\DatabaseManager;

use Inquisition\Core\Application\Persistence\DatabaseManagerInterface;
use Inquisition\Core\Infrastructure\Persistence\DatabaseConnection;

final readonly class PostgresDatabaseManager implements DatabaseManagerInterface
{
    public function __construct(private DatabaseConnection $connection) {}

    #[\Override]
    public function create(): void
    {
        $sql = sprintf(
            'CREATE DATABASE "%s" ENCODING = \'UTF8\' TEMPLATE template0',
            str_replace('"', '""', $this->connection->getDatabaseName()),
        );

        $this->connection->connect()->exec($sql);
    }

    #[\Override]
    public function exists(): bool
    {
        $stmt = $this->connection->connect()
            ->prepare('SELECT 1 FROM pg_database WHERE datname = :name');
        $stmt->execute(['name' => $this->connection->getDatabaseName()]);
        return (bool) $stmt->fetchColumn();
    }

    #[\Override]
    public function reset(): void
    {
        if ($this->exists()) {
            $connect = $this->connection->connect();
            $connect->exec(
                sprintf(
                    'SELECT pg_terminate_backend(pid) FROM pg_stat_activity WHERE datname = \'%s\'',
                    str_replace("'", "''", $this->connection->getDatabaseName()),
                ),
            );
            $connect->exec(
                sprintf(
                    'DROP DATABASE "%s"',
                    str_replace('"', '""', $this->connection->getDatabaseName()),
                ),
            );
        }
        $this->create();
    }
}
