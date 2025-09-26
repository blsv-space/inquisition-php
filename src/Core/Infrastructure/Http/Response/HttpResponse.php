<?php

namespace Inquisition\Core\Infrastructure\Http\Response;

use Inquisition\Core\Infrastructure\Http\HttpStatusCode;
use JsonException;

class HttpResponse implements ResponseInterface
{
    protected HttpStatusCode $statusCode = HttpStatusCode::OK;
    protected string         $content    = '';
    protected array          $headers    = [];
    protected bool           $sent       = false {
        get {
            return $this->sent;
        }
    }

    /**
     * @param HttpStatusCode $statusCode
     *
     * @return $this
     */
    public function setStatusCode(HttpStatusCode $statusCode): self
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * @return HttpStatusCode
     */
    public function getStatusCode(): HttpStatusCode
    {
        return $this->statusCode;
    }

    /**
     * @return void
     */
    public function send(): void
    {
        if ($this->sent || headers_sent()) {
            return;
        }

        // Send a status line with a proper message
        http_response_code($this->statusCode->value);
        header("HTTP/1.1 {$this->statusCode->value} {$this->statusCode->getMessage()}");

        // Send headers
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }

        echo $this->content;
        $this->sent = true;
    }

    /**
     * @return string
     */
    public function getStatusMessage(): string
    {
        return $this->statusCode->getMessage();
    }

    /**
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return $this->statusCode->isSuccessful();
    }

    /**
     * @return bool
     */
    public function isError(): bool
    {
        return $this->statusCode->isError();
    }

    /**
     * @param string $content
     *
     * @return $this
     */
    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @param string $name
     * @param string $value
     *
     * @return $this
     */
    public function setHeader(string $name, string $value): self
    {
        $this->headers[$this->normalizeHeaderName($name)] = $value;

        return $this;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @param array $headers
     *
     * @return $this
     */
    public function setHeaders(array $headers): self
    {
        foreach ($headers as $name => $value) {
            $this->setHeader($name, $value);
        }

        return $this;
    }

    /**
     * Get a specific header value
     */
    public function getHeader(string $name): ?string
    {
        $normalizedName = $this->normalizeHeaderName($name);

        return $this->headers[$normalizedName] ?? null;
    }

    /**
     * Check if a header exists
     */
    public function hasHeader(string $name): bool
    {
        $normalizedName = $this->normalizeHeaderName($name);

        return isset($this->headers[$normalizedName]);
    }

    /**
     * Remove a header
     */
    public function removeHeader(string $name): self
    {
        $normalizedName = $this->normalizeHeaderName($name);
        unset($this->headers[$normalizedName]);

        return $this;
    }

    /**
     * Set JSON content with the appropriate header
     *
     * @throws JsonException
     */
    public function setJsonContent(array $data): self
    {
        $this->setHeader('Content-Type', 'application/json');
        $this->setContent(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));

        return $this;
    }

    /**
     * Set HTML content with the appropriate header
     */
    public function setHtmlContent(string $html): self
    {
        $this->setHeader('Content-Type', 'text/html; charset=UTF-8');
        $this->setContent($html);

        return $this;
    }

    /**
     * Set plain text content with the appropriate header
     */
    public function setTextContent(string $text): self
    {
        $this->setHeader('Content-Type', 'text/plain; charset=UTF-8');
        $this->setContent($text);

        return $this;
    }

    /**
     * Create a redirect response
     */
    public function redirect(string $url, HttpStatusCode $statusCode = HttpStatusCode::FOUND): self
    {
        $this->setStatusCode($statusCode);
        $this->setHeader('Location', $url);

        return $this;
    }

    /**
     * Normalize header name (capitalize words, use hyphens)
     * Example: content-type -> Content-Type
     */
    private function normalizeHeaderName(string $name): string
    {
        return str_replace(' ', '-', ucwords(str_replace(['_', '-'], ' ', strtolower($name))));
    }

    /**
     * Set cache control headers
     */
    public function setCacheControl(string $directive): self
    {
        $this->setHeader('Cache-Control', $directive);

        return $this;
    }

    /**
     * Set no-cache headers
     */
    public function setNoCache(): self
    {
        $this->setHeaders([
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);

        return $this;
    }

    /**
     * Enable CORS for API responses
     */
    public function enableCors(
        array $origins = ['*'],
        array $methods = ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
    ): self {
        $this->setHeaders([
            'Access-Control-Allow-Origin' => implode(', ', $origins),
            'Access-Control-Allow-Methods' => implode(', ', $methods),
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With',
            'Access-Control-Max-Age' => '3600',
        ]);

        return $this;
    }

}
