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
    protected(set) string $path {
        get => $this->path;
    }

    /**
     * @var HttpMethod[]
     */
    protected(set) array $methods {
        get => $this->methods;
    }

    /**
     * @var string|null
     */
    protected(set) ?string $name {
        get => $this->name;
    }

    /**
     * @var string
     */
    protected(set) string $controller {
        get => $this->controller;
    }

    /**
     * @var string
     */
    protected(set) string $action {
        get => $this->action;
    }

    /**
     * @var array
     */
    private array $parameters = [];

    /**
     * @var MiddlewareInterface[]
     */
    protected(set) array $middlewares = [] {
        get => $this->middlewares;
        set {
            if ($value instanceof MiddlewareInterface) {
                $this->middlewares[] = $value;
            } elseif (is_array($value)) {
                $this->middlewares = array_merge($this->middlewares,
                    array_filter($value, fn($m) => $m instanceof MiddlewareInterface));
            }
        }
    }
    private array $metadata = [];
    private ?string $compiledPattern = null;
    private array $parameterNames = [];
    protected(set) array $constraints = [] {
        get => $this->constraints;
    }


    /**
     * @param string $path
     * @param string $controller
     * @param string $action
     * @param array $methods
     * @param string|null $name
     */
    public function __construct(
        string  $path,
        string  $controller,
        string  $action,
        array   $methods = [HttpMethod::GET],
        ?string $name = null,
    )
    {
        if (!class_exists($controller)) {
            throw new InvalidArgumentException("Controller class $controller does not exist");
        }
        if (!method_exists($controller, $action)) {
            throw new InvalidArgumentException("Action $action does not exist in controller $controller");
        }
        if (empty($methods)) {
            throw new InvalidArgumentException('At least one method is required');
        }
        foreach ($methods as $method) {
            if (!($method instanceof HttpMethod)) {
                throw new InvalidArgumentException('Method must be an instance of HttpMethod');
            }
        }

        $this->path = trim($path, '/');
        $this->controller = $controller;
        $this->action = $action;
        $this->methods = array_filter($methods, fn($m) => $m instanceof HttpMethod);
        $this->name = $name;
        $this->compilePattern();

        Router::getInstance()->addRoute($this);
    }

    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @param array $parameters
     * @return $this
     */
    public function setParameters(array $parameters): self
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * @param HttpMethod $method
     * @param string $path
     * @return bool
     */
    public function matches(HttpMethod $method, string $path): bool
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
            $this->parameters = $parameters;

            return true;
        }

        return false;
    }

    /**
     * @param MiddlewareInterface|array $middleware
     * @return $this
     */
    public function middleware(MiddlewareInterface|array $middleware): self
    {
        $this->middlewares = $middleware;

        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function name(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param array $constraints
     * @return $this
     */
    public function where(array $constraints): self
    {
        $this->constraints = array_merge($this->constraints, $constraints);

        $this->compilePattern();

        return $this;
    }

    /**
     * @return array
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * @param array $metadata
     * @return $this
     */
    public function setMetadata(array $metadata): self
    {
        $this->metadata = $metadata;

        return $this;
    }

    /**
     * @param HttpMethod $method
     * @return bool
     */
    public function hasMethod(HttpMethod $method): bool
    {
        return in_array($method, $this->methods, true);
    }

    /**
     * Compile the route pattern into a regex
     *
     * @return void
     */
    private function compilePattern(): void
    {
        $pattern = $this->path;
        $this->parameterNames = [];

        $pattern = preg_replace_callback(
            '/\{([^}]+)}/',
            function ($matches) {
                $param = $matches[1];
                $isOptional = str_ends_with($param, '?');

                if ($isOptional) {
                    $param = substr($param, 0, -1);
                }

                $this->parameterNames[] = $param;

                $constraint = $this->constraints[$param] ?? '[^/]+';

                return $isOptional ? "($constraint)?" : "($constraint)";
            },
            $pattern
        );

        $pattern = str_replace('/', '\/', $pattern);

        $this->compiledPattern = '/^' . $pattern . '$/';
    }

    /**
     * Create a new route instance with the GET method
     *
     * @param string $path
     * @param string $controller
     * @param string $action
     * @param string|null $name
     * @return self
     */
    public static function get(string $path, string $controller, string $action, ?string $name = null): self
    {
        return new self($path, $controller, $action, [HttpMethod::GET], $name);
    }

    /**
     * Create a new route instance with the POST method
     *
     * @param string $path
     * @param string $controller
     * @param string $action
     * @param string|null $name
     * @return self
     */
    public static function post(string $path, string $controller, string $action, ?string $name = null): self
    {
        return new self($path, $controller, $action, [HttpMethod::POST], $name);
    }

    /**
     * Create a new route instance with the PUT method
     *
     * @param string $path
     * @param string $controller
     * @param string $action
     * @param string|null $name
     * @return self
     */
    public static function put(string $path, string $controller, string $action, ?string $name = null): self
    {
        return new self($path, $controller, $action, [HttpMethod::PUT], $name);
    }

    /**
     * Create a new route instance with the DELETE method
     *
     * @param string $path
     * @param string $controller
     * @param string $action
     * @param string|null $name
     * @return self
     */
    public static function delete(string $path, string $controller, string $action, ?string $name = null): self
    {
        return new self($path, $controller, $action, [HttpMethod::DELETE], $name);
    }

    /**
     * Create a new route instance with the PATCH method
     *
     * @param string $path
     * @param string $controller
     * @param string $action
     * @param string|null $name
     * @return self
     */
    public static function patch(string $path, string $controller, string $action, ?string $name = null): self
    {
        return new self($path, $controller, $action, [HttpMethod::PATCH], $name);
    }

    /**
     * Create a new route instance with multiple methods
     *
     * @param string $path
     * @param string $controller
     * @param string $action
     * @param string|null $name
     * @return self
     */
    public static function any(string $path, string $controller, string $action, ?string $name = null): self
    {
        return new self($path, $controller, $action, [HttpMethod::GET, HttpMethod::POST, HttpMethod::PUT, HttpMethod::DELETE, HttpMethod::PATCH], $name);
    }

    /**
     * Create a new route instance with custom methods
     *
     * @param array $methods
     * @param string $path
     * @param string $controller
     * @param string $action
     * @param string|null $name
     * @return self
     */
    public static function match(array $methods, string $path, string $controller, string $action, ?string $name = null): self
    {
        return new self($path, $controller, $action, $methods, $name);
    }
}