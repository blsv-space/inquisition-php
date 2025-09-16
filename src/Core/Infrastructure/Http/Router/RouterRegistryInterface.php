<?php

namespace Inquisition\Core\Infrastructure\Http\Router;

interface RouterRegistryInterface
{
    public static function register(?RouteGroupInterface $parentRouteGroup): void;
}