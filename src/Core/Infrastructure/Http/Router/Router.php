<?php

namespace Inquisition\Core\Infrastructure\Http\Router;

use Inquisition\Core\Infrastructure\Http\Request\RequestInterface;
use Inquisition\Foundation\Singleton\SingletonTrait;
use InvalidArgumentException;

/**
 * Router Implementation
 * Concrete implementation of RouterInterface for HTTP routing
 */
final class Router implements RouterInterface
{
    use SingletonTrait;

    private readonly UrlGeneratorInterface $urlGenerator;
    private readonly RouteMatcherInterface $routeMatcher;

    /**
     * @var RouteInterface[]
     */
    protected(set) array $routes = [] {
        get {
            return $this->routes;
        }

        set(RouteInterface|array $value) {
            if (!is_array($value)) {
                $value = [$value];
            }
            $named = [];
            foreach ($value as $route) {
                if (!$route instanceof RouteInterface) {
                    throw new InvalidArgumentException("Route must be an instance of RouteInterface");
                }
                $this->routes[] = $value;

                if ($route->name !== null) {
                    $named[$route->name] = $route;
                }
            }
            $named && $this->namedRoutes = $named;
        }
    }

    /**
     * @var RouteGroupInterface[] <string, RouteGroupInterface>
     */
    private array $routeGroups = [];

    /**
     * @var RouteInterface|null
     */
    protected(set) ?RouteInterface $currentRoute = null {
        get {
            return $this->currentRoute;
        }
    }

    /**
     * @var array<string, RouteInterface>
     */
    protected(set) array $namedRoutes = [] {
        get {
            return $this->namedRoutes;
        }

        set {
            foreach ($value as $route) {
                if ($this->hasRoute($route->name)) {
                    throw new InvalidArgumentException("Route name '$route->name' already exists");
                }
                $this->namedRoutes[$route->name] = $route;
            }
        }
    }

    private function __construct()
    {
        $this->urlGenerator = UrlGenerator::getInstance();
        $this->routeMatcher = RouteMatcher::getInstance();
    }

    /**
     * Find a route that matches the given request
     *
     * @param RequestInterface $request
     *
     * @return RouteMatchResult|null
     */
    public function routeByRequest(RequestInterface $request): ?RouteMatchResult
    {
        foreach ($this->routes as $route) {
            $routeMatchResult = $this->routeMatcher->match($request, $route);
            if ($routeMatchResult) {
                return new RouteMatchResult($route, $route->getParameters());
            }
        }

        return null;
    }

    /**
     * Get route by name
     *
     * @param string $name
     *
     * @return RouteInterface|null
     */
    public function getRouteByName(string $name): ?RouteInterface
    {
        return $this->namedRoutes[$name] ?? null;
    }

    /**
     * Generate URL for a named route
     *
     * @param string $name
     * @param array  $parameters
     *
     * @return string
     */
    public function generateUrlByName(string $name, array $parameters = []): string
    {
        $route = $this->getRouteByName($name);

        if ($route === null) {
            throw new InvalidArgumentException("Route '$name' not found");
        }

        return $this->generateUrlByRoute($route, $parameters);
    }

    /**
     * Generate URL for a route
     *
     * @param RouteInterface $route
     * @param array          $parameters
     *
     * @return string
     */
    public function generateUrlByRoute(RouteInterface $route, array $parameters = []): string
    {
        return $this->urlGenerator->generate($route, $parameters);
    }

    /**
     * @param RouteInterface $route
     *
     * @return void
     */
    public function addRoute(RouteInterface $route): void
    {
        $this->routes = $route;
    }

    /**
     * @param string $name
     * @return RouteGroupInterface
     */
    public function group(string $name): RouteGroupInterface
    {
        return new RouteGroup($name);
    }

    /**
     * @param RouteGroupInterface $routeGroup
     * @return void
     */
    public function groupRegistry(RouteGroupInterface $routeGroup): void
    {
        $this->routeGroups[$routeGroup->name] = $routeGroup;
    }

    /**
     * @param string $name
     * @return RouteGroupInterface|null
     */
    public function getGroup(string $name): ?RouteGroupInterface
    {
        return $this->routeGroups[$name] ?? null;
    }

    /**
     * Check if the route exists by name
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasRoute(string $name): bool
    {
        return isset($this->namedRoutes[$name]);
    }


    /**
     * Remove route by name
     *
     * @param string $name
     *
     * @return bool
     */
    public function removeRoute(string $name): bool
    {
        if (!$this->hasRoute($name)) {
            return false;
        }

        $route = $this->namedRoutes[$name];
        unset($this->namedRoutes[$name]);

        // Remove from routes array
        $this->routes = array_filter($this->routes, fn($r) => $r !== $route);
        $this->routes = array_values($this->routes); // Re-index array

        return true;
    }

    /**
     * Clear all routes
     *
     * @return void
     */
    public function clearRoutes(): void
    {
        $this->routes = [];
        $this->namedRoutes = [];
    }
}
