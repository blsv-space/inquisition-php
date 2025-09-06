<?php

namespace Inquisition\Core\Infrastructure\Http\Router;

use Inquisition\Core\Infrastructure\Http\HttpMethod;
use Inquisition\Core\Infrastructure\Http\Middleware\MiddlewareInterface;
use InvalidArgumentException;

class RouteGroup implements RouteGroupInterface
{
    protected(set) string $prefix = '' {
        get {
            return $this->prefix;
        }
    }

    protected(set) array $middlewares = [] {
        get {
            return $this->middlewares;
        }
        set {
            if ($value instanceof MiddlewareInterface) {
                $this->middlewares[] = $value;
            } elseif (is_array($value)) {
                $this->middlewares = array_merge($this->middlewares,
                    array_filter($value, fn($m) => $m instanceof MiddlewareInterface));
            }
        }
    }

    protected(set) ?string $namespace = null {
        get {
            return $this->namespace;
        }
    }

    protected(set) ?string $namePrefix = null {
        get {
            return $this->namePrefix;
        }
    }

    protected(set) array $constraints = [] {
        get {
            return $this->constraints;
        }

        set {
            if (is_string($value)) {
                $this->constraints['default'] = $value;
            } elseif (is_array($value)) {
                $this->constraints = array_merge($this->constraints, $value);
            }
        }
    }

    protected(set) array $attributes = [] {
        get {
            return $this->attributes;
        }

        set {
            $this->attributes = array_merge($this->attributes, $value);
        }
    }

    protected(set) array $routes = [] {
        get {
            return $this->routes;
        }

        set {
            foreach ($value as $route) {
                if (!($route instanceof RouteInterface)) {
                    throw new InvalidArgumentException('Route must be an instance of RouteInterface');
                }

                $this->routes[] = $value;
            }
        }
    }

    /**
     * Set the group prefix
     */
    public function prefix(string $prefix): self
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * Add middleware to the group
     */
    public function middleware(string|array $middleware): self
    {
        $this->middlewares = $middleware;

        return $this;
    }

    /**
     * Set the group namespace
     */
    public function namespace(string $namespace): self
    {
        $this->namespace = $namespace;

        return $this;
    }

    /**
     * Set the group name prefix
     */
    public function namePrefix(string $namePrefix): self
    {
        $this->namePrefix = $namePrefix;

        return $this;
    }

    /**
     * Add constraints to the group
     */
    public function where(string|array $constraints): self
    {
        $this->constraints = $constraints;

        return $this;
    }

    /**
     * Set group attributes
     */
    public function attributes(array $attributes): self
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * Create a route within this group
     */
    public function route(
        string  $path,
        mixed   $handler = null,
        array   $methods = [HttpMethod::GET],
        ?string $name = null,
    ): RouteInterface {
        $fullPath = $this->buildFullPath($path);
        $fullName = $this->buildFullName($name);
        $fullHandler = $this->buildFullHandler($handler);

        foreach ($methods as $method) {
            if (!($method instanceof HttpMethod)) {
                throw new InvalidArgumentException('Method must be an instance of HttpMethod');
            }
        }

        $route = new Route($fullPath, $fullHandler, $methods, $fullName);
        $this->applyToRoute($route);
        $this->routes = [$route];

        return $route;
    }

    /**
     * Create a GET route within this group
     */
    public function get(string $path, mixed $handler, ?string $name = null): RouteInterface
    {
        return $this->route($path, $handler, [HttpMethod::GET], $name);
    }

    /**
     * Create a POST route within this group
     */
    public function post(string $path, mixed $handler, ?string $name = null): RouteInterface
    {
        return $this->route($path, $handler, [HttpMethod::POST], $name);
    }

    /**
     * Create a PUT route within this group
     */
    public function put(string $path, mixed $handler, ?string $name = null): RouteInterface
    {
        return $this->route($path, $handler, [HttpMethod::PUT], $name);
    }

    /**
     * Create a DELETE route within this group
     */
    public function delete(string $path, mixed $handler, ?string $name = null): RouteInterface
    {
        return $this->route($path, $handler, [HttpMethod::DELETE], $name);
    }

    /**
     * Create a PATCH route within this group
     */
    public function patch(string $path, mixed $handler, ?string $name = null): RouteInterface
    {
        return $this->route($path, $handler, [HttpMethod::PATCH], $name);
    }

