<?php

namespace Inquisition\Core\Infrastructure\Http\Router;

use Inquisition\Core\Infrastructure\Http\HttpMethod;
use Inquisition\Core\Infrastructure\Http\Middleware\MiddlewareInterface;
use InvalidArgumentException;

class RouteGroup implements RouteGroupInterface
{
    /**
     * @var string
     */
    protected(set) string $name {
        get {
            return $this->name;
        }
    }

    /**
     * @var string
     */
    protected(set) string $prefix = '' {
        get {
            return $this->prefix;
        }
    }

    /**
     * @var array
     */
    protected(set) array $middlewares = [] {
        get {
            return $this->middlewares;
        }
        set {
            $this->middlewares = array_merge($this->middlewares,
                array_filter($value, fn($m) => $m instanceof MiddlewareInterface));
        }
    }

    /**
     * @var string|null
     */
    protected(set) ?string $namePrefix = null {
        get {
            return $this->namePrefix;
        }
    }

    /**
     * @var array
     */
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

    /**
     * @var array
     */
    protected(set) array $attributes = [] {
        get {
            return $this->attributes;
        }

        set {
            $this->attributes = array_merge($this->attributes, $value);
        }
    }

    /**
     * @var array
     */
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

    public function __construct(string $name)
    {
        $this->name = $name;
        Router::getInstance()->groupRegistry($this);
    }

    /**
     * Set the group prefix
     *
     * @param string $prefix
     * @return $this
     */
    public function prefix(string $prefix): self
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * Add middleware to the group
     *
     * @param MiddlewareInterface|MiddlewareInterface[] $middleware
     * @return $this
     */
    public function middleware(MiddlewareInterface|array $middleware): self
    {
        $this->middlewares = $middleware;

        return $this;
    }

    /**
     * Set the group name prefix
     *
     * @param string $namePrefix
     * @return $this
     */
    public function namePrefix(string $namePrefix): self
    {
        $this->namePrefix = $namePrefix;

        return $this;
    }

    /**
     * Add constraints to the group
     *
     * @param string|array $constraints
     * @return $this
     */
    public function where(string|array $constraints): self
    {
        $this->constraints = $constraints;

        return $this;
    }

    /**
     * Set group attributes
     *
     * @param array $attributes
     * @return $this
     */
    public function attributes(array $attributes): self
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * Create a route within this group
     *
     * @param string $path
     * @param string $controller
     * @param string $action
     * @param array $methods
     * @param string|null $name
     * @return RouteInterface
     */
    public function route(
        string  $path,
        string  $controller,
        string  $action,
        array   $methods,
        ?string $name = null,
    ): RouteInterface
    {
        $fullPath = $this->buildFullPath($path);
        $fullName = $this->buildFullName($name);

        $route = new Route($fullPath, $controller, $action, $methods, $fullName);
        $this->applyToRoute($route);
        $this->routes = [$route];
        Router::getInstance()->addRoute($route);

        return $route;
    }

    /**
     * Create a GET route within this group
     *
     * @param string $path
     * @param string $controller
     * @param string $action
     * @param string|null $name
     *
     * @return self
     */
    public function get(string $path, string $controller, string $action, ?string $name = null): self
    {
        $this->route(
            path: $path,
            controller: $controller,
            action: $action,
            methods: [HttpMethod::GET],
            name: $name ?? $this->buildRouteName($action),
        );

        return $this;
    }

    /**
     * Create a POST route within this group
     *
     * @param string $path
     * @param string $controller
     * @param string $action
     * @param string|null $name
     *
     * @return self
     */
    public function post(string $path, string $controller, string $action, ?string $name = null): self
    {
        $this->route(
            path: $path,
            controller: $controller,
            action: $action,
            methods: [HttpMethod::POST],
            name: $name ?? $this->buildRouteName($action),
        );

        return $this;
    }

    /**
     * Create a PUT route within this group
     *
     * @param string $path
     * @param string $controller
     * @param string $action
     * @param string|null $name
     *
     * @return self
     */
    public function put(string $path, string $controller, string $action, ?string $name = null): self
    {
        $this->route(
            path: $path,
            controller: $controller,
            action: $action,
            methods: [HttpMethod::PUT],
            name: $name ?? $this->buildRouteName($action),
        );

        return $this;
    }

