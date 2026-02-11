<?php

declare(strict_types=1);

namespace Inquisition\Core\Infrastructure\Http\Router;

use Inquisition\Core\Infrastructure\Http\HttpMethod;
use Inquisition\Core\Infrastructure\Http\Request\RequestInterface;

/**
 * Route Matcher Interface
 * Defines the contract for route matching logic
 */
interface NavigatorInterface
{
    /**
     * Navigate to a route for the request based on the route group
     */
    public function navigateByRouteGroup(RequestInterface $request, RouteGroupInterface $routeGroup): ?NavigatorResult;

    /**
     * Find a matching route for the request
     */
    public function navigate(RequestInterface $request, RouteInterface $route): ?NavigatorResult;

    /**
     * Check if a route matches the request
     */
    public function navigateRoute(RouteInterface $route, HttpMethod $method, string $path): ?array;

    /**
     * Normalize a path for matching
     */
    public function normalizePath(string $path): string;
}
