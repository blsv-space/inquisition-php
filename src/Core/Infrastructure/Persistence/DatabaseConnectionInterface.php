<?php

namespace Inquisition\Core\Infrastructure\Persistence;

use PDO;

interface DatabaseConnectionInterface
{
    public function connect(bool $reconnect = false): PDO;

    /**
     * Begin a database transaction
     */
    public function beginTransaction(): void;

    /**
     * Commit the current transaction
     */
    public function commit(): void;

    /**
     * Rollback the current transaction
     */
    public function rollback(): void;

    public function getDatabaseName(): string;
    public function getDatabaseDriver(): DbDriverEnum;
}