    /**
     * Create a DELETE route within this group
     *
     * @param string $path
     * @param string $controller
     * @param string $action
     * @param string|null $name
     *
     * @return self
     */
    public function delete(string $path, string $controller, string $action, ?string $name = null): self
    {
        $this->route(
            path: $path,
            controller: $controller,
            action: $action,
            methods: [HttpMethod::DELETE],
            name: $name ?? $this->buildRouteName($action),
        );

        return $this;
    }

    /**
     * Create a PATCH route within this group
     *
     * @param string $path
     * @param string $controller
     * @param string $action
     * @param string|null $name
     *
     * @return self
     */
    public function patch(string $path, string $controller, string $action, ?string $name = null): self
    {
        $this->route(
            path: $path,
            controller: $controller,
            action: $action,
            methods: [HttpMethod::PATCH],
            name: $name ?? $this->buildRouteName($action),
        );

        return $this;
    }

    /**
     * Create a route that matches any HTTP method
     *
     * @param string $path
     * @param string $controller
     * @param string $action
     * @param string|null $name
     *
     * @return self
     */
    public function any(string $path, string $controller, string $action, ?string $name = null): self
    {
        $this->route(
            path: $path,
            controller: $controller,
            action: $action,
            methods: [
                HttpMethod::GET,
                HttpMethod::POST,
                HttpMethod::PUT,
                HttpMethod::DELETE,
                HttpMethod::PATCH,
            ],
            name: $name ?? $this->buildRouteName($action),
        );

        return $this;
    }

    /**
     * Create a route that matches specific HTTP methods
     *
     * @param array $methods
     * @param string $path
     * @param string $controller
     * @param string $action
     * @param string|null $name
     *
     * @return self
     */
    public function match(array $methods, string $path, string $controller, string $action, ?string $name = null): self
    {
        $this->route(
            path: $path,
            controller: $controller,
            action: $action,
            methods: $methods,
            name: $name ?? $this->buildRouteName($action),
        );

        return $this;
    }

    /**
     * @param string $action
     * @return string
     */
    private function buildRouteName(string $action): string
    {
        return $this->name . '->' . $action;
    }

    /**
     * Create a nested group within this group
     *
     * @param string $name
     * @return $this
     */
    public function group(string $name): self
    {
        $name = $this->name . '.' . $name;

        $nestedGroup = new self($name);
        $nestedGroup->mergeWith($this);

        return $nestedGroup;
    }

    /**
     * Apply group attributes to the route
     *
     * @param RouteInterface $route
     * @return RouteInterface
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
     *
     * @param MiddlewareInterface $middleware
     * @return bool
     */
    public function hasMiddleware(MiddlewareInterface $middleware): bool
    {
        return array_any($this->middlewares, fn (MiddlewareInterface $m) => $m::class === $middleware::class);
    }

    /**
     * Merge with a parent group
     *
     * @param RouteGroupInterface $parentGroup
     * @return RouteGroupInterface
     */
    public function mergeWith(RouteGroupInterface $parentGroup): self
    {
        $this->prefix = $this->buildMergedPrefix($parentGroup->prefix, $this->prefix);
        $this->middlewares = array_merge($parentGroup->middlewares, $this->middlewares);
        $this->namePrefix = $this->buildMergedNamePrefix($parentGroup->namePrefix, $this->namePrefix);
        $this->constraints = array_merge($parentGroup->constraints, $this->constraints);
        $this->attributes = array_merge($parentGroup->attributes, $this->attributes);

        return $this;
    }

    /**
     * Build the full path by combining group prefix with the route path
     *
     * @param string $path
     * @return string
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
     *
     * @param string|null $name
     * @return string|null
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
     * Build merged prefix from parent and current
     *
     * @param string $parentPrefix
     * @param string $currentPrefix
     * @return string
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
     * Build merged name prefix from parent and current
     *
     * @param string|null $parentNamePrefix
     * @param string|null $currentNamePrefix
     * @return string|null
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
}
