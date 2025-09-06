<?php

namespace Inquisition\Core\Infrastructure\Persistence\Exception;

class ConnectionNotFoundException extends PersistenceException
{
    public function __construct(string $connection)
    {
        parent::__construct("Connection '$connection' not found");
    }
}