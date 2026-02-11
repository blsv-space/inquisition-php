<?php

declare(strict_types=1);

namespace Inquisition\Core\Infrastructure\Persistence\Exception;

use PDOException;

class UnableToEstablishConnection extends PersistenceException
{
    public function __construct(string $connection, PDOException $exception)
    {
        parent::__construct("Unable to establish connection to '$connection'", $exception->getCode(), $exception);
    }
}
