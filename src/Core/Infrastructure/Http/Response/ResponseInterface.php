<?php

declare(strict_types=1);

namespace Inquisition\Core\Infrastructure\Http\Response;

use Inquisition\Core\Infrastructure\Http\HttpStatusCode;

/**
 * HTTP Response Interface
 * Abstraction for outgoing HTTP responses
 */
interface ResponseInterface
{
    /**
     * Set HTTP status code
     */
    public function setStatusCode(HttpStatusCode $statusCode): self;

    /**
     * Get HTTP status code
     */
    public function getStatusCode(): HttpStatusCode;

    /**
     * Set response body content
     */
    public function setContent(string $content): self;

    /**
     * Get response body content
     */
    public function getContent(): string;

    /**
     * Set response header
     */
    public function setHeader(string $name, string $value): self;

    /**
     * Get response headers
     */
    public function getHeaders(): array;

    /**
     * Set multiple headers at once
     */
    public function setHeaders(array $headers): self;

    /**
     * Send the response to the client
     */
    public function send(): void;
}
