<?php

namespace Inquisition\Core\Infrastructure\Persistence;

use PDO;

class DatabaseConnection implements DatabaseConnectionInterface
{
    private ?PDO $connection = null;
    private ?string $dsn = null;

    public function __construct(
        private readonly DbDriverEnum $driver,
        private readonly string       $database,
        private readonly ?string      $host,
        private readonly ?string      $unix_socket,
        private readonly ?int         $port = null,
        private readonly ?string      $charset = null,
        private readonly ?string      $username = null,
        private readonly ?string      $password = null,
        private readonly ?array       $options = null,
    )
    {
    }

    /**
     * @param bool $reconnect
     *
     * @return PDO
     */
    public function connect(bool $reconnect = false): PDO
    {
        if (is_null($this->connection) || $reconnect) {
            $this->connection = new PDO($this->getDsn(), $this->username, $this->password, $this->getOptions());
        }

        return $this->connection;
    }

    /**
     * @return array
     */
    private function getOptions(): array
    {
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];
        if (!is_null($this->options)) {
            $options = array_merge($options, $this->options);
        }

        return $options;
    }

    /**
     * @return string
     */
    private function getDsn(): string
    {
        if (is_null($this->dsn)) {
            $this->dsn = $this->driver->dsn();

            if ($this->driver === DbDriverEnum::SQLITE) {
                $this->dsn .= $this->database;

                return $this->dsn;
            }

            if ($this->unix_socket) {
                $this->dsn .= "unix_socket=$this->unix_socket";
            } elseif ($this->host) {
                $this->dsn .= "host=$this->host";
            }

            $this->dsn .= ";dbname=$this->database";

            if (!is_null($this->port)) {
                $this->dsn .= ';port=' . $this->port;
            }

            if (!is_null($this->charset)) {
                $this->dsn .= ';charset=' . $this->charset;
            }
        }

        return $this->dsn;
    }

    /**
     * @return void
     */
    public function beginTransaction(): void
    {
        $this->connection->beginTransaction();
    }

    /**
     * @return void
     */
    public function commit(): void
    {
        $this->connection->commit();
    }

    /**
     * @return void
     */
    public function rollback(): void
    {
        $this->connection->rollback();
    }

    /**
     * @return string
     */
    public function getDatabaseName(): string
    {
        return $this->database;
    }

    /**
     * @return DbDriverEnum
     */
    public function getDatabaseDriver(): DbDriverEnum
    {
        return $this->driver;
    }

}