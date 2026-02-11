<?php

declare(strict_types=1);

namespace Inquisition\Core\Infrastructure\Persistence\Exception;

class InvalidConnectionConfig extends PersistenceException
{
    public function __construct(string $name, string $message)
    {
        parent::__construct("Invalid connection '$name' config: $message");
    }
}
