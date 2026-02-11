<?php

declare(strict_types=1);

namespace Inquisition\Core\Infrastructure\Http\Request;

use Inquisition\Core\Infrastructure\Http\HttpMethod;

/**
 * HTTP Request Interface
 * Abstraction for incoming HTTP requests
 */
interface RequestInterface
{
    /**
     * Get request method (GET, POST, PUT, DELETE, etc.)
     */
    public function getMethod(): HttpMethod;

    /**
     * Get request URI/path
     */
    public function getUri(): string;

    /**
     * Get all request parameters (query + body)
     */
    public function getAllParameters(): array;

    /**
     * Get specific parameter value
     */
    public function getParameter(string $key, $default = null);

    public array $headers {
        get;
    }

    /**
     * Get a specific header value
     */
    public function getHeader(string $name, $default = null): ?string;

    /**
     * Get request body as string
     */
    public function getBody(): string;

    /**
     * Get parsed JSON body as an array
     */
    public function getJsonBody(): ?array;

    /**
     * Get files from Request
     */
    public function getFiles(): array;

    /**
     * Check if request has specific parameter
     */
    public function hasParameter(string $key): bool;

    /**
     * @return mixed
     */
    public function getClientIp(): string;
}
