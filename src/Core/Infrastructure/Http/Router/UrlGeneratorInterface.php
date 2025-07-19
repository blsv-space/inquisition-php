<?php

namespace Inquisition\Core\Infrastructure\Http\Router;

use Inquisition\Core\Infrastructure\Http\HttpSchema;

/**
 * URL Generator Interface
 * Defines the contract for generating URLs from routes
 */
interface UrlGeneratorInterface
{
    /**
     * Generate URL for a named route
     */
    public function generate(RouteInterface $route, array $parameters = [], array $options = []): string;

    /**
     * Generate absolute URL
     */
    public function generateAbsolute(RouteInterface $route, array $parameters = []): string;

    /**
     * Generate relative URL
     */
    public function generateRelative(RouteInterface $route, array $parameters = []): string;

    public string $baseUrl {
        get;
        set;
    }

    public HttpSchema $scheme {
        get;
        set;
    }

    public string $host {
        set;
        get;
    }

}
