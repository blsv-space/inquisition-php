<?php

namespace Inquisition\Core\Infrastructure\Http\Router;

use Inquisition\Core\Infrastructure\Http\HttpMethod;
use Inquisition\Core\Infrastructure\Http\Request\RequestInterface;
use Inquisition\Core\Infrastructure\Http\Router\Exception\RouterException;
use Inquisition\Foundation\Singleton\SingletonInterface;
use Inquisition\Foundation\Singleton\SingletonTrait;

final class Navigator
    implements NavigatorInterface, SingletonInterface
{
    use SingletonTrait;

    /**
     * @param RequestInterface $request
     * @param RouteGroupInterface $routeGroup
     * @return NavigatorResult|null
     * @throws RouterException
     */
    public function navigateByRouteGroup(RequestInterface $request, RouteGroupInterface $routeGroup): ?NavigatorResult
    {
        $method = $request->getMethod();
        $path = $this->normalizePath($request->getUri());

        foreach ($routeGroup->routes as $route) {
            $parameters = $this->navigateRoute($route, $method, $path);
            if ($parameters !== null) {
                return new NavigatorResult(
                    route: $route,
                    parameters: $parameters,
                );
            }
        }

        return null;
    }

    /**
     * @param RequestInterface $request
     * @param RouteInterface $route
     * @return NavigatorResult|null
     * @throws RouterException
     */
    public function navigate(RequestInterface $request, RouteInterface $route): ?NavigatorResult
    {
        $method = $request->getMethod();
        $path = $this->normalizePath($request->getUri());

        $parameters = $this->navigateRoute($route, $method, $path);
        if ($parameters !== null) {
            return new NavigatorResult(
                route: $route,
                parameters: $parameters,
            );
        }

        return null;
    }

    /**
     * @param RouteInterface $route
     * @param HttpMethod $method
     * @param string $path
     * @return array<string, string>|null
     * @throws RouterException
     */
    public function navigateRoute(RouteInterface $route, HttpMethod $method, string $path): ?array
    {
        if (!$this->routeSupportsMethod($route, $method)) {
            return null;
        }

        return $this->validateAndExtractParameters($route, $path);
    }

    /**
     * @param RouteInterface $route
     * @param string $path
     *
     * @return array<string, string>|null
     *
     * @throws RouterException
     */
    public function validateAndExtractParameters(RouteInterface $route, string $path): ?array
    {
        $pathSteps = explode('/', trim($route->path, '/'));
        $requestSteps = explode('/', trim($path, '/'));

        if (count($pathSteps) !== count($requestSteps)) {
            return null;
        }
        $parameters = [];
        foreach ($pathSteps as $index => $step) {
            if (str_starts_with($step, '{') && str_ends_with($step, '}')) {
                $paramName = substr($step, 1, -1);
                if (empty($paramName)) {
                    throw new RouterException('Parameter name cannot be empty');
                }
                $parameters[$paramName] = $requestSteps[$index];
                continue;
            }

            if ($requestSteps[$index] !== $step) {
                return null;
            }
        }

        return $parameters;
    }

    /**
     * @param string $path
     * @return string
     */
    public function normalizePath(string $path): string
    {
        $path = strtok($path, '?') ?: $path;

        $path = '/' . trim($path, '/');

        if ($path === '/') {
            return '/';
        }

        return rtrim($path, '/');
    }

    /**
     * @param RouteInterface $route
     * @param HttpMethod $method
     * @return bool
     */
    private function routeSupportsMethod(RouteInterface $route, HttpMethod $method): bool
    {
        return in_array($method, $route->methods, true);
    }
}