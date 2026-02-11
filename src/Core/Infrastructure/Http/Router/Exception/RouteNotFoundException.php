<?php

declare(strict_types=1);

namespace Inquisition\Core\Infrastructure\Http\Router\Exception;

class RouteNotFoundException extends RouterException
{
    public function __construct(string $method, string $path)
    {
        parent::__construct("Route not found: $method $path");
    }
}
