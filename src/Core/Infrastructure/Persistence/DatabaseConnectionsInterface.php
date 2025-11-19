<?php

namespace Inquisition\Core\Infrastructure\Persistence;

use Inquisition\Foundation\Singleton\SingletonInterface;

interface DatabaseConnectionsInterface extends SingletonInterface
{
    public function connect(?string $name = null): DatabaseConnection;

}
