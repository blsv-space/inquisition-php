<?php

namespace Inquisition\Core\Infrastructure\Http\Request;

use Inquisition\Core\Application\Http\Request\RequestInterface;
use Inquisition\Core\Infrastructure\Http\HttpMethod;

class HttpRequest implements RequestInterface
{

    /**
     * @inheritDoc
     */
    public function getMethod(): HttpMethod
    {
        return HttpMethod::fromString($_SERVER['REQUEST_METHOD']) ?? HttpMethod::GET;
    }

    /**
     * @inheritDoc
     */
    public function getUri(): string
    {
        return $_SERVER['REQUEST_URI'] ?? '/';
    }

    /**
     * @inheritDoc
     */
    public function getAllParameters(): array
    {
        return array_merge($_REQUEST, $this->getJsonBody() ?? []);
    }

    /**
     * @inheritDoc
     */
    public function getParameter(string $key, $default = null)
    {
        return $this->getAllParameters()[$key] ?? $default;
    }

    /**
     * @inheritDoc
     */
    public function getHeaders(): array
    {
        return getallheaders();
    }

    /**
     * @inheritDoc
     */
    public function getHeader(string $name, $default = null): ?string
    {
        $headers = $this->getHeaders();

        return $headers[$name] ?? $default;
    }

    /**
     * @inheritDoc
     */
    public function getBody(): string
    {
        return file_get_contents('php://input');
    }

    /**
     * @inheritDoc
     */
    public function getJsonBody(): ?array
    {
        $body = $this->getBody();

        return !empty($body) ? json_decode($body, true) : null;
    }

    /**
     * @inheritDoc
     */
    public function hasParameter(string $key): bool
    {
        return array_key_exists($key, $this->getAllParameters());
    }

    /**
     * @inheritDoc
     */
    public function getFiles(): array
    {
        return $_FILES;
    }

    /**
     * @return string
     */
    public function getClientIp(): string
    {
        return $_SERVER['REMOTE_ADDR'];
    }
}