<?php

namespace Inquisition\Core\Infrastructure\Http\Router;

use Inquisition\Core\Infrastructure\Http\HttpMethod;
use Inquisition\Core\Infrastructure\Http\Middleware\MiddlewareInterface;
use InvalidArgumentException;

/**
 * Route Implementation
 * Concrete implementation of RouteInterface for HTTP routes
 */
class Route implements RouteInterface
{
    private(set) string $path {
        get {
            return $this->path;
        }
    }
    /**
     * @var HttpMethod[]
     */
    private(set) array   $methods {
        get {
            return $this->methods;
        }
    }
    private(set) ?string $name {
        get {
            return $this->name;
        }
    }
    private(set) mixed   $handler {
        get {
            return $this->handler;
        }
    }
    private array        $parameters = [];

    /**
     * @var MiddlewareInterface[]
     */
    private(set) array $middlewares     = [] {
        get {
            return $this->middlewares;
        }
        set {
            if ($value instanceof MiddlewareInterface) {
                $this->middlewares[] = $value;
            } elseif (is_array($value)) {
                $this->middlewares = array_merge($this->middlewares,
                    array_filter($value, fn($m) => $m instanceof MiddlewareInterface));;
            }
        }
    }
    public array       $defaults        = [] {
        get {
            return $this->defaults;
        }
    }
    private array      $metadata        = [];
    private ?string    $compiledPattern = null;
    private array      $parameterNames  = [];
    private array      $constraints     = [];


    public function __construct(
        string  $path,
        mixed   $handler,
        array   $methods = [HttpMethod::GET],
        ?string $name = null,
    ) {
        $this->path = $path;
        $this->handler = $handler;
        $this->methods = array_filter($methods, fn($m) => $m instanceof HttpMethod);;
        $this->name = $name;
        $this->compilePattern();

        Router::getInstance()->addRoute($this);
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function setParameters(array $parameters): self
    {
        $this->parameters = $parameters;

        return $this;
    }

    public function matches(string $method, string $path): bool
    {
        if (!$this->hasMethod($method)) {
            return false;
        }

        if ($this->compiledPattern === null) {
            return false;
        }

        $matches = [];
        if (preg_match($this->compiledPattern, $path, $matches)) {
            // Extract parameters
            $parameters = [];
            foreach ($this->parameterNames as $index => $name) {
                if (isset($matches[$index + 1])) {
                    $parameters[$name] = $matches[$index + 1];
                }
            }

            // Merge with defaults
            $this->parameters = array_merge($this->defaults, $parameters);

            return true;
        }

        return false;
    }

    public function middleware(string|array $middleware): self
    {
        $this->middlewares = $middleware;

        return $this;
    }

    public function name(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function where(string|array $constraints): self
    {
        if (is_string($constraints)) {
            // If it's a string, treat it as a constraint for all parameters
            // This is less common but could be useful
            foreach ($this->parameterNames as $paramName) {
                $this->constraints[$paramName] = $constraints;
            }
        } elseif (is_array($constraints)) {
            // Merge with existing constraints
            $this->constraints = array_merge($this->constraints, $constraints);
        }

        // Recompile the pattern with the new constraints
        $this->compilePattern();

        return $this;
    }


    public function defaults(array $defaults): self
    {
        $this->defaults = array_merge($this->defaults, $defaults);

        return $this;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function setMetadata(array $metadata): self
    {
        $this->metadata = $metadata;

        return $this;
    }

    public function hasMethod(string $method): bool
    {
        return in_array(strtoupper($method), $this->methods, true);
    }

    /**
     * Compile the route pattern into a regex
     */
    private function compilePattern(): void
    {
        $pattern = $this->path;
        $this->parameterNames = [];

        $pattern = preg_replace_callback(
            '/\{([^}]+)\}/',
            function ($matches) {
                $param = $matches[1];
                $isOptional = str_ends_with($param, '?');

                if ($isOptional) {
                    $param = substr($param, 0, -1);
                }

                $this->parameterNames[] = $param;

                $constraint = $this->constraints[$param] ?? '[^/]+';

                return $isOptional ? "({$constraint})?" : "({$constraint})";
            },
            $pattern
        );

        $pattern = str_replace('/', '\/', $pattern);

        $this->compiledPattern = '/^' . $pattern . '$/';
    }

    /**
     * Create a new route instance with the GET method
     */
    public static function get(string $path, mixed $handler, ?string $name = null): self
    {
        return new self($path, $handler, ['GET'], $name);
    }

    /**
     * Create a new route instance with the POST method
     */
    public static function post(string $path, mixed $handler, ?string $name = null): self
    {
        return new self($path, $handler, ['POST'], $name);
    }

    /**
     * Create a new route instance with the PUT method
     */
    public static function put(string $path, mixed $handler, ?string $name = null): self
    {
        return new self($path, $handler, ['PUT'], $name);
    }

    /**
     * Create a new route instance with the DELETE method
     */
    public static function delete(string $path, mixed $handler, ?string $name = null): self
    {
        return new self($path, $handler, ['DELETE'], $name);
    }

    /**
     * Create a new route instance with the PATCH method
     */
    public static function patch(string $path, mixed $handler, ?string $name = null): self
    {
        return new self($path, $handler, ['PATCH'], $name);
    }

    /**
     * Create a new route instance with multiple methods
     */
    public static function any(string $path, mixed $handler, ?string $name = null): self
    {
        return new self($path, $handler, ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], $name);
    }

    /**
     * Create a new route instance with custom methods
     */
    public static function match(array $methods, string $path, mixed $handler, ?string $name = null): self
    {
        return new self($path, $handler, $methods, $name);
    }
}