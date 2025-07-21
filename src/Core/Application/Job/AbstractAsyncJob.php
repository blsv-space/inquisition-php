<?php

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

    /**
     * @param array $payload
     */
    public function __construct(
        public array $payload = [] {
            get {
                return $this->payload;
            }
        }
    )
    {
    }

    /**
     * Must be implemented by concrete jobs
     */
    abstract public function handle();

    /**
     * @return JobQueueInterface
     */
    abstract public function getQueue(): JobQueueInterface;

    /**
     * Execute async job (enqueue it)
     *
     * @return void
     */
    public function execute(): void
    {
        $this->getQueue()->enqueue($this);
    }


    /**
     * @return string
     */
    public function getName(): string
    {
        return static::class;
    }

    /**
     * @return bool
     */
    public function isAsync(): bool
    {
        return true;
    }

    /**
     * @return int
     */
    public function getMaxRetries(): int
    {
        return static::MAX_RETRIES;
    }

    /**
     * @return int
     */
    public function getRetryDelay(): int
    {
        return static::RETRY_DELAY_SECONDS;
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return static::PRIORITY;
    }

    /**
     * @return string
     */
    public function getQueueName(): string
    {
        return static::QUEUE_NAME;
    }

    /**
     * @param Throwable $exception
     * @param int $attempt
     * @return bool
     */
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

    /**
     * @param Throwable $exception
     * @return void
     */
    public function onFailure(Throwable $exception): void
    {
    }

    /**
     * @param mixed $result
     * @return void
     */
    public function onSuccess(mixed $result): void
    {
    }
}
