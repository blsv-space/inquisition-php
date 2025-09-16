<?php

namespace Inquisition\Core\Infrastructure\Http;

/**
 * HTTP Methods Enum
 * Centralized definition of all HTTP methods
 */
enum HttpMethod: string
{
    case GET = 'GET';
    case POST = 'POST';
    case PUT = 'PUT';
    case PATCH = 'PATCH';
    case DELETE = 'DELETE';
    case HEAD = 'HEAD';
    case OPTIONS = 'OPTIONS';
    case TRACE = 'TRACE';
    case CONNECT = 'CONNECT';

    /**
     * Get method description
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::GET => 'Retrieve data from server',
            self::POST => 'Send data to server for processing',
            self::PUT => 'Update/create resource completely',
            self::PATCH => 'Partially update resource',
            self::DELETE => 'Remove resource from server',
            self::HEAD => 'Get headers only (like GET but no body)',
            self::OPTIONS => 'Get allowed methods and capabilities',
            self::TRACE => 'Diagnostic method for debugging',
            self::CONNECT => 'Establish tunnel (for proxies)',
        };
    }

    /**
     * Check if the method is safe (read-only)
     */
    public function isSafe(): bool
    {
        return in_array($this, [self::GET, self::HEAD, self::OPTIONS, self::TRACE]);
    }

    /**
     * Check if the method is idempotent
     */
    public function isIdempotent(): bool
    {
        return in_array($this, [self::GET, self::PUT, self::DELETE, self::HEAD, self::OPTIONS, self::TRACE]);
    }

    /**
     * Check if method can have request body
     */
    public function canHaveRequestBody(): bool
    {
        return in_array($this, [self::POST, self::PUT, self::PATCH]);
    }

    /**
     * Check if a method should have response body
     */
    public function shouldHaveResponseBody(): bool
    {
        return $this != self::HEAD;
    }

    /**
     * Check if a method is cacheable
     */
    public function isCacheable(): bool
    {
        return in_array($this, [self::GET, self::HEAD]);
    }

    /**
     * Get semantic meaning
     */
    public function getSemantic(): string
    {
        return match ($this) {
            self::GET => 'READ',
            self::POST => 'CREATE',
            self::PUT => 'UPDATE/CREATE',
            self::PATCH => 'PARTIAL_UPDATE',
            self::DELETE => 'DELETE',
            self::HEAD => 'READ_METADATA',
            self::OPTIONS => 'CAPABILITIES',
            self::TRACE => 'DIAGNOSTIC',
            self::CONNECT => 'TUNNEL',
        };
    }

    /**
     * Get expected success status codes
     */
    public function getExpectedSuccessStatusCodes(): array
    {
        return match ($this) {
            self::GET => [HttpStatusCode::OK, HttpStatusCode::PARTIAL_CONTENT],
            self::POST => [HttpStatusCode::OK, HttpStatusCode::CREATED, HttpStatusCode::ACCEPTED],
            self::PUT => [HttpStatusCode::OK, HttpStatusCode::CREATED, HttpStatusCode::NO_CONTENT],
            self::PATCH, self::OPTIONS => [HttpStatusCode::OK, HttpStatusCode::NO_CONTENT],
            self::DELETE => [HttpStatusCode::OK, HttpStatusCode::NO_CONTENT, HttpStatusCode::ACCEPTED],
            self::HEAD, self::TRACE, self::CONNECT => [HttpStatusCode::OK],
        };
    }

    /**
     * Create from string (case-insensitive)
     */
    public static function fromString(string $method): ?self
    {
        return self::tryFrom(strtoupper($method));
    }

    /**
     * Get all CRUD methods
     */
    public static function crudMethods(): array
    {
        return [self::GET, self::POST, self::PUT, self::PATCH, self::DELETE];
    }

    /**
     * Get all safe methods
     */
    public static function safeMethods(): array
    {
        return [self::GET, self::HEAD, self::OPTIONS, self::TRACE];
    }

    /**
     * Get all methods that modify data
     */
    public static function modifyingMethods(): array
    {
        return [self::POST, self::PUT, self::PATCH, self::DELETE];
    }
}

