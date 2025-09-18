<?php

namespace Inquisition\Core\Infrastructure\Http\Router;

use Inquisition\Core\Infrastructure\Http\HttpMethod;

/**
 * Route Group Interface
 * Defines the contract for route groups with fluent builder API
 */
interface RouteGroupInterface
{
    /**
     * Set the group prefix
     */
    public function prefix(string $prefix): self;

    /**
     * Add middleware to the group
     * @param string|array $middleware
     *
     * @return self
     */
    public function middleware(string|array $middleware): self;

    /**
     * Set the group name prefix
     */
    public function namePrefix(string $namePrefix): self;

    /**
     * Add constraints to the group
     */
    public function where(string|array $constraints): self;

    /**
     * Set group attributes
     */
    public function attributes(array $attributes): self;

    /**
     * Create a route within this group
     *
     * @param string $path
     * @param class-string $controller
     * @param string $action
     * @param array $methods
     * @param string|null $name
     * @return RouteInterface
     */
    public function route(
        string $path,
        string $controller,
        string $action,
        array $methods = [HttpMethod::GET],
        ?string $name = null
    ): RouteInterface;

    /**
     * Create a GET route within this group
     */
    public function get(string $path, string $controller, string $action, ?string $name = null): RouteInterface;

    /**
     * Create a POST route within this group
     */
    public function post(string $path, string $controller, string $action, ?string $name = null): RouteInterface;

    /**
     * Create a PUT route within this group
     */
    public function put(string $path, string $controller, string $action, ?string $name = null): RouteInterface;

    /**
     * Create a DELETE route within this group
     */
    public function delete(string $path, string $controller, string $action, ?string $name = null): RouteInterface;

    /**
     * Create a PATCH route within this group
     */
    public function patch(string $path, string $controller, string $action, ?string $name = null): RouteInterface;

    /**
     * Create a route that matches any HTTP method
     */
    public function any(string $path, string $controller, string $action, ?string $name = null): RouteInterface;

    /**
     * Create a route that matches specific HTTP methods
     */
    public function match(array $methods, string $path, string $controller, string $action, ?string $name = null): RouteInterface;

    /**
     * Create a nested group within this group
     */
    public function group(string $name): self;

    public string $prefix {
        get;
    }

    public array $middlewares {
        get;
    }

    public null|string $namePrefix {
        get;
    }

    public array $constraints {
        get;
    }

    public array $attributes {
        get;
    }

    /**
     * Apply group attributes to the route
     */
    public function applyToRoute(RouteInterface $route): RouteInterface;

    /**
     * Check if a group has middleware
     */
    public function hasMiddleware(string $middleware): bool;

    /**
     * Merge with a parent group
     */
    public function mergeWith(RouteGroupInterface $parentGroup): RouteGroupInterface;

    public array $routes {
        get;
    }
}



