<?php

declare(strict_types=1);

namespace Inquisition\Core\Infrastructure\Http\RateLimit;

interface RateLimiterInterface
{
    /**
     * Check if the request limit is exceeded for the given identifier
     *
     */
    public function isExceeded(RateLimitIdentifierInterface $identifier, int $maxRequests, int $timeWindow): bool;

    /**
     * Increment the request count for the given identifier
     *
     */
    public function increment(RateLimitIdentifierInterface $identifier): int;

    /**
     * Get the current request count for the identifier
     *
     */
    public function getCount(RateLimitIdentifierInterface $identifier, int $timeWindow): int;

    /**
     * Reset the counter for the identifier
     *
     */
    public function reset(RateLimitIdentifierInterface $identifier): void;

    /**
     * Get remaining requests for the identifier
     *
     */
    public function getRemainingRequests(RateLimitIdentifierInterface $identifier, int $maxRequests, int $timeWindow): int;

    /**
     * Get the time when the rate limit resets
     *
     */
    public function getResetTime(RateLimitIdentifierInterface $identifier, int $timeWindow): int;
}
