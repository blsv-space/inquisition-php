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
    public string $path {
        get;
    }

    /**
     * @var HttpMethod[]
     */
    public array $methods {
        get;
    }

    /**
     * @var string|null
     */
    public null|string $name {
        get;
    }

    /**
     * @var class-string
     */
    public string $controller {
        get;
    }

    /**
     * @var string
     */
    public string $action {
        get;
    }

    /**
     * @var MiddlewareInterface[]
     */
    public array $middlewares {
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
