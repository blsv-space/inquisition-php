<?php

namespace Inquisition\Core\Infrastructure\Persistence;

use Inquisition\Core\Infrastructure\Persistence\Exception\InvalidConnectionConfig;
use Inquisition\Core\Infrastructure\Persistence\Exception\PersistenceException;
use Inquisition\Foundation\Config\Config;
use Inquisition\Foundation\Singleton\SingletonTrait;

final class DatabaseConnections implements DatabaseConnectionsInterface
{
    use SingletonTrait;

    /**
     * @var DatabaseConnection[]
     */
    private array  $connections;
    private Config $config;
    private string $defaultConnectionName;

    private function __construct()
    {
        $this->config = Config::getInstance();
    }

    /**
     * @param string|null $name
     *
     * @return DatabaseConnection
     * @throws PersistenceException
     */
    public function connect(?string $name = null): DatabaseConnection
    {
        if (is_null($name)) {
            $name = $this->getDefaultConnectionName();
        }

        return $this->getConnection($name);
    }

    /**
     * @param $name
     *
     * @return DatabaseConnection
     * @throws PersistenceException
     */
    private function getConnection($name): DatabaseConnection
    {
        if (!isset($this->connections[$name])) {
            $this->loadConnection($name);
        }

        return $this->connections[$name];
    }

    /**
     * @return string
     * @throws PersistenceException
     */
    public function getDefaultConnectionName(): string
    {
        if (!isset($this->defaultConnectionName)) {
            $this->loadDefaultConnection();
        }

        return $this->defaultConnectionName;
    }

    /**
     * @throws PersistenceException
     */
    private function loadDefaultConnection(): void
    {
        $name = $name ?? $this->config->getByPath('database.default');

        if (is_null($name)) {
            throw new PersistenceException('No database connection name specified');
        }

        $this->defaultConnectionName = $name;
    }

    /**
     * @param $name
     *
     * @return void
     * @throws PersistenceException
     */
    private function loadConnection($name = null): void
    {
        $connectionConfig = $this->config->getByPath("database.connections.$name");
        if (!is_array($connectionConfig)) {
            throw new PersistenceException("Database connection '$name' not found in database.connections.");
        }

        $this->validateConnectionSettings($name, $connectionConfig);

        $this->connections[$name] = new DatabaseConnection(
            driver     : $connectionConfig['driver'],
            database   : $connectionConfig['database'],
            host       : $connectionConfig['host'] ?? null,
            unix_socket: $connectionConfig['unix_socket'] ?? null,
            port       : $connectionConfig['port'] ?? null,
            charset    : $connectionConfig['charset'] ?? null,
            username   : $connectionConfig['username'] ?? null,
            password   : $connectionConfig['password'] ?? null,
            options    : $connectionConfig['options'] ?? null,
        );
    }

    /**
     * @param string $name
     * @param array  $connectionConfig
     *
     * @return void
     * @throws InvalidConnectionConfig
     */
    private function validateConnectionSettings(string $name, array $connectionConfig): void
    {
        if (!isset($connectionConfig['driver'])) {
            throw new InvalidConnectionConfig($name, 'No database driver specified');
        }

        if (!isset($connectionConfig['host']) || !isset($connectionConfig['unix_socket'])) {
            throw new InvalidConnectionConfig($name, 'No database host or unix_socket specified');
        } elseif (isset($connectionConfig['host'], $connectionConfig['unix_socket'])) {
            throw new InvalidConnectionConfig($name, 'Both host and unix_socket specified');
        }

        if (!isset($connectionConfig['database'])) {
            throw new InvalidConnectionConfig($name, 'No database name specified');
        }
    }

}