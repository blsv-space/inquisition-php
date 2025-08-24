<?php

namespace Inquisition\Core\Infrastructure\Http\Router;

use Inquisition\Core\Application\Http\Request\RequestInterface;
use Inquisition\Foundation\Singleton\SingletonInterface;
use Inquisition\Foundation\Singleton\SingletonTrait;

final readonly class RouteMatcher
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

    public function matchByRouteGroup(RequestInterface $request, RouteGroupInterface $routeGroup): ?RouteMatchResult
    {
        $method = $request->getMethod()->value;
        $path = $this->normalizePath($request->getUri());

        foreach ($routeGroup->routes as $route) {
            $matchResult = $this->matchRoute($route, $method, $path);
            if ($matchResult !== null) {
                return new RouteMatchResult(
                    route: $route,
                    parameters: $matchResult,
                    middlewares: $routeGroup->middlewares
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
        $method = $request->getMethod()->value;
        $path = $this->normalizePath($request->getUri());

        $matchResult = $this->matchRoute($route, $method, $path);
        if ($matchResult !== null) {
            return new RouteMatchResult(
                route: $route,
                parameters: $matchResult,
                middlewares: $route->middlewares
            );
        }

        return null;
    }

    public function matchRoute(RouteInterface $route, string $method, string $path): ?array
    {
        // Check if the route supports the requested HTTP method
        if (!$this->routeSupportsMethod($route, $method)) {
            return null;
        }

        // Get the compiled pattern for the route
        $pattern = $this->compilePattern($route->path, $route->constraints ?? []);

        // Normalize the route path for matching
        $normalizedPath = $this->normalizePath($path);

        // Attempt to match the path against the pattern
        if (!preg_match($pattern, $normalizedPath, $matches)) {
            return null;
        }

        // Extract parameters from the matches
        $parameters = $this->extractParametersFromMatches($matches, $route->path);

        // Merge with default values
        return array_merge($route->defaults, $parameters);
    }

    public function extractParameters(string $pattern, string $path): array
    {
        $compiledPattern = $this->compilePattern($pattern);
        $normalizedPath = $this->normalizePath($path);

        if (!preg_match($compiledPattern, $normalizedPath, $matches)) {
            return [];
        }

        return $this->extractParametersFromMatches($matches, $pattern);
    }

    public function compilePattern(string $pattern, array $constraints = []): string
    {
        $normalizedPattern = $this->normalizePath($pattern);

        // Handle optional parameters first
        $compiledPattern = preg_replace_callback(
            self::OPTIONAL_PARAMETER_PATTERN,
            function ($matches) use ($constraints) {
                $paramName = $matches[1];
                $constraint = $this->getConstraintPattern($paramName, $constraints);

                return '(?:/(' . $constraint . '))?';
            },
            $normalizedPattern
        );

        // Handle required parameters
        $compiledPattern = preg_replace_callback(
            self::PARAMETER_PATTERN,
            function ($matches) use ($constraints) {
                $paramName = $matches[1];
                $constraint = $this->getConstraintPattern($paramName, $constraints);

                return '(' . $constraint . ')';
            },
            $compiledPattern
        );

        // Escape special regex characters except those we want to keep
        $compiledPattern = str_replace(['(', ')'], ['\(', '\)'], $compiledPattern);
        $compiledPattern = str_replace(['\(', '\)'], ['(', ')'], $compiledPattern);

        return '#^' . $compiledPattern . '$#i';
    }

    public function normalizePath(string $path): string
    {
        // Remove query string if present
        $path = strtok($path, '?') ?: $path;

        // Normalize slashes
        $path = '/' . trim($path, '/');

        // Handle root path
        if ($path === '/') {
            return '/';
        }

        // Remove trailing slash for non-root paths
        return rtrim($path, '/');
    }

    private function routeSupportsMethod(RouteInterface $route, string $method): bool
    {
        return array_any($route->methods, fn($routeMethod) => $routeMethod->value === strtoupper($method));

    }

    private function getConstraintPattern(string $paramName, array $constraints): string
    {
        if (!isset($constraints[$paramName])) {
            return '[^/]+'; // Default pattern - match anything except slash
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

        return '[^/]+'; // Fallback to default
    }

    private function extractParametersFromMatches(array $matches, string $originalPattern): array
    {
        $parameters = [];
        $paramIndex = 1; // Skip the full match at index 0

        // Find all parameter names in the original pattern
        preg_match_all('/\{([a-zA-Z_][a-zA-Z0-9_]*)\??}/', $originalPattern, $paramNames);

        foreach ($paramNames[1] as $index => $paramName) {
            if (isset($matches[$paramIndex + $index]) && $matches[$paramIndex + $index] !== '') {
                $parameters[$paramName] = $matches[$paramIndex + $index];
            }
        }

        return $parameters;
    }
}