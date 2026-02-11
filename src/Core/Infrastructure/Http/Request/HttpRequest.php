<?php

declare(strict_types=1);

namespace Inquisition\Core\Infrastructure\Http\Request;

use Inquisition\Core\Infrastructure\Http\HttpMethod;
use JsonException;

final class HttpRequest implements RequestInterface
{
    /** @var array<string,string|string[]> */
    public protected(set) array $headers {
        get {
            return $this->headers;
        }
    }
    private ?array $decodedJson = null;

    /**
     * @param array<string,string|string[]>                                                                                        $headers
     * @param array<int|non-empty-string, non-empty-array<int|non-empty-string, array<int|non-empty-string, mixed>|string>|string> $query
     * @param array<int|non-empty-string, non-empty-array<int|non-empty-string, array<int|non-empty-string, mixed>|string>|string> $body
     * @param array<string,mixed>                                                                                                  $files
     */
    public function __construct(
        private readonly HttpMethod $method,
        private readonly string     $uri,
        private readonly array      $query = [],
        private readonly array      $body = [],
        private readonly string     $rawBody = '',
        private readonly array      $files = [],
        private readonly string     $clientIp = '0.0.0.0',
        array                       $headers = [],
    ) {
        $this->headers = $this->normaliseHeaders($headers);
    }

    #[\Override]
    public function getMethod(): HttpMethod
    {
        return $this->method;
    }

    #[\Override]
    public function getUri(): string
    {
        return $this->uri;
    }

    #[\Override]
    public function getAllParameters(): array
    {
        return $this->body + $this->query;
    }

    /**
     * @return mixed|null
     */
    #[\Override]
    public function getParameter(string $key, $default = null): mixed
    {
        return $this->body[$key]
            ?? $this->query[$key]
            ?? $default;
    }

    #[\Override]
    public function hasParameter(string $key): bool
    {
        return array_key_exists($key, $this->body) || array_key_exists($key, $this->query);
    }

    #[\Override]
    public function getHeader(string $name, $default = null): ?string
    {
        $normalised = strtolower($name);

        if (!isset($this->headers[$normalised])) {
            return $default;
        }

        $value = $this->headers[$normalised];
        if (is_array($value)) {
            return implode(', ', $value);
        }

        return $value;
    }

    #[\Override]
    public function getBody(): string
    {
        return $this->rawBody;
    }

    /**
     * @throws JsonException
     */
    #[\Override]
    public function getJsonBody(): ?array
    {
        if ($this->decodedJson !== null) {
            return $this->decodedJson;
        }

        $contentType = $this->getHeader('Content-Type');

        if ($contentType !== null && str_contains($contentType, 'application/json')) {
            $decoded = json_decode($this->rawBody, true, 512, JSON_THROW_ON_ERROR | JSON_BIGINT_AS_STRING);
            if (is_array($decoded)) {
                return $this->decodedJson = $decoded;
            }
        }

        return null;
    }

    #[\Override]
    public function getFiles(): array
    {
        return $this->files;
    }

    #[\Override]
    public function getClientIp(): string
    {
        return $this->clientIp;
    }


    /**
     * Build a request object from PHP super-globals.
     *
     */
    public static function createFromGlobals(): self
    {
        $method = HttpMethod::from($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $uri = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
        $headers = self::collectHeaders();
        $rawBody = file_get_contents('php://input') ?: '';
        $clientIp = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        return new self(
            method: $method,
            uri: $uri,
            query: $_GET,
            body: $_POST,
            rawBody: $rawBody,
            files: $_FILES,
            clientIp: $clientIp,
            headers: $headers,
        );
    }

    /**
     * Convert header keys to lower-case for case-insensitive lookup.
     *
     * @param array<string,string|string[]> $headers
     *
     * @return array<string,string|string[]>
     */
    private function normaliseHeaders(array $headers): array
    {
        $normalised = [];
        foreach ($headers as $key => $value) {
            $normalised[strtolower($key)] = trim($value);
        }

        return $normalised;
    }

    /**
     * Collect HTTP headers in a SAPI-agnostic way.
     *
     * @return array<string,string>
     */
    private static function collectHeaders(): array
    {
        if (function_exists('getallheaders')) {
            /** @var array<string,string> $headers */
            $headers = getallheaders();

            return $headers;
        }

        // Fallback for non-Apache SAPIs
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (str_starts_with($name, 'HTTP_')) {
                $header = str_replace('_', '-', substr($name, 5));
                $headers[$header] = (string) $value;
            }
        }

        return $headers;
    }
}
