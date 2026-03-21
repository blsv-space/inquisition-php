<?php

declare(strict_types=1);

namespace Inquisition\Core\Application\Job;

use Inquisition\Core\Application\Job\Exception\JobFailedException;
use Inquisition\Core\Application\Job\Exception\JobRetryableException;
use Throwable;

abstract class AbstractAsyncJob implements JobInterface
{
    protected const int MAX_RETRIES = 3;
    protected const int RETRY_DELAY_SECONDS = 60;
    protected const int PRIORITY = 0;
    protected const string QUEUE_NAME = 'default';

    public function __construct(
        public array $payload = [] {
            get {
                return $this->payload;
            }
        },
    )
    {
    }

    /**
     * Must be implemented by concrete jobs
     */
    #[\Override]
    abstract public function handle();

    abstract public function getQueue(): JobQueueInterface;

    /**
     * Execute async job (enqueue it)
     *
     */
    #[\Override]
    public function execute(): void
    {
        $this->getQueue()->enqueue($this);
    }


    #[\Override]
    public function getName(): string
    {
        return static::class;
    }

    #[\Override]
    public function isAsync(): bool
    {
        return true;
    }

    #[\Override]
    public function getMaxRetries(): int
    {
        return static::MAX_RETRIES;
    }

    #[\Override]
    public function getRetryDelay(): int
    {
        return static::RETRY_DELAY_SECONDS;
    }

    #[\Override]
    public function getPriority(): int
    {
        return static::PRIORITY;
    }

    #[\Override]
    public function getQueueName(): string
    {
        return static::QUEUE_NAME;
    }

    #[\Override]
    public function shouldRetry(Throwable $exception, int $attempt): bool
    {
        // Don't retry if it's a permanent failure
        if ($exception instanceof JobFailedException) {
            return false;
        }

        // Retry if it's explicitly retryable and we haven't exceeded max attempts
        if ($exception instanceof JobRetryableException) {
            return $attempt < $this->getMaxRetries();
        }

        // Default: retry on any other exception up to max attempts
        return $attempt < $this->getMaxRetries();
    }

    #[\Override]
    public function onFailure(Throwable $exception): void {}

    #[\Override]
    public function onSuccess(mixed $result): void {}
}
