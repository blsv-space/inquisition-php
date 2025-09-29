<?php

namespace Inquisition\Core\Infrastructure\Persistence;

enum DbDriverEnum: string
{
    case CUBRID = 'cubrid';
    case DBLIB = 'dblib';
    case FIREBASE = 'firebase';
    case IBM = 'ibm';
    case INFORMIX = 'informmix';
    case MYSQL = 'mysql';
    case OCI = 'oci';
    case ODBC = 'odbc';
    case PGSQL = 'pgsql';
    case SQLITE = 'sqlite';
    case SQLSRV = 'sqlsrv';

    public static function isSupported(string $driver): bool
    {
        return in_array($driver, self::cases());
    }

    public function dsn(): string
    {
        return "$this->value:";
    }
}
