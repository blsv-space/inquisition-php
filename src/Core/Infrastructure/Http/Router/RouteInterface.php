<?php

namespace Inquisition\Core\Infrastructure\Http\Router;

use Inquisition\Core\Infrastructure\Http\HttpMethod;
use Inquisition\Core\Infrastructure\Http\Middleware\MiddlewareInterface;

/**
 * Route Interface
 * Defines the contract for HTTP routes
 */
interface RouteInterface
{
    private(set) string $path {
        get;
    }

    /**
     * @var HttpMethod[]
     */
    private(set) array $methods {
        get;
    }

    private(set) null|string $name {
        get;
    }

    private(set) mixed $handler {
        get;
    }

    /**
     * Get route parameters extracted from the URL
     */
    public function getParameters(): array;

    /**
     * Set route parameters
     */
    public function setParameters(array $parameters): self;

    /**
     * @var MiddlewareInterface[]
     */
    private(set) array $middlewares {
        get;
    }

    /**
     * Add middleware to the route (fluent interface)
     */
    public function middleware(string|array $middleware): self;


    /**
     * Check if the route matches the given method and path
     */
    public function matches(string $method, string $path): bool;

    private(set) array $defaults {
        get;
    }

    /**
     * Set default values for parameters
     */
    public function defaults(array $defaults): self;

    /**
     * Get route metadata
     */
    public function getMetadata(): array;

    /**
     * Set route metadata
     */
    public function setMetadata(array $metadata): self;

    /**
     * Check if the route has a specific method
     */
    public function hasMethod(string $method): bool;

    /**
     * Set route name (fluent interface)
     */
    public function name(string $name): self;

    /**
     * Add constraints to route parameters (fluent interface)
     */
    public function where(string|array $constraints): self;

}
