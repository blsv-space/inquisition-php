<?php

declare(strict_types=1);

namespace Inquisition\Core\Infrastructure\Http\Router;

use Inquisition\Core\Infrastructure\Http\HttpMethod;
use Inquisition\Core\Infrastructure\Http\Request\RequestInterface;
use Inquisition\Core\Infrastructure\Http\Router\Exception\RouterException;
use Inquisition\Foundation\Singleton\SingletonInterface;
use Inquisition\Foundation\Singleton\SingletonTrait;

final class Navigator implements NavigatorInterface, SingletonInterface
{
    use SingletonTrait;

    /**
     * @throws RouterException
     */
    #[\Override]
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
     * @throws RouterException
     */
    #[\Override]
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
     * @throws RouterException
     * @return array<string, string>|null
     */
    #[\Override]
    public function navigateRoute(RouteInterface $route, HttpMethod $method, string $path): ?array
    {
        if (!$this->routeSupportsMethod($route, $method)) {
            return null;
        }

        return $this->validateAndExtractParameters($route, $path);
    }

    /**
     *
     *
     * @throws RouterException
     * @return array<string, string>|null
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

    #[\Override]
    public function normalizePath(string $path): string
    {
        $path = strtok($path, '?') ?: $path;

        $path = '/' . trim($path, '/');

        if ($path === '/') {
            return '/';
        }

        return rtrim($path, '/');
    }

    private function routeSupportsMethod(RouteInterface $route, HttpMethod $method): bool
    {
        return in_array($method, $route->methods, true);
    }
}
