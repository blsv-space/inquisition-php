<?php

namespace Inquisition\Core\Application\Job;

use Throwable;

abstract class AbstractSyncJob implements JobInterface
{
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

    abstract public function handle();

    /**
     * Execute the job directly
     *
     * @return mixed
     * @throws Throwable
     */
    public function execute(): mixed
    {
        try {
            $result = $this->handle();
            $this->onSuccess($result);

            return $result;
        } catch (Throwable $exception) {
            $this->onFailure($exception);
            throw $exception;
        }
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
        return false;
    }

    /**
     * @return int
     */
    public function getMaxRetries(): int
    {
        return 0;
    }

    /**
     * @return int
     */
    public function getRetryDelay(): int
    {
        return 0;
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return 0;
    }

    /**
     * @return string
     */
    public function getQueueName(): string
    {
        return 'sync';
    }

    /**
     * @param Throwable $exception
     * @param int $attempt
     * @return bool
     */
    public function shouldRetry(Throwable $exception, int $attempt): bool
    {
        return false;
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
