<?php

declare(strict_types=1);

namespace Inquisition\Core\Infrastructure\Http\Router;

interface RouterRegistryInterface
{
    public static function register(?RouteGroupInterface $parentRouteGroup): void;
}
