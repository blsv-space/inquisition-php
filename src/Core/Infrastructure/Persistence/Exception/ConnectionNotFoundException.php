<?php

namespace Inquisition\Core\Infrastructure\Persistence\Exception;

use Inquisition\Core\Infrastructure\Persistence\Exception\PersistenceException;

class ConnectionNotFoundException extends PersistenceException
{
    public function __construct(string $connection)
    {
        parent::__construct("Connection '{$connection}' not found");
    }
}