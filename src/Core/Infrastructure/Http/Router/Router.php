<?php

declare(strict_types=1);

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
    private readonly NavigatorInterface $navigator;

    /**
     * @var RouteInterface[]
     */
    public protected(set) array $routes = [] {
        get {
            return $this->routes;
        }
    }

    /**
     * @var RouteGroupInterface[] <string, RouteGroupInterface>
     */
    private array $routeGroups = [];

    public protected(set) ?RouteInterface $currentRoute = null {
        get {
            return $this->currentRoute;
        }
    }

    /**
     * @var array<string, RouteInterface>
     */
    public protected(set) array $namedRoutes = [] {
        get {
            return $this->namedRoutes;
        }
    }

    private function __construct()
    {
        $this->urlGenerator = UrlGenerator::getInstance();
        $this->navigator = Navigator::getInstance();
    }

    /**
     * Find a route that matches the given request
     *
     * @throws Exception\RouterException
     */
    #[\Override]
    public function routeByRequest(RequestInterface $request): ?NavigatorResult
    {
        foreach ($this->routes as $route) {
            $navigatorResult = $this->navigator->navigate($request, $route);
            if ($navigatorResult) {
                return $navigatorResult;
            }
        }

        return null;
    }

    /**
     * Get route by name
     *
     *
     */
    #[\Override]
    public function getRouteByName(string $name): ?RouteInterface
    {
        return $this->namedRoutes[$name] ?? null;
    }

    /**
     * Generate URL for a named route
     *
     *
     */
    #[\Override]
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
     *
     */
    #[\Override]
    public function generateUrlByRoute(RouteInterface $route, array $parameters = []): string
    {
        return $this->urlGenerator->generate($route, $parameters);
    }

    #[\Override]
    public function addRoute(RouteInterface $route): void
    {
        $routes = $this->routes;
        $routes[] = $route;
        $this->routes = $routes;
        if ($route->name) {
            if (array_key_exists($route->name, $this->namedRoutes)) {
                throw new InvalidArgumentException("Route with name '$route->name' already exists");
            }
            $namedRoutes = $this->namedRoutes;
            $namedRoutes[$route->name] = $route;
            $this->namedRoutes = $namedRoutes;
        }
    }

    #[\Override]
    public function group(string $name): RouteGroupInterface
    {
        return new RouteGroup($name);
    }

    #[\Override]
    public function groupRegistry(RouteGroupInterface $routeGroup): void
    {
        $this->routeGroups[$routeGroup->name] = $routeGroup;
    }

    #[\Override]
    public function getGroup(string $name): ?RouteGroupInterface
    {
        return $this->routeGroups[$name] ?? null;
    }

    /**
     * Check if the route exists by name
     *
     *
     */
    #[\Override]
    public function hasRoute(string $name): bool
    {
        return isset($this->namedRoutes[$name]);
    }


    /**
     * Remove route by name
     *
     *
     */
    #[\Override]
    public function removeRoute(string $name): bool
    {
        if (!$this->hasRoute($name)) {
            return false;
        }

        $route = $this->namedRoutes[$name];
        unset($this->namedRoutes[$name]);

        $this->routes = array_filter($this->routes, fn($r) => $r !== $route);
        $this->routes = array_values($this->routes);

        return true;
    }

    /**
     * Clear all routes
     *
     */
    #[\Override]
    public function clearRoutes(): void
    {
        $this->routes = [];
        $this->namedRoutes = [];
    }

}