    /**
     * Create a route that matches any HTTP method
     */
    public function any(string $path, mixed $handler, ?string $name = null): RouteInterface
    {
        return $this->route($path, $handler, [
            HttpMethod::GET,
            HttpMethod::POST,
            HttpMethod::PUT,
            HttpMethod::DELETE,
            HttpMethod::PATCH,
        ], $name);
    }

    /**
     * Create a route that matches specific HTTP methods
     */
    public function match(array $methods, string $path, mixed $handler, ?string $name = null): RouteInterface
    {
        return $this->route($path, $handler, $methods, $name);
    }

    /**
     * Create a nested group within this group
     */
    public function group(callable $callback): self
    {
        $nestedGroup = new self();
        $nestedGroup = $nestedGroup->mergeWith($this);

        $callback($nestedGroup);

        // Merge routes from the nested group
        $this->routes = array_merge($this->routes, $nestedGroup->routes);

        return $this;
    }

    /**
     * Apply group attributes to the route
     */
    public function applyToRoute(RouteInterface $route): RouteInterface
    {
        // Apply middlewares
        if (!empty($this->middlewares)) {
            $route->middleware($this->middlewares);
        }

        // Apply constraints
        if (!empty($this->constraints)) {
            $route->where($this->constraints);
        }

        return $route;
    }

    /**
     * Check if a group has middleware
     */
    public function hasMiddleware(string $middleware): bool
    {
        return in_array($middleware, $this->middlewares, true);
    }

    /**
     * Merge with a parent group
     */
    public function mergeWith(RouteGroupInterface $parentGroup): RouteGroupInterface
    {
        $merged = new self();

        // Merge prefix
        $merged->prefix = $this->buildMergedPrefix($parentGroup->prefix, $this->prefix);

        // Merge middlewares
        $merged->middlewares = array_merge($parentGroup->middlewares, $this->middlewares);

        // Merge namespace
        $merged->namespace = $this->buildMergedNamespace($parentGroup->namespace, $this->namespace);

        // Merge name prefix
        $merged->namePrefix = $this->buildMergedNamePrefix($parentGroup->namePrefix, $this->namePrefix);

        // Merge constraints
        $merged->constraints = array_merge($parentGroup->constraints, $this->constraints);

        // Merge attributes
        $merged->attributes = array_merge($parentGroup->attributes, $this->attributes);

        return $merged;
    }

    /**
     * Build the full path by combining group prefix with the route path
     */
    private function buildFullPath(string $path): string
    {
        $fullPath = $this->prefix;

        if (!empty($path) && $path !== '/') {
            $fullPath .= '/' . ltrim($path, '/');
        }

        return $fullPath ?: '/';
    }

    /**
     * Build the full name by combining the group name prefix with route name
     */
    private function buildFullName(?string $name): ?string
    {
        if ($name === null) {
            return null;
        }

        if ($this->namePrefix !== null) {
            return $this->namePrefix . '.' . $name;
        }

        return $name;
    }

    /**
     * Build the full handler by combining group namespace with handler
     */
    private function buildFullHandler(mixed $handler): mixed
    {
        if ($this->namespace !== null && is_string($handler)) {
            return $this->namespace . '\\' . $handler;
        }

        return $handler;
    }

    /**
     * Build merged prefix from parent and current
     */
    private function buildMergedPrefix(string $parentPrefix, string $currentPrefix): string
    {
        if (empty($parentPrefix)) {
            return $currentPrefix;
        }

        if (empty($currentPrefix)) {
            return $parentPrefix;
        }

        return $parentPrefix . '/' . ltrim($currentPrefix, '/');
    }

    /**
     * Build merged namespace from parent and current
     */
    private function buildMergedNamespace(?string $parentNamespace, ?string $currentNamespace): ?string
    {
        if ($parentNamespace === null) {
            return $currentNamespace;
        }

        if ($currentNamespace === null) {
            return $parentNamespace;
        }

        return $parentNamespace . '\\' . $currentNamespace;
    }

    /**
     * Build merged name prefix from parent and current
     */
    private function buildMergedNamePrefix(?string $parentNamePrefix, ?string $currentNamePrefix): ?string
    {
        if ($parentNamePrefix === null) {
            return $currentNamePrefix;
        }

        if ($currentNamePrefix === null) {
            return $parentNamePrefix;
        }

        return $parentNamePrefix . '.' . $currentNamePrefix;
    }

    public function setNamespace(?string $namespace): RouteGroup
    {
        $this->namespace = $namespace;

        return $this;
    }
}
