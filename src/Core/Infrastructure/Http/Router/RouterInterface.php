<?php

namespace Inquisition\Core\Infrastructure\Http\Router;

use Inquisition\Core\Application\Http\Request\RequestInterface;
use Inquisition\Foundation\Singleton\SingletonInterface;

/**
 * Router Interface
 * Defines the contract for HTTP routing
 */
interface RouterInterface extends SingletonInterface
{
    /**
     * Find a route that matches the given request
     */
    public function routeByRequest(RequestInterface $request): ?RouteMatchResult;

    public array $routes {
        get;
    }

    /**
     * Get route by name
     */
    public function getRouteByName(string $name): ?RouteInterface;

    /**
     * Generate URL for a named route
     */
    public function generateUrlByName(string $name, array $parameters = []): string;

    /**
     * Generate URL for a route
     */
    public function generateUrlByRoute(RouteInterface $route, array $parameters = []): string;

    /**
     * Register an router
     */
    public function addRoute(RouteInterface $route): void;

    /**
     * Check if the route exists by name
     */
    public function hasRoute(string $name): bool;

    /**
     * Remove route by name
     */
    public function removeRoute(string $name): bool;

    /**
     * Clear all routes
     */
    public function clearRoutes(): void;

}
