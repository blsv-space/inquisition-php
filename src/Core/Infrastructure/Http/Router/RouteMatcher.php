<?php

namespace Inquisition\Core\Infrastructure\Http\Router;

use Inquisition\Core\Infrastructure\Http\HttpMethod;
use Inquisition\Core\Infrastructure\Http\Request\RequestInterface;
use Inquisition\Foundation\Singleton\SingletonInterface;
use Inquisition\Foundation\Singleton\SingletonTrait;

final class RouteMatcher
    implements RouteMatcherInterface, SingletonInterface
{
    use SingletonTrait;

    private const string PARAMETER_PATTERN = '/\{([a-zA-Z_][a-zA-Z0-9_]*)}/';
    private const string OPTIONAL_PARAMETER_PATTERN = '/\{([a-zA-Z_][a-zA-Z0-9_]*)\?}/';
    private const array CONSTRAINT_PATTERN_MAP = [
        'int' => '\d+',
        'number' => '\d+',
        'alpha' => '[a-zA-Z]+',
        'alphanumeric' => '[a-zA-Z0-9]+',
        'slug' => '[a-zA-Z0-9\-_]+',
        'uuid' => '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}',
    ];

    /**
     * @param RequestInterface $request
     * @param RouteGroupInterface $routeGroup
     * @return RouteMatchResult|null
     */
    public function matchByRouteGroup(RequestInterface $request, RouteGroupInterface $routeGroup): ?RouteMatchResult
    {
        $method = $request->getMethod();
        $path = $this->normalizePath($request->getUri());

        foreach ($routeGroup->routes as $route) {
            $parameters = $this->matchRoute($route, $method, $path);
            if ($parameters !== null) {
                return new RouteMatchResult(
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
     * @return RouteMatchResult|null
     */
    public function match(RequestInterface $request, RouteInterface $route): ?RouteMatchResult
    {
        $method = $request->getMethod();
        $path = $this->normalizePath($request->getUri());

        $parameters = $this->matchRoute($route, $method, $path);
        if ($parameters !== null) {
            return new RouteMatchResult(
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
     */
    public function matchRoute(RouteInterface $route, HttpMethod $method, string $path): ?array
    {
        if (!$this->routeSupportsMethod($route, $method)) {
            return null;
        }

        $pattern = $this->compilePattern($route->path, $route->constraints ?? []);
        $normalizedPath = $this->normalizePath($path);

        if (!preg_match($pattern, $normalizedPath, $matches)) {
            return null;
        }
        $parameters = $this->extractParametersFromMatches($matches, $route->path);

        return array_merge($route->defaults, $parameters);
    }

    /**
     * @param string $pattern
     * @param string $path
     * @return array
     */
    public function extractParameters(string $pattern, string $path): array
    {
        $compiledPattern = $this->compilePattern($pattern);
        $normalizedPath = $this->normalizePath($path);

        if (!preg_match($compiledPattern, $normalizedPath, $matches)) {
            return [];
        }

        return $this->extractParametersFromMatches($matches, $pattern);
    }

    /**
     * @param string $pattern
     * @param array $constraints
     * @return string
     */
    public function compilePattern(string $pattern, array $constraints = []): string
    {
        $normalizedPattern = $this->normalizePath($pattern);

        $compiledPattern = preg_replace_callback(
            self::OPTIONAL_PARAMETER_PATTERN,
            function ($matches) use ($constraints) {
                $paramName = $matches[1];
                $constraint = $this->getConstraintPattern($paramName, $constraints);

                return '(?:/(' . $constraint . '))?';
            },
            $normalizedPattern
        );

        $compiledPattern = preg_replace_callback(
            self::PARAMETER_PATTERN,
            function ($matches) use ($constraints) {
                $paramName = $matches[1];
                $constraint = $this->getConstraintPattern($paramName, $constraints);

                return '(' . $constraint . ')';
            },
            $compiledPattern
        );

        $compiledPattern = str_replace(['(', ')'], ['\(', '\)'], $compiledPattern);
        $compiledPattern = str_replace(['\(', '\)'], ['(', ')'], $compiledPattern);

        return '#^' . $compiledPattern . '$#i';
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

    /**
     * @param string $paramName
     * @param array $constraints
     * @return string
     */
    private function getConstraintPattern(string $paramName, array $constraints): string
    {
        if (!isset($constraints[$paramName])) {
            return '[^/]+';
        }

        $constraint = $constraints[$paramName];

        // If it's a predefined constraint type
        if (isset(self::CONSTRAINT_PATTERN_MAP[$constraint])) {
            return self::CONSTRAINT_PATTERN_MAP[$constraint];
        }

        // If it's a custom regex pattern
        if (is_string($constraint)) {
            return $constraint;
        }

        return '[^/]+';
    }

    /**
     * @param string[] $matches
     * @param string $originalPattern
     * @return array<string, string>
     */
    private function extractParametersFromMatches(array $matches, string $originalPattern): array
    {
        $parameters = [];
        $paramIndex = 1;

        preg_match_all('/\{([a-zA-Z_][a-zA-Z0-9_]*)\??}/', $originalPattern, $paramNames);

        foreach ($paramNames[1] as $index => $paramName) {
            if (isset($matches[$paramIndex + $index]) && $matches[$paramIndex + $index] !== '') {
                $parameters[$paramName] = $matches[$paramIndex + $index];
            }
        }

        return $parameters;
    }
}