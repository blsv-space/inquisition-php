<?php

namespace Inquisition\Core\Application\Job;

use Inquisition\Core\Application\Job\Exception\JobFailedException;
use Inquisition\Core\Application\Job\Exception\JobRetryableException;
use Throwable;

interface JobInterface
{
    /**
     * Execute the job
     * return The result of the job execution (optional)
     *
     * @throws JobFailedException If the job fails and should not be retried
     * @throws JobRetryableException If the job fails but should be retried
     */
    public function handle();

    /**
     * Get the job identifier/name
     */
    public function getName(): string;

    public array $payload {
        get;
    }

    public function execute();

    /**
     * Check if this job should run asynchronously
     */
    public function isAsync(): bool;

    /**
     * Get maximum number of retry attempts
     */
    public function getMaxRetries(): int;

    /**
     * Get a delay in seconds before retry
     */
    public function getRetryDelay(): int;

    /**
     * Get job priority (higher number = higher priority)
     */
    public function getPriority(): int;

    /**
     * Get the queue name this job should run on
     */
    public function getQueueName(): string;

    /**
     * Check if a job should be retried after failure
     */
    public function shouldRetry(Throwable $exception, int $attempt): bool;

    /**
     * Called when a job fails permanently (after all retries)
     */
    public function onFailure(Throwable $exception): void;

    /**
     * Called when a job completes successfully
     */
    public function onSuccess(mixed $result): void;
}
