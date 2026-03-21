<?php

declare(strict_types=1);

namespace Inquisition\Core\Infrastructure\Persistence;

use Inquisition\Core\Application\Persistence\DatabaseManagerInterface;
use Inquisition\Core\Infrastructure\Persistence\DatabaseManager\MySQLDatabaseManager;
use Inquisition\Core\Infrastructure\Persistence\DatabaseManager\PostgresDatabaseManager;
use Inquisition\Core\Infrastructure\Persistence\DatabaseManager\SQLiteDatabaseManager;
use Inquisition\Foundation\Singleton\SingletonInterface;
use Inquisition\Foundation\Singleton\SingletonTrait;
use InvalidArgumentException;

class DatabaseManagerFactory implements SingletonInterface
{
    use SingletonTrait;

    public function getManager(DatabaseConnection $connection): DatabaseManagerInterface
    {
        return match ($connection->getDatabaseDriver()) {
            DbDriverEnum::MYSQL => new MySQLDatabaseManager($connection),
            DbDriverEnum::PGSQL => new PostgresDatabaseManager($connection),
            DbDriverEnum::SQLITE => new SQLiteDatabaseManager($connection),
            default => throw new InvalidArgumentException('Unsupported database driver'),
        };
    }
}
