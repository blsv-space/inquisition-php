<?php

namespace Inquisition\Core\Infrastructure\Persistence;

use Inquisition\Foundation\Singleton\SingletonInterface;
use PDO;

interface DatabaseConnectionsInterface extends SingletonInterface
{
    public function connect(?string $name = null): DatabaseConnection;

}
