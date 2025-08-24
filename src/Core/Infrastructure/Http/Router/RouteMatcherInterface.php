<?php

namespace Inquisition\Core\Infrastructure\Http\Router;

use Inquisition\Core\Application\Http\Request\RequestInterface;

/**
 * Route Matcher Interface
 * Defines the contract for route matching logic
 */
interface RouteMatcherInterface
{
    /**
     * Find a matching route for the request based on the route group
     */
    public function matchByRouteGroup(RequestInterface $request, RouteGroupInterface $routeGroup): ?RouteMatchResult;

    /**
     * Find a matching route for the request
     */
    public function match(RequestInterface $request, RouteInterface $route): ?RouteMatchResult;

    /**
     * Check if a route matches the request
     */
    public function matchRoute(RouteInterface $route, string $method, string $path): ?array;

    /**
     * Extract parameters from a path using a route pattern
     */
    public function extractParameters(string $pattern, string $path): array;

    /**
     * Compile route pattern to regex
     */
    public function compilePattern(string $pattern, array $constraints = []): string;

    /**
     * Normalize a path for matching
     */
    public function normalizePath(string $path): string;
}
