<?php

namespace Inquisition\Core\Infrastructure\Http\Middleware;

use Inquisition\Core\Infrastructure\Http\HttpStatusCode;
use Inquisition\Core\Infrastructure\Http\RateLimit\RateLimiterInterface;
use Inquisition\Core\Infrastructure\Http\RateLimit\RateLimitIdentifierInterface;
use Inquisition\Core\Infrastructure\Http\Request\RequestInterface;
use Inquisition\Core\Infrastructure\Http\Response\ResponseFactory;
use Inquisition\Core\Infrastructure\Http\Response\ResponseInterface;
use JsonException;

readonly class RateLimitMiddleware implements MiddlewareInterface
{
    const string HEADER_RATE_LIMIT_LIMIT = 'X-RateLimit-Limit';
    const string HEADER_RATE_LIMIT_REMAINING = 'X-RateLimit-Remaining';
    const string HEADER_RATE_LIMIT_RESET = 'X-RateLimit-Reset';

    public function __construct(
        private RateLimiterInterface         $rateLimiter,
        private RateLimitIdentifierInterface $identifier,
        private int                          $maxRequests = 100,
        private int                          $timeWindow = 3600 // 1 hour in seconds
    )
    {
    }

    /**
     * @param RequestInterface $request
     * @param callable $next
     * @return ResponseInterface
     * @throws JsonException
     */
    public function process(RequestInterface $request, callable $next): ResponseInterface
    {

        if ($this->rateLimiter->isExceeded($this->identifier, $this->maxRequests, $this->timeWindow)) {
            return ResponseFactory::error(
                'Rate limit exceeded. Try again later.',
                HttpStatusCode::TOO_MANY_REQUESTS
            );
        }

        $currentCount = $this->rateLimiter->increment($this->identifier);

        $response = $next($request);
        $this->addRateLimitHeaders($response, $currentCount);

        return $response;
    }

    /**
     * @param ResponseInterface $response
     * @param int $currentCount
     * @return void
     */
    private function addRateLimitHeaders(ResponseInterface $response, int $currentCount): void
    {
        $remaining = max(0, $this->maxRequests - $currentCount);
        $resetTime = $this->rateLimiter->getResetTime($this->identifier, $this->timeWindow);

        $response->setHeaders([
            static::HEADER_RATE_LIMIT_LIMIT => (string)$this->maxRequests,
            static::HEADER_RATE_LIMIT_REMAINING => (string)$remaining,
            static::HEADER_RATE_LIMIT_RESET => (string)$resetTime,
        ]);
    }
}