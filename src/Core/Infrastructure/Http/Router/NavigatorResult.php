<?php

namespace Inquisition\Core\Infrastructure\Http\Router;

/**
 * Route Match Result
 * Represents the result of route matching
 */
final readonly class NavigatorResult
{
    public function __construct(
        private RouteInterface $route,
        /**
         * @var array<string, string>
         */
        private array          $parameters = [],
    )
    {
    }

    /**
     * Get the matched route
     *
     * @return RouteInterface
     */
    public function getRoute(): RouteInterface
    {
        return $this->route;
    }

    /**
     * Get extracted parameters
     *
     * @return array<string, string>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Get specific parameter
     *
     * @param string $name
     * @param string|null $default
     *
     * @return ?string
     */
    public function getParameter(string $name, ?string $default = null): ?string
    {
        return $this->parameters[$name] ?? $default;
    }

    /**
     * Get a route name
     *
     * @return string|null
     */
    public function getRouteName(): ?string
    {
        return $this->route->name;
    }

    /**
     * Check if parameter exists
     *
     * @param string $name
     * @return bool
     */
    public function hasParameter(string $name): bool
    {
        return array_key_exists($name, $this->parameters);
    }

    /**
     * Get a route path
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->route->path;
    }

    /**
     * Get route methods
     *
     * @return array
     */
    public function getMethods(): array
    {
        return $this->route->methods;
    }
}
