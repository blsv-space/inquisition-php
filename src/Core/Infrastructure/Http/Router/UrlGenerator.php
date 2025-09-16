<?php

namespace Inquisition\Core\Infrastructure\Http\Router;

use Inquisition\Core\Infrastructure\Http\HttpSchema;
use Inquisition\Foundation\Singleton\SingletonInterface;
use Inquisition\Foundation\Singleton\SingletonTrait;
use InvalidArgumentException;

/**
 * URL Generator Implementation
 * Generates URLs from routes with support for parameters and options
 */
final class UrlGenerator
    implements UrlGeneratorInterface, SingletonInterface
{
    use SingletonTrait;

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

    private function __construct()
    {
        $this->detectCurrentSchemeAndHost();
    }

    /**
     * Generate URL for a named route
     *
     * @param RouteInterface $route
     * @param array $parameters
     * @param array $options
     * @return string
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
     *
     * @param RouteInterface $route
     * @param array $parameters
     * @return string
     */
    public function generateAbsolute(RouteInterface $route, array $parameters = []): string
    {
        return $this->generate($route, $parameters, ['absolute' => true]);
    }

    /**
     * Generate relative URL
     *
     * @param RouteInterface $route
     * @param array $parameters
     * @return string
     */
    public function generateRelative(RouteInterface $route, array $parameters = []): string
    {
        return $this->generate($route, $parameters, ['relative' => true]);
    }

    /**
     * Build URL from route and parameters
     *
     * @param RouteInterface $route
     * @param array $parameters
     * @return string
     */
    private function buildUrl(RouteInterface $route, array $parameters = []): string
    {
        $path = $route->path;

        foreach ($parameters as $key => $value) {
            $path = str_replace("{{$key}}", $value, $path);
        }

        if (!str_starts_with($path, '/')) {
            $path = '/' . $path;
        }

        return $path;
    }

    /**
     * Make URL absolute
     *
     * @param string $url
     * @return string
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
     *
     * @param string $url
     * @return string
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
     *
     * @return void
     */
    private function detectCurrentSchemeAndHost(): void
    {
        if (isset($_SERVER['HTTP_HOST'])) {
            $this->host = $_SERVER['HTTP_HOST'];
        }

        $SERVER_PORT = $_SERVER['SERVER_PORT'] ?? 80;
        $HTTPS = $_SERVER['HTTPS'] ?? null;
        $HTTP_X_FORWARDED_PROTO = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? null;
        if ($HTTPS === 'on') {
            $this->scheme = HttpSchema::HTTPS;
        } elseif ($HTTP_X_FORWARDED_PROTO === 'https') {
            $this->scheme = HttpSchema::HTTPS;
        } elseif ($SERVER_PORT === '443') {
            $this->scheme = HttpSchema::HTTPS;
        } else {
            $this->scheme = HttpSchema::HTTP;
        }

        $this->port = $SERVER_PORT;
    }
}
