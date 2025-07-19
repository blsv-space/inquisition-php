<?php

namespace Inquisition\Core\Infrastructure\Http\Router;

use Inquisition\Core\Infrastructure\Http\HttpSchema;
use InvalidArgumentException;

/**
 * URL Generator Implementation
 * Generates URLs from routes with support for parameters and options
 */
final class UrlGenerator implements UrlGeneratorInterface
{
    public string     $baseUrl = '' {
        get {
            return $this->baseUrl;
        }
        set {
            $this->baseUrl = rtrim($value, '/');
        }
    }
    public HttpSchema $scheme  = HttpSchema::HTTP {
        get {
            return $this->scheme;
        }
        set {
            $this->scheme = $value;
        }
    }

    public string $host = 'localhost' {
        get {
            return $this->host;
        }
        set {
            $this->host = $value;
        }
    }

    public int $port = 80 {
        get {
            return $this->port;
        }
        set {
            if ($value < 1 || $value > 65535) {
                throw new InvalidArgumentException('Invalid port number');
            }
            $this->port = $value;
        }
    }

    public function __construct()
    {
        $this->detectCurrentSchemeAndHost();
    }

    /**
     * Generate URL for a named route
     */
    public function generate(RouteInterface $route, array $parameters = [], array $options = []): string
    {
        $url = $this->buildUrl($route, $parameters);

        // Handle options
        if (isset($options['absolute']) && $options['absolute']) {
            return $this->makeAbsolute($url);
        }

        if (isset($options['relative']) && $options['relative']) {
            return $this->makeRelative($url);
        }

        // Default behavior - return relative URL with base URL if set
        return $this->baseUrl . $url;
    }

    /**
     * Generate absolute URL
     */
    public function generateAbsolute(RouteInterface $route, array $parameters = []): string
    {
        return $this->generate($route, $parameters, ['absolute' => true]);
    }

    /**
     * Generate relative URL
     */
    public function generateRelative(RouteInterface $route, array $parameters = []): string
    {
        return $this->generate($route, $parameters, ['relative' => true]);
    }

    /**
     * Build URL from route and parameters
     */
    private function buildUrl(RouteInterface $route, array $parameters = []): string
    {
        $path = $route->path;

        // Merge route defaults with provided parameters
        $allParameters = array_merge($route->defaults, $parameters);

        // Replace path parameters
        $path = preg_replace_callback('/\{([^}]+)\}/', function ($matches) use ($allParameters) {
            $paramName = $matches[1];

            // Handle optional parameters (ending with ?)
            if (str_ends_with($paramName, '?')) {
                $paramName = rtrim($paramName, '?');

                return isset($allParameters[$paramName]) ? (string) $allParameters[$paramName] : '';
            }

            if (!isset($allParameters[$paramName])) {
                throw new InvalidArgumentException("Missing required parameter '{$paramName}'");
            }

            return (string) $allParameters[$paramName];
        }, $path);

        // Clean up any double slashes
        $path = preg_replace('#/+#', '/', $path);

        // Ensure path starts with /
        if (!str_starts_with($path, '/')) {
            $path = '/' . $path;
        }

        // Add query parameters for any unused parameters
        $usedParams = [];
        preg_match_all('/\{([^}?]+)\??}/', $route->path, $matches);
        if (isset($matches[1])) {
            $usedParams = array_map(fn($param) => rtrim($param, '?'), $matches[1]);
        }

        $queryParams = array_diff_key($allParameters, array_flip($usedParams), $route->defaults);

        if (!empty($queryParams)) {
            $path .= '?' . http_build_query($queryParams);
        }

        return $path;
    }

    /**
     * Make URL absolute
     */
    public function makeAbsolute(string $url): string
    {
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        $baseUrl = $this->scheme->value . '://' . $this->host;

        if ($this->scheme === HttpSchema::HTTP
            && !str_contains($this->host, ':80')
            && !str_contains($this->host, ':')
            && $this->port !== 80
        ) {
            $baseUrl .= ':' . $this->port;
        } elseif (
            $this->scheme === HttpSchema::HTTPS
            && !str_contains($this->host, ':443')
            && !str_contains($this->host, ':')
            && $this->port !== 443
        ) {
            $baseUrl .= ':' . $this->port;
        }

        return $baseUrl . $url;
    }

    /**
     * Make URL relative (strip scheme and host)
     */
    public function makeRelative(string $url): string
    {
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return parse_url($url, PHP_URL_PATH) .
                (parse_url($url, PHP_URL_QUERY) ? '?' . parse_url($url, PHP_URL_QUERY) : '');
        }

        if ($this->baseUrl && str_starts_with($url, $this->baseUrl)) {
            $url = substr($url, strlen($this->baseUrl));
        }

        return $url ?: '/';
    }

    /**
     * Detect the current scheme and host from the environment
     */
    private function detectCurrentSchemeAndHost(): void
    {
        if (isset($_SERVER['HTTP_HOST'])) {
            $this->host = $_SERVER['HTTP_HOST'];
        }

        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            $this->scheme = HttpSchema::HTTPS;
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            $this->scheme = HttpSchema::HTTPS;
        } elseif (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] === '443') {
            $this->scheme = HttpSchema::HTTPS;
        } else {
            $this->scheme = HttpSchema::HTTP;
        }

        $this->port = $_SERVER['SERVER_PORT'];
    }
}
