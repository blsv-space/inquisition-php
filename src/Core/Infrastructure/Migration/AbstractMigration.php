<?php

namespace Inquisition\Core\Infrastructure\Migration;

use Inquisition\Core\Infrastructure\Persistence\DatabaseConnectionInterface;
use Inquisition\Core\Infrastructure\Persistence\DatabaseConnections;
use Inquisition\Core\Infrastructure\Persistence\DbDriverEnum;
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

    abstract public function getVersion(): string;

    abstract public function getDescription(): string;

    protected function execute(string $sql): void
    {
        $this->connection->connect()->exec($this->prepareSQL($sql));
    }

    protected function query(string $sql): PDOStatement
    {
        return $this->connection->connect()->query($this->prepareSQL($sql));
    }

    private function prepareSQL(string $sql): string
    {
        switch ($this->connection->getDatabaseDriver()){
            case DbDriverEnum::SQLITE:
                return $this->convertToSQLite($sql);
                default:
                    return $sql;
        }
    }

    private function convertToSQLite(string $sql): string
    {
        // Replace AUTO_INCREMENT with AUTOINCREMENT
        $sql = str_replace('AUTO_INCREMENT', 'AUTOINCREMENT', $sql);
        $sql = str_replace('auto_increment', 'AUTOINCREMENT', $sql);

        // Remove unsigned attributes (SQLite doesn't support UNSIGNED)
        $sql = preg_replace('/\s+UNSIGNED/i', '', $sql);

        // Convert ENGINE specifications (SQLite ignores these)
        $sql = preg_replace('/ENGINE\s*=\s*\w+/i', '', $sql);

        // Convert DEFAULT CHARSET (SQLite uses UTF-8 by default)
        $sql = preg_replace('/DEFAULT\s+CHARSET\s*=\s*\w+/i', '', $sql);
        $sql = preg_replace('/CHARACTER\s+SET\s+\w+/i', '', $sql);
        $sql = preg_replace('/COLLATE\s+\w+/i', '', $sql);

        // Convert data types
        $sql = preg_replace('/\bTINYINT(\(\d+\))?/i', 'INTEGER', $sql);
        $sql = preg_replace('/\bSMALLINT(\(\d+\))?/i', 'INTEGER', $sql);
        $sql = preg_replace('/\bMEDIUMINT(\(\d+\))?/i', 'INTEGER', $sql);
        $sql = preg_replace('/\bBIGINT(\(\d+\))?/i', 'INTEGER', $sql);
        $sql = preg_replace('/\bDOUBLE(\(\d+,\d+\))?/i', 'REAL', $sql);
        $sql = preg_replace('/\bFLOAT(\(\d+,\d+\))?/i', 'REAL', $sql);
        $sql = preg_replace('/\bDATETIME/i', 'TEXT', $sql);
        $sql = preg_replace('/\bTIMESTAMP/i', 'TEXT', $sql);
        $sql = preg_replace('/\bTINYTEXT/i', 'TEXT', $sql);
        $sql = preg_replace('/\bMEDIUMTEXT/i', 'TEXT', $sql);
        $sql = preg_replace('/\bLONGTEXT/i', 'TEXT', $sql);
        $sql = preg_replace('/\bTINYBLOB/i', 'BLOB', $sql);
        $sql = preg_replace('/\bMEDIUMBLOB/i', 'BLOB', $sql);
        $sql = preg_replace('/\bLONGBLOB/i', 'BLOB', $sql);

        // Convert ENUM to TEXT (SQLite doesn't support ENUM)
        $sql = preg_replace('/\bENUM\s*\([^)]+\)/i', 'TEXT', $sql);

        // Remove extra commas and clean up whitespace
        $sql = preg_replace('/,\s*\)/', ')', $sql);
        $sql = preg_replace('/\s+/', ' ', $sql);

        return trim($sql);
    }

}