<?php

namespace Inquisition\Core\Infrastructure\Http\Router\Exception;

use Inquisition\Core\Infrastructure\Http\Router\Exception\RouterException;

class DuplicateRouteException extends RouterException
{
    public function __construct(string $name)
    {
        parent::__construct("Route with name '{$name}' already exists");
    }

}