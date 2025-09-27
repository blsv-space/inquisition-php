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
    protected(set) string $path {
        get;
    }

    /**
     * @var HttpMethod[]
     */
    protected(set) array $methods {
        get;
    }

    /**
     * @var string|null
     */
    protected(set) null|string $name {
        get;
    }

    /**
     * @var class-string
     */
    protected(set) string $controller {
        get;
    }

    /**
     * @var string
     */
    protected(set) string $action {
        get;
    }

    /**
     * @var array
     */
    protected(set) array $constraints {
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
    protected(set) array $middlewares {
        get;
    }

    /**
     * Add middleware to the route (fluent interface)
     *
     * @param MiddlewareInterface|MiddlewareInterface[] $middleware
     * @return self
     */
    public function middleware(MiddlewareInterface|array $middleware): self;

    /**
     * Check if the route matches the given method and path
     */
    public function matches(HttpMethod $method, string $path): bool;

    public array $defaults {
        get;
        set;
    }

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
    public function hasMethod(HttpMethod $method): bool;

    /**
     * Set route name (fluent interface)
     */
    public function name(string $name): self;

    /**
     * Add constraints to route parameters (fluent interface)
     */
    public function where(array $constraints): self;

}
