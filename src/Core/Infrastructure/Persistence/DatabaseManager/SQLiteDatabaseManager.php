<?php

namespace Inquisition\Core\Infrastructure\Persistence\DatabaseManager;

use Inquisition\Core\Application\Persistence\DatabaseManagerInterface;
use Inquisition\Core\Infrastructure\Persistence\DatabaseConnection;
use Inquisition\Foundation\Kernel;

final readonly class SQLiteDatabaseManager implements DatabaseManagerInterface
{

    public function __construct(private DatabaseConnection $connection)
    {}

    /**
     * @return void
     */
    public function create(): void
    {
        $this->connection->connect()->exec('PRAGMA journal_mode = WAL;');
    }

    /**
     * @return bool
     */
    public function exists(): bool
    {
        if ($this->connection->getDatabaseName() === ':memory:') {

            return true;
        }

        return file_exists($this->connection->getDatabaseName());
    }

    /**
     * @return void
     */
    public function reset(): void
    {
        $path = $this->connection->getDatabaseName();

        if ($path === ':memory:') {
            $this->connection->connect(true);
            $this->create();

            return;
        }

        if (!str_starts_with($path, '/')) {
            $path = Kernel::getInstance()->projectRoot . '/' . $path;
        }

        if (file_exists($path)) {
            unlink($path);
        }

        $this->create();
    }
}