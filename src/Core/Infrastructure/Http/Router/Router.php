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
    private readonly NavigatorInterface $navigator;

    /**
     * @var RouteInterface[]
     */
    protected array $routes = [];

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
    protected array $namedRoutes = [];

    private function __construct()
    {
        $this->urlGenerator = UrlGenerator::getInstance();
        $this->navigator = Navigator::getInstance();
    }

    /**
     * Find a route that matches the given request
     *
     * @param RequestInterface $request
     * @return NavigatorResult|null
     * @throws Exception\RouterException
     */
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
     * @param array $parameters
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
     * @param array $parameters
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
        $this->routes[] = $route;
        if ($route->name) {
            if (array_key_exists($route->name, $this->namedRoutes)) {
                throw new InvalidArgumentException("Route with name '$route->name' already exists");
            }
            $this->namedRoutes[$route->name] = $route;
        }
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

        $this->routes = array_filter($this->routes, fn($r) => $r !== $route);
        $this->routes = array_values($this->routes);

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

    /**
     * @return RouteInterface[]
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * @return array<string, RouteInterface>
     */
    public function getNamedRoutes(): array
    {
        return $this->namedRoutes;
    }
}
