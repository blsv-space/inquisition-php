<?php

namespace Inquisition\Core\Infrastructure\Http\Router\Exception;

class InvalidRouteException extends RouterException
{
    public function __construct(string $message)
    {
        parent::__construct("Invalid route: {$message}");
    }

}