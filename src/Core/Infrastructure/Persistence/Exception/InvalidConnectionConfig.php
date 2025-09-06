<?php

namespace Inquisition\Core\Infrastructure\Persistence\Exception;

use Inquisition\Core\Infrastructure\Persistence\Exception\PersistenceException;

class InvalidConnectionConfig extends PersistenceException
{
    public function __construct(string $name, string $message)
    {
        parent::__construct("Invalid connection $name config: $message");
    }
}