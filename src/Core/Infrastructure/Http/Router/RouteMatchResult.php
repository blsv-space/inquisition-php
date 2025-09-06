<?php

namespace Inquisition\Core\Infrastructure\Http\Router;

use _PHPStan_95d365e52\React\Dns\Model\Record;

/**
 * Route Match Result
 * Represents the result of route matching
 */
final readonly class RouteMatchResult
{
    public function __construct(
        private RouteInterface $route,
        private array          $parameters = [],
        private array          $middlewares = [],
    ) {
    }

    /**
     * Get the matched route
     */
    public function getRoute(): RouteInterface
    {
        return $this->route;
    }

    /**
     * Get extracted parameters
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Get specific parameter
     */
    public function getParameter(string $name, mixed $default = null): mixed
    {
        return $this->parameters[$name] ?? $default;
    }

    /**
     * Get route handler
     */
    public function getHandler(): RouteInterface
    {
        return $this->route;
    }

    /**
     * Get all middlewares (route + group middlewares)
     */
    public function getMiddlewares(): array
    {
        return array_merge($this->middlewares, $this->route->middlewares);
    }

    /**
     * Get a route name
     */
    public function getRouteName(): ?string
    {
        return $this->route->name;
    }

    /**
     * Check if parameter exists
     */
    public function hasParameter(string $name): bool
    {
        return array_key_exists($name, $this->parameters);
    }

    /**
     * Get a route path
     */
    public function getPath(): string
    {
        return $this->route->path;
    }

    /**
     * Get route methods
     */
    public function getMethods(): array
    {
        return $this->route->methods;
    }
}
