<?php

namespace Inquisition\Core\Infrastructure\Http\RateLimit;

interface RateLimiterInterface
{
    /**
     * Check if the request limit is exceeded for the given identifier
     *
     * @param RateLimitIdentifierInterface $identifier
     * @param int $maxRequests
     * @param int $timeWindow
     * @return bool
     */
    public function isExceeded(RateLimitIdentifierInterface $identifier, int $maxRequests, int $timeWindow): bool;

    /**
     * Increment the request count for the given identifier
     *
     * @param RateLimitIdentifierInterface $identifier
     * @return int
     */
    public function increment(RateLimitIdentifierInterface $identifier): int;

    /**
     * Get the current request count for the identifier
     *
     * @param RateLimitIdentifierInterface $identifier
     * @param int $timeWindow
     * @return int
     */
    public function getCount(RateLimitIdentifierInterface $identifier, int $timeWindow): int;

    /**
     * Reset the counter for the identifier
     *
     * @param RateLimitIdentifierInterface $identifier
     * @return void
     */
    public function reset(RateLimitIdentifierInterface $identifier): void;

    /**
     * Get remaining requests for the identifier
     *
     * @param RateLimitIdentifierInterface $identifier
     * @param int $maxRequests
     * @param int $timeWindow
     * @return int
     */
    public function getRemainingRequests(RateLimitIdentifierInterface $identifier, int $maxRequests, int $timeWindow): int;

    /**
     * Get the time when the rate limit resets
     *
     * @param RateLimitIdentifierInterface $identifier
     * @param int $timeWindow
     * @return int
     */
    public function getResetTime(RateLimitIdentifierInterface $identifier, int $timeWindow): int;
}