<?php

namespace Inquisition\Core\Infrastructure\Http\Middleware;

use Inquisition\Core\Infrastructure\Http\Request\RequestInterface;
use Inquisition\Core\Infrastructure\Http\Response\ResponseInterface;
use Inquisition\Core\Infrastructure\Http\HttpMethod;
use Inquisition\Core\Infrastructure\Http\HttpStatusCode;
use Inquisition\Core\Infrastructure\Http\Response\HttpResponse;
use InvalidArgumentException;

/**
 * Cors Middleware
 */
final readonly class CorsMiddleware implements MiddlewareInterface
{
    public function __construct(
        private array $allowedOrigins = ['*'],
        private array $allowedMethods = [
            HttpMethod::GET,
            HttpMethod::POST,
            HttpMethod::PUT,
            HttpMethod::PATCH,
            HttpMethod::DELETE,
            HttpMethod::OPTIONS,
            HttpMethod::HEAD,
            HttpMethod::TRACE,
            HttpMethod::CONNECT,
        ],
        private array $allowedHeaders = ['Content-Type', 'Authorization'],
        private int $accessControlMaxAge = 3600,
    ) {
        $this->validate();
    }

    /**
     * @return void
     */
    private function validate(): void
    {
        if (array_any($this->allowedMethods, fn (mixed $m) => !$m instanceof HttpMethod)) {
            throw new InvalidArgumentException('Allowed methods must be an array of HttpMethod instances');
        }
    }

    /**
     * @param RequestInterface $request
     * @param callable $next
     * @return ResponseInterface
     */
    public function process(RequestInterface $request, callable $next): ResponseInterface
    {
        if ($request->getMethod() === HttpMethod::OPTIONS) {
            return $this->createPreflightResponse();
        }

        $response = $next($request);

        return $this->addCorsHeaders($response);
    }

    /**
     * @return ResponseInterface
     */
    private function createPreflightResponse(): ResponseInterface
    {
        return new HttpResponse()
            ->setStatusCode(HttpStatusCode::NO_CONTENT)
            ->setHeaders([
                'Access-Control-Allow-Origin' => implode(', ', $this->allowedOrigins),
                'Access-Control-Allow-Methods' => implode(', ',
                    array_map(fn (HttpMethod $m) => $m->value,  $this->allowedMethods)),
                'Access-Control-Allow-Headers' => implode(', ', $this->allowedHeaders),
                'Access-Control-Max-Age' => $this->accessControlMaxAge,
            ]);
    }

    /**
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    private function addCorsHeaders(ResponseInterface $response): ResponseInterface
    {
        return $response->setHeaders([
            'Access-Control-Allow-Origin' => implode(', ', $this->allowedOrigins),
            'Access-Control-Allow-Methods' => implode(', ',
                array_map(fn (HttpMethod $m) => $m->value,  $this->allowedMethods)),
            'Access-Control-Allow-Headers' => implode(', ', $this->allowedHeaders),
        ]);
    }
}